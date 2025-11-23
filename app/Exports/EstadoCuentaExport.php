<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EstadoCuentaExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents
{
    protected $datos;

    public function __construct(array $datos)
    {
        $this->datos = $datos;
    }

    /**
     * Retornamos la colección de movimientos que serán exportados
     */
    public function collection()
    {
        // Esperamos que 'movimientos' sea una colección/array de objetos
        return collect($this->datos['movimientos'] ?? []);
    }

    /**
     * Encabezados de la tabla de movimientos
     */
    public function headings(): array
    {
        return [
            'Número',
            'Fecha',
            'Vencimiento',
            'Tipo',
            'Serie/Referencia',
            'Debe (S/)',
            'Haber (S/)',
            'Saldo Acumulado (S/)'
        ];
    }

    /**
     * Mapeo por fila
     */
    public function map($row): array
    {
        return [
            $row->Numero ?? $row->numero ?? '',
            isset($row->Fecha) ? \Carbon\Carbon::parse($row->Fecha)->format('d/m/Y') : '',
            isset($row->Vencimiento) ? \Carbon\Carbon::parse($row->Vencimiento)->format('d/m/Y') : '',
            $row->tipo_movimiento ?? $row->Tipo ?? $row->tipo ?? '',
            $row->Serie ?? $row->serie ?? $row->referencia ?? '',
            isset($row->debe) ? (float) $row->debe : ((isset($row->Total) && isset($row->Saldo) && isset($row->haber)) ? 0 : 0),
            isset($row->haber) ? (float) $row->haber : 0,
            isset($row->saldo_acumulado) ? (float) $row->saldo_acumulado : (isset($row->Saldo) ? (float) $row->Saldo : 0)
        ];
    }

    /**
     * Estilos simples: encabezado en negrita
     */
    public function styles(Worksheet $sheet)
    {
        return [
            5 => ['font' => ['bold' => true]] // asumimos que arrancamos datos en fila 5 (luego del header personalizado)
        ];
    }

    /**
     * Evento para insertar encabezado con información del cliente
     */
    public function registerEvents(): array
    {
        $cliente = $this->datos['cliente'] ?? null;
        $resumen = $this->datos['resumen'] ?? [];

        return [
            AfterSheet::class => function(AfterSheet $event) use ($cliente, $resumen) {
                $sheet = $event->sheet->getDelegate();

                // Escribimos información del cliente en las primeras filas
                $sheet->setCellValue('A1', 'ESTADO DE CUENTA');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

                if ($cliente) {
                    $sheet->setCellValue('A2', 'Cliente: ' . ($cliente->Razon ?? $cliente->RazonSocial ?? $cliente->razon ?? ''));
                    $sheet->setCellValue('A3', 'RUC: ' . ($cliente->Ruc ?? $cliente->RUC ?? $cliente->ruc ?? ''));
                }

                // Periodo (si está disponible)
                $fechaDesde = $this->datos['fecha_desde'] ?? $this->datos['fechaDesde'] ?? '';
                $fechaHasta = $this->datos['fecha_hasta'] ?? $this->datos['fechaHasta'] ?? '';
                if ($fechaDesde || $fechaHasta) {
                    $sheet->setCellValue('A4', 'Periodo: ' . ($fechaDesde ? \Carbon\Carbon::parse($fechaDesde)->format('d/m/Y') : '') . ' - ' . ($fechaHasta ? \Carbon\Carbon::parse($fechaHasta)->format('d/m/Y') : ''));
                }

                // Ajustes de formato: negrita en encabezados y ancho automático
                $sheet->getStyle('A5:H5')->getFont()->setBold(true);
                $lastRow = $sheet->getHighestRow();
                $sheet->getStyle("F6:H{$lastRow}")->getNumberFormat()->setFormatCode('#,##0.00');
            }
        ];
    }
}
