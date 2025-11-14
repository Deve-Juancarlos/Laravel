<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;


class LibroDiarioExport implements FromView, WithTitle, WithStyles, WithColumnWidths
{
    protected $asientos;
    protected $totales;
    protected $fechaInicio;
    protected $fechaFin;

    public function __construct($asientos, $totales, $fechaInicio, $fechaFin)
    {
        $this->asientos = $asientos;
        $this->totales = $totales;
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
    }

 
    public function view(): View
    {
        return view('contabilidad.libros.diario.export_excel', [
            'asientos' => $this->asientos,
            'totales' => $this->totales,
            'fechaInicio' => $this->fechaInicio,
            'fechaFin' => $this->fechaFin
        ]);
    }

    
    public function title(): string
    {
        return 'Libro Diario';
    }

    
    public function columnWidths(): array
    {
        return [
            'A' => 12, 
            'B' => 12, 
            'C' => 12,
            'D' => 35, 
            'E' => 40, 
            'F' => 15, 
            'G' => 15, 
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            
            1    => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'D9EAD3'],
                ]
            ],

            
            2    => ['font' => ['bold' => true]],
        ];
    }
}