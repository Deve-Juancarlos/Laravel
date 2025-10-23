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

class CuentasCorrientesExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
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
            'Código Cliente',
            'Documento',
            'Tipo',
            'Fecha Promesa',
            'Importe (S/)',
            'Fecha Ingreso',
            'Estado'
        ];
    }

    public function map($row): array
    {
        // Mapear el tipo numérico a texto
        $tipoNombre = match($row->Tipo ?? 0) {
            1 => 'Factura',
            2 => 'Boleta',
            7 => 'Letra',
            8 => 'Nota Crédito',
            default => 'Tipo ' . ($row->Tipo ?? 'N/A'),
        };

      
        $estado = ($row->Importe ?? 0) < 0 ? 'Deudor' : 'Acreedor';

        return [
            $row->CodClie ?? '',
            $row->Documento ?? '',
            $tipoNombre,
            $row->FechaF ? \Carbon\Carbon::parse($row->FechaF)->format('d/m/Y') : '',
            $row->Importe ?? 0,
            $row->FechaV ? \Carbon\Carbon::parse($row->FechaV)->format('d/m/Y') : '',
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