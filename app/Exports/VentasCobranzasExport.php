<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class VentasCobranzasExport implements FromView, ShouldAutoSize
{
    protected $datosTabla;
    protected $totales;

    /**
    * Recibimos los datos desde el controlador
    */
    public function __construct(array $datosTabla, array $totales)
    {
        $this->datosTabla = $datosTabla;
        $this->totales = $totales;
    }

    /**
    * Le decimos a Laravel Excel que use una vista de Blade
    * para renderizar la hoja de cálculo.
    */
    public function view(): View
    {
        return view('reportes.exports.ventas-cobranzas-excel', [
            'datosTabla' => $this->datosTabla,
            'totales' => $this->totales
        ]);
    }
}