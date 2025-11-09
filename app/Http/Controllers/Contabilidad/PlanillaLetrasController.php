<?php

namespace App\Http\Controllers\Contabilidad;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Services\Contabilidad\CajaService; 

class PlanillaLetrasController extends Controller
{
    protected $connection = 'sqlsrv';
    protected $cajaService; 

    public function __construct(CajaService $cajaService)
    {
        $this->middleware('auth');
        $this->cajaService = $cajaService;
    }

    
    public function index(Request $request)
    {
        $query = DB::connection($this->connection)->table('PllaLetras as pl')
            ->leftJoin('Bancos as b', 'pl.CodBanco', '=', 'b.Cuenta'); 

        $planillas = $query->select(
                'pl.Serie',
                'pl.Numero',
                'pl.CodBanco',
                'pl.Fecha',
                'pl.Procesado',
                'b.Banco as NombreBanco'
            )
            ->orderBy('pl.Fecha', 'desc')
            ->paginate(25);

        $planillas->transform(function($item){
            $item->CodBanco = trim($item->CodBanco);
            $item->NombreBanco = $item->NombreBanco ? trim($item->NombreBanco) : null;
            return $item;
        });

        return view('letras.descuento.index', ['planillas' => $planillas]);
    }


    public function create()
    {
        $bancos = DB::connection($this->connection)->table('Bancos')->get();
        return view('letras.descuento.create', ['bancos' => $bancos]);
    }

    
    public function store(Request $request)
    {
        $request->validate([
            'CodBanco' => 'required|string',
            'Fecha' => 'required|date',
            'Serie' => 'required|string|max:4'
        ]);

        try {
            // Generar correlativo para la planilla
            $serie = $request->Serie;
            $ultimoNum = DB::connection($this->connection)->table('PllaLetras')
                            ->where('Serie', $serie)->max('Numero');
            $nuevoNumInt = $ultimoNum ? (int)$ultimoNum + 1 : 1;
            $numero = str_pad($nuevoNumInt, 12, '0', STR_PAD_LEFT);

            DB::connection($this->connection)->table('PllaLetras')->insert([
                'Serie' => $serie,
                'Numero' => $numero,
                'CodBanco' => $request->CodBanco,
                'Fecha' => $request->Fecha,
                'Procesado' => 0
            ]);

            return redirect()->route('contador.letras_descuento.show', ['serie' => $serie, 'numero' => $numero]);

        } catch (\Exception $e) {
            Log::error("Error al crear PllaLetras: " . $e->getMessage());
            return redirect()->back()->with('error', 'Error al crear la planilla: ' . $e->getMessage())->withInput();
        }
    }

    
    public function show($serie, $numero)
    {
        $planilla = DB::connection($this->connection)->table('PllaLetras as pl')
            ->leftJoin('Bancos as b', 'pl.CodBanco', '=', 'b.Cuenta') 
            ->where('pl.Serie', $serie)
            ->where('pl.Numero', $numero)
            ->select('pl.*', 'b.Banco as NombreBanco')
            ->first();

        if (!$planilla) abort(404, 'Planilla no encontrada');

        $detalles = DB::connection($this->connection)->table('PllaDetLetras')
            ->where('Serie', $serie)
            ->where('Numero', $numero)
            ->get();

        $totalPlanilla = $detalles->sum('Importe');

        return view('letras.descuento.show', [
            'planilla' => $planilla,
            'detalles' => $detalles,
            'totalPlanilla' => $totalPlanilla
        ]);
    }

    
    
    public function procesarDescuento(Request $request, $serie, $numero)
    {
        $planilla = DB::connection($this->connection)->table('PllaLetras')
                    ->where('Serie', $serie)->where('Numero', $numero)->first();
                    
        if (!$planilla) return redirect()->back()->with('error', 'Planilla no encontrada.');
        if ($planilla->Procesado) return redirect()->back()->with('error', 'Esta planilla ya fue procesada.');

        $detalles = DB::connection($this->connection)->table('PllaDetLetras')
                    ->where('Serie', $serie)->where('Numero', $numero)->get();
        
        if ($detalles->isEmpty()) {
            return redirect()->back()->with('error', 'No se puede procesar una planilla vacía.');
        }

        $request->validate([
            'fecha_abono' => 'required|date',
            'interes' => 'required|numeric|min:0',
        ]);
        
        $montoTotalLetras = $detalles->sum('Importe');
        $montoInteres = (float) $request->input('interes');
        $montoNetoAbonado = $montoTotalLetras - $montoInteres;

        DB::connection($this->connection)->beginTransaction();
        try {

            foreach ($detalles as $letra) {
                DB::connection($this->connection)->table('CtaCliente')
                    ->where('Documento', $letra->NroLetra)
                    ->where('Tipo', 9) 
                    ->update(['Saldo' => 0]);
            }

            DB::connection($this->connection)->table('CtaBanco')->insert([
                'Documento' => "PLANILLA {$serie}-{$numero}",
                'Tipo' => 1, 
                'Clase' => 1, 
                'Fecha' => $request->fecha_abono,
                'Cuenta' => $planilla->CodBanco, 
                'Moneda' => 1, 'Cambio' => 1,
                'Monto' => $montoNetoAbonado,
                'Usuario' => Auth::user()->usuario,
                'Eliminado' => 0,
            ]);
            
            
            $numeroAsiento = $this->cajaService->obtenerSiguienteNumeroAsiento($request->fecha_abono); 
            
            $asientoId = DB::connection($this->connection)->table('libro_diario')->insertGetId([
                'numero' => $numeroAsiento,
                'fecha' => $request->fecha_abono,
                'glosa' => "Descuento Letras Planilla {$serie}-{$numero}. Banco: {$planilla->CodBanco}",
                'total_debe' => $montoTotalLetras,
                'total_haber' => $montoTotalLetras,
                'balanceado' => 1, 'estado' => 'ACTIVO', 'usuario_id' => Auth::id(),
                'created_at' => now(), 'updated_at' => now(),
            ]);
            
            DB::connection($this->connection)->table('libro_diario_detalles')->insert([
                'asiento_id' => $asientoId, 'cuenta_contable' => '104101', 
                'debe' => $montoNetoAbonado, 'haber' => 0,
                'concepto' => "Abono neto planilla {$serie}-{$numero}", 'created_at' => now(),
            ]);
            DB::connection($this->connection)->table('libro_diario_detalles')->insert([
                'asiento_id' => $asientoId, 'cuenta_contable' => '671101', 
                'debe' => $montoInteres, 'haber' => 0,
                'concepto' => "Intereses descuento planilla", 'created_at' => now(),
            ]);
            
            DB::connection($this->connection)->table('libro_diario_detalles')->insert([
                'asiento_id' => $asientoId, 'cuenta_contable' => '123201', 
                'debe' => 0, 'haber' => $montoTotalLetras,
                'concepto' => "Salida de letras en cartera", 'created_at' => now(),
            ]);

            DB::connection($this->connection)->table('PllaLetras')
                ->where('Serie', $serie)->where('Numero', $numero)
                ->update(['Procesado' => 1]);

            DB::connection($this->connection)->commit();
            
            return redirect()->route('contador.letras_descuento.show', ['serie' => $serie, 'numero' => $numero])
                             ->with('success', "Planilla {$serie}-{$numero} procesada. Asiento {$numeroAsiento} creado.");

        } catch (\Exception $e) {
            DB::connection($this->connection)->rollBack();
            Log::error("Error al procesar planilla: " . $e->getMessage());
            Log::error($e->getTraceAsString());
            return redirect()->back()->with('error', 'Error crítico al procesar la planilla: ' . $e->getMessage())->withInput();
        }
    }

   
    public function apiBuscarLetrasPendientes(Request $request)
    {
        $query = $request->input('q');
        if (strlen($query) < 3) return response()->json([]);

        $letras = DB::connection($this->connection)->table('CtaCliente as l')
            ->join('Clientes as c', 'l.CodClie', '=', 'c.Codclie')
            ->where('l.Tipo', 9) 
            ->where('l.Saldo', '>', 0) 
            ->where(function($q) use ($query) {
                $q->where('c.Razon', 'LIKE', "%{$query}%")
                  ->orWhere('l.Documento', 'LIKE', "%{$query}%");
            })
            ->select('l.Documento', 'c.Razon', 'c.CodClie', 'l.FechaV', 'l.Saldo', 'c.Documento as Ruc')
            ->limit(10)
            ->get();
        
        return response()->json($letras);
    }
    
  
    public function agregarLetraPlanilla(Request $request)
    {
        $request->validate([
            'Serie' => 'required', 'Numero' => 'required', 'CodBanco' => 'required',
            'Ruc' => 'required', 'Cliente' => 'required', 'NroLetra' => 'required',
            'Vencimiento' => 'required|date', 'Importe' => 'required|numeric'
        ]);

        try {
            DB::connection($this->connection)->table('PllaDetLetras')->insert($request->all());
            $totalPlanilla = DB::connection($this->connection)->table('PllaDetLetras')
                                ->where('Serie', $request->Serie)->where('Numero', $request->Numero)
                                ->sum('Importe');
                                
            return response()->json(['success' => true, 'total' => $totalPlanilla]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
    
   
    public function quitarLetraPlanilla($id) 
    {
        try {
            $detalle = DB::connection($this->connection)->table('PllaDetLetras')->where('Orden', $id)->first();
            if ($detalle) {
                DB::connection($this->connection)->table('PllaDetLetras')->where('Orden', $id)->delete();
                $totalPlanilla = DB::connection($this->connection)->table('PllaDetLetras')
                                ->where('Serie', $detalle->Serie)->where('Numero', $detalle->Numero)
                                ->sum('Importe');
                return response()->json(['success' => true, 'total' => $totalPlanilla]);
            }
            return response()->json(['success' => false, 'message' => 'Item no encontrado'], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
}