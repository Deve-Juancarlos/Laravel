<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PDT621Export implements FromCollection, WithHeadings
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
        ];
    }
}