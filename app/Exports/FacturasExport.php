<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class FacturasExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $data;

    public function __construct(Collection $data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'Documento',
            'Tipo',
            'Cliente',
            'Fecha',
            'SubTotal (S/)',
            'IGV (S/)',
            'Total (S/)',
            'Estado'
        ];
    }

    public function map($row): array
    {
        // Mapear tipo numérico a texto
        $tipoNombre = match($row->TipoDoc ?? $row->tipo ?? 0) {
            1 => 'Factura',
            2 => 'Boleta',
            3 => 'Nota Crédito',
            default => 'Otro',
        };

        // Determinar estado
        if (isset($row->Anulado) && $row->Anulado) {
            $estado = 'Anulado';
        } elseif (isset($row->Estado) && $row->Estado == 1) {
            $estado = 'Activo';
        } else {
            $estado = 'Sin estado';
        }

        return [
            $row->Numero ?? $row->documento ?? '',
            $tipoNombre,
            $row->Codclie ?? '—',
            $row->Fecha ? \Carbon\Carbon::parse($row->Fecha)->format('d/m/Y') : '',
            $row->SubTotal ?? 0,
            $row->Igv ?? 0,
            $row->Total ?? $row->monto ?? 0,
            $estado
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '2C3E50']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }
}