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

class MovimientosExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
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
            'Origen',
            'Tipo',
            'Fecha',
            'Moneda',
            'Monto (S/)',
            'Detalle'
        ];
    }

    public function map($row): array
    {
        // Determinar origen
        $origen = (isset($row->Numero) && !isset($row->Cuenta)) ? 'Caja' : 'Banco';

        // Mapear tipo
        $tipoTexto = match($row->Tipo ?? 0) {
            1 => 'Ingreso',
            2 => 'Egreso',
            5 => 'Cobranza',
            default => 'Otro'
        };

        // Moneda
        $moneda = ($row->Moneda ?? 1) == 1 ? 'PEN' : 'USD';

        // Formato de fecha con hora
        $fecha = $row->Fecha ?? $row->fecha;
        $fechaFormateada = $fecha ? \Carbon\Carbon::parse($fecha)->format('d/m/Y H:i') : '';

        // Monto con signo explícito (opcional, pero útil)
        $monto = $row->Monto ?? 0;
        $tipoClase = match($row->Tipo ?? 0) {
            1, 5 => 'ingreso', 
            2 => 'egreso',
            default => 'otro'
        };
        $montoConSigno = ($tipoClase === 'ingreso') ? $monto : -$monto;

        return [
            $row->Documento ?? $row->Cuenta ?? '',
            $origen,
            $tipoTexto,
            $fechaFormateada,
            $moneda,
            $montoConSigno, // Excel manejará el signo; puedes usar solo $monto si prefieres sin signo
            $row->Razon ?? $row->Clase ?? '—'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '11998E']], // Verde similar al de tu UI
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }
}