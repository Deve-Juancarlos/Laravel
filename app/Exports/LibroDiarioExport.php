<?php

namespace App\Exports;

use App\Models\LibroDiario;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

class LibroDiarioExport implements FromView, ShouldAutoSize, WithTitle, WithStyles
{
    protected $asientos;
    protected $totales;
    protected $fechaInicio;
    protected $fechaFin;

    /**
     * Recibimos los datos desde el servicio.
     */
    public function __construct(Collection $asientos, array $totales, $fechaInicio, $fechaFin)
    {
        $this->asientos = $asientos;
        $this->totales = $totales;
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
    }

    /**
     * Le decimos a Excel que use una vista Blade para la plantilla.
     */
    public function view(): View
    {
        return view('contabilidad.libros.diario.export_excel', [
            'asientos' => $this->asientos,
            'totales' => $this->totales,
            'fechaInicio' => $this->fechaInicio,
            'fechaFin' => $this->fechaFin
        ]);
    }

    /**
     * Define el nombre de la pestaña en Excel.
     */
    public function title(): string
    {
        return 'Libro Diario';
    }

    /**
     * Aplica estilos (ej. cabecera en negrita).
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Pone en negrita la fila 4 (nuestra cabecera de datos)
            4    => ['font' => ['bold' => true]],
        ];
    }
}