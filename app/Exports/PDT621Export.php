<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class PDT621Export implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $datos;

    public function __construct($datos)
    {
        $this->datos = $datos;
    }

    public function collection()
    {
        // Ajusta esto según la estructura de tus datos
        return collect($this->datos['registros'] ?? []);
    }

    public function headings(): array
    {
        // Define los encabezados según el formato PDT 621
        return [
            'Periodo',
            'RUC',
            'Razón Social',
            'Código',
            'Tipo de Documento',
            'Importe (S/)',
            'Observaciones'
        ];
    }

    public function map($row): array
    {
        // Mapear un registro según la estructura esperada
        return [
            $row['periodo'] ?? $row->periodo ?? '',
            $row['ruc'] ?? $row->ruc ?? '',
            $row['razon'] ?? $row->razon_social ?? $row->razonSocial ?? '',
            $row['codigo'] ?? $row->codigo ?? '',
            $row['tipo_documento'] ?? $row->tipo_documento ?? '',
            isset($row['importe']) ? (float) $row['importe'] : (isset($row->importe) ? (float) $row->importe : 0),
            $row['observaciones'] ?? $row->observaciones ?? ''
        ];
    }
}