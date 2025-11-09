<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Carbon\Carbon;

class LibroMayorExport implements FromView, ShouldAutoSize, WithEvents
{
    protected $movimientos;
    protected $tipo;
    protected $fechaInicio;
    protected $fechaFin;

    public function __construct($movimientos, $tipo, $fechaInicio, $fechaFin)
    {
        $this->movimientos = $movimientos;
        $this->tipo = $tipo;
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
    }

    /**
     * Retorna la vista que será exportada
     */
    public function view(): View
    {
        return view('contabilidad.libros.mayor.exports.excel', [
            'movimientos' => $this->movimientos,
            'tipo' => $this->tipo,
            'fechaInicio' => $this->fechaInicio,
            'fechaFin' => $this->fechaFin,
        ]);
    }

    /**
     * Evento para aplicar estilos al Excel
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // ✅ Título principal
                $sheet->mergeCells('A1:E1');
                $sheet->setCellValue('A1', 'LIBRO MAYOR - ' . strtoupper($this->tipo));
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

                // ✅ Rango de fechas
                $sheet->mergeCells('A2:E2');
                $sheet->setCellValue('A2', 'Periodo: ' . Carbon::parse($this->fechaInicio)->format('d/m/Y') . ' al ' . Carbon::parse($this->fechaFin)->format('d/m/Y'));
                $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');
                $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(11);

                // ✅ Cabeceras con fondo azul
                $sheet->getStyle('A4:E4')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => [
                        'fillType' => 'solid',
                        'startColor' => ['rgb' => '0070C0']
                    ],
                    'alignment' => ['horizontal' => 'center']
                ]);

                // ✅ Bordes y formato de totales
                $lastRow = $sheet->getHighestRow();
                $sheet->getStyle("A4:E{$lastRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => 'thin',
                            'color' => ['rgb' => 'CCCCCC']
                        ]
                    ]
                ]);

                // ✅ Totales en negrita
                $sheet->getStyle("A{$lastRow}:E{$lastRow}")
                      ->getFont()->setBold(true);

                // ✅ Formato contable (debe, haber, saldo)
                $sheet->getStyle("C5:E{$lastRow}")
                      ->getNumberFormat()
                      ->setFormatCode('#,##0.00');
            }
        ];
    }
}
