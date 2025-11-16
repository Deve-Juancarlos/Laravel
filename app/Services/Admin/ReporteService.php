<?php

namespace App\Services\Admin;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReporteService
{
    /**
     * Reporte de Ventas por Período
     */
    public function reporteVentasPeriodo($fechaInicio, $fechaFin)
    {
        return DB::table('Doccab')
            ->join('Clientes', 'Doccab.CodClie', '=', 'Clientes.Codclie')
            ->join('Empleados', 'Doccab.Vendedor', '=', 'Empleados.Codemp')
            ->leftJoin('TiposDocumentoSUNAT', 'Doccab.tipo_documento_sunat', '=', 'TiposDocumentoSUNAT.Codigo')
            ->select(
                'Doccab.Numero',
                'Doccab.Tipo',
                'Doccab.Fecha',
                'Clientes.Razon as cliente',
                'Empleados.Nombre as vendedor',
                'TiposDocumentoSUNAT.Descripcion as tipo_doc',
                'Doccab.Subtotal',
                'Doccab.Igv',
                'Doccab.Total',
                'Doccab.estado_sunat'
            )
            ->whereBetween('Doccab.Fecha', [$fechaInicio, $fechaFin])
            ->where('Doccab.Eliminado', 0)
            ->orderBy('Doccab.Fecha', 'desc')
            ->get();
    }

    /**
     * Reporte de Ventas por Cliente
     */
    public function reporteVentasPorCliente($fechaInicio, $fechaFin)
    {
        return DB::table('Doccab')
            ->join('Clientes', 'Doccab.CodClie', '=', 'Clientes.Codclie')
            ->select(
                'Clientes.Codclie',
                'Clientes.Razon',
                'Clientes.Documento',
                'Clientes.Zona',
                DB::raw('COUNT(DISTINCT Doccab.Numero) as total_documentos'),
                DB::raw('SUM(Doccab.Total) as total_vendido'),
                DB::raw('AVG(Doccab.Total) as ticket_promedio')
            )
            ->whereBetween('Doccab.Fecha', [$fechaInicio, $fechaFin])
            ->where('Doccab.Eliminado', 0)
            ->groupBy('Clientes.Codclie', 'Clientes.Razon', 'Clientes.Documento', 'Clientes.Zona')
            ->orderByDesc('total_vendido')
            ->get();
    }

    /**
     * Reporte de Ventas por Producto
     */
    public function reporteVentasPorProducto($fechaInicio, $fechaFin)
    {
        return DB::table('Docdet')
            ->join('Doccab', function($join) use ($fechaInicio, $fechaFin) {
                $join->on('Docdet.Numero', '=', 'Doccab.Numero')
                     ->on('Docdet.Tipo', '=', 'Doccab.Tipo')
                     ->whereBetween('Doccab.Fecha', [$fechaInicio, $fechaFin])
                     ->where('Doccab.Eliminado', 0);
            })
            ->join('Productos', 'Docdet.Codpro', '=', 'Productos.CodPro')
            ->leftJoin('Laboratorios', 'Productos.CodProv', '=', 'Laboratorios.CodLab')
            ->select(
                'Productos.CodPro',
                'Productos.Nombre',
                'Laboratorios.Descripcion as laboratorio',
                DB::raw('SUM(Docdet.Cantidad) as cantidad_vendida'),
                DB::raw('SUM(Docdet.Subtotal) as total_vendido'),
                DB::raw('SUM(Docdet.Cantidad * Docdet.Costo) as costo_total'),
                DB::raw('SUM(Docdet.Subtotal) - SUM(Docdet.Cantidad * Docdet.Costo) as utilidad'),
                DB::raw('CASE 
                    WHEN SUM(Docdet.Cantidad * Docdet.Costo) > 0 
                    THEN ((SUM(Docdet.Subtotal) - SUM(Docdet.Cantidad * Docdet.Costo)) / SUM(Docdet.Cantidad * Docdet.Costo)) * 100 
                    ELSE 0 
                END as margen_porcentaje')
            )
            ->groupBy('Productos.CodPro', 'Productos.Nombre', 'Laboratorios.Descripcion')
            ->orderByDesc('total_vendido')
            ->get();
    }

    /**
     * Reporte de Ventas por Vendedor
     */
    public function reporteVentasPorVendedor($fechaInicio, $fechaFin)
    {
        return DB::table('Doccab')
            ->join('Empleados', 'Doccab.Vendedor', '=', 'Empleados.Codemp')
            ->select(
                'Empleados.Codemp',
                'Empleados.Nombre',
                DB::raw('COUNT(*) as total_ventas'),
                DB::raw('SUM(Doccab.Total) as total_vendido'),
                DB::raw('AVG(Doccab.Total) as ticket_promedio'),
                DB::raw('COUNT(DISTINCT Doccab.CodClie) as clientes_atendidos')
            )
            ->whereBetween('Doccab.Fecha', [$fechaInicio, $fechaFin])
            ->where('Doccab.Eliminado', 0)
            ->groupBy('Empleados.Codemp', 'Empleados.Nombre')
            ->orderByDesc('total_vendido')
            ->get();
    }

    /**
     * Reporte Aging Cuentas por Cobrar (usa vista vagingcartera)
     */
    public function reporteAgingCuentasPorCobrar()
    {
        return DB::table('v_aging_cartera')
            ->select(
                'Codclie',
                'Razon',
                'Documento',
                'FechaF',
                'FechaV',
                'Importe',
                'Saldo',
                'dias_vencidos',
                'rango'
            )
            ->orderBy('dias_vencidos', 'desc')
            ->get();
    }

    /**
     * Resumen Cuentas por Cobrar
     */
    public function resumenCuentasPorCobrar()
    {
        $hoy = Carbon::today();
        
        return [
            'total' => DB::table('CtaCliente')->where('Saldo', '>', 0)->sum('Saldo'),
            'vigente' => DB::table('CtaCliente')
                ->where('Saldo', '>', 0)
                ->where('FechaV', '>=', $hoy)
                ->sum('Saldo'),
            'vencido_1_30' => DB::table('v_aging_cartera')
                ->where('rango', '1-30')
                ->sum('Saldo'),
            'vencido_31_60' => DB::table('v_aging_cartera')
                ->where('rango', '31-60')
                ->sum('Saldo'),
            'vencido_61_90' => DB::table('v_aging_cartera')
                ->where('rango', '61-90')
                ->sum('Saldo'),
            'vencido_mas_90' => DB::table('v_aging_cartera')
                ->where('rango', '90+')
                ->sum('Saldo'),

        ];
    }

    /**
     * Reporte Aging Cuentas por Pagar (usa vista vagingcuentasporpagar)
     */
    public function reporteAgingCuentasPorPagar()
    {
        return DB::table('v_aging_cuentas_por_pagar')
            ->select(
                'CodProv',
                'RazonSocial',
                'Ruc',
                'Documento',
                'FechaFactura',
                'FechaVencimiento',
                'Importe',
                'Saldo',
                'dias_vencidos',
                'rango_vencimiento'
            )
            ->orderBy('dias_vencidos', 'desc')
            ->get();
    }

    /**
     * Resumen Cuentas por Pagar
     */
    public function resumenCuentasPorPagar()
    {
        $hoy = Carbon::today();
        
        return [
            'total' => DB::table('CtaProveedor')->where('Saldo', '>', 0)->sum('Saldo'),
            'vigente' => DB::table('CtaProveedor')
                ->where('Saldo', '>', 0)
                ->where('FechaV', '>=', $hoy)
                ->sum('Saldo'),
            'vencido_1_30' => DB::table('v_aging_cuentas_por_pagar')
                ->where('rango_vencimiento', '1-30')
                ->sum('Saldo'),
            'vencido_31_60' => DB::table('v_aging_cuentas_por_pagar')
                ->where('rango_vencimiento', '31-60')
                ->sum('Saldo'),
            'vencido_61_90' => DB::table('v_aging_cuentas_por_pagar')
                ->where('rango_vencimiento', '61-90')
                ->sum('Saldo'),
            'vencido_mas_90' => DB::table('v_aging_cuentas_por_pagar')
                ->where('rango_vencimiento', '90+')
                ->sum('Saldo'),
        ];
    }

    /**
     * Reporte de Inventario Valorizado
     */
    public function reporteInventarioValorado($tipoAgrupacion = 'laboratorio')
    {
        $query = DB::table('Saldos')
            ->join('Productos', 'Saldos.codpro', '=', 'Productos.CodPro')
            ->leftJoin('Laboratorios', 'Productos.CodProv', '=', 'Laboratorios.CodLab')
            ->where('Productos.Eliminado', 0)
            ->where('Saldos.saldo', '>', 0);

        if ($tipoAgrupacion == 'laboratorio') {
            return $query->select(
                    'Laboratorios.CodLab',
                    'Laboratorios.Descripcion as laboratorio',
                    DB::raw('SUM(Saldos.saldo) as stock_total'),
                    DB::raw('SUM(Saldos.saldo * Productos.Costo) as valor_total'),
                    DB::raw('COUNT(DISTINCT Productos.CodPro) as total_productos')
                )
                ->groupBy('Laboratorios.CodLab', 'Laboratorios.Descripcion')
                ->orderByDesc('valor_total')
                ->get();
        } elseif ($tipoAgrupacion == 'producto') {
            return $query->select(
                    'Productos.CodPro',
                    'Productos.Nombre',
                    'Laboratorios.Descripcion as laboratorio',
                    DB::raw('SUM(Saldos.saldo) as stock_total'),
                    'Productos.Costo',
                    DB::raw('SUM(Saldos.saldo * Productos.Costo) as valor_total')
                )
                ->groupBy('Productos.CodPro', 'Productos.Nombre', 'Laboratorios.Descripcion', 'Productos.Costo')
                ->orderByDesc('valor_total')
                ->get();
        } else {
            return $query->select(
                    'Saldos.almacen',
                    DB::raw('SUM(Saldos.saldo * Productos.Costo) as valor_total'),
                    DB::raw('COUNT(DISTINCT Productos.CodPro) as total_productos')
                )
                ->groupBy('Saldos.almacen')
                ->get();
        }
    }

    /**
     * Reporte de Productos por Vencer (usa vista vproductosporvencer)
     */
    public function reporteProductosVencer($dias = 60)
    {
        return DB::table('v_productos_por_vencer')
            ->where('DiasParaVencer', '<=', $dias)
            ->where('DiasParaVencer', '>', 0)
            ->orderBy('DiasParaVencer', 'asc')
            ->get();
    }

   

    /**
     * Registro de Ventas SUNAT (usa vista vsunatregistroventas82)
     */
    public function reporteRegistroVentasSunat($periodo)
    {
        list($anio, $mes) = explode('-', $periodo);
        
        return DB::table('v_sunat_registro_ventas_8_2')
            ->whereYear('fecha_emision', $anio)
            ->whereMonth('fecha_emision', $mes)
            ->orderBy('fecha_emision', 'asc')
            ->get();
    }

    /**
     * Registro de Compras SUNAT
     */
    public function reporteRegistroComprasSunat($periodo)
    {
        list($anio, $mes) = explode('-', $periodo);
        
        return DB::table('CompraCab')
            ->join('Proveedores', 'CompraCab.CodProv', '=', 'Proveedores.CodProv')
            ->join('TiposDocumentoSUNAT', 'CompraCab.TipoDoc', '=', 'TiposDocumentoSUNAT.Codigo')
            ->select(
                'CompraCab.FechaEmision',
                'TiposDocumentoSUNAT.Descripcion as tipo_doc',
                'CompraCab.Serie',
                'CompraCab.Numero',
                'Proveedores.Ruc',
                'Proveedores.RazonSocial',
                'CompraCab.BaseAfecta',
                'CompraCab.Igv',
                'CompraCab.Total'
            )
            ->whereYear('CompraCab.FechaEmision', $anio)
            ->whereMonth('CompraCab.FechaEmision', $mes)
            ->where('CompraCab.Estado', '!=', 'ANULADO')
            ->orderBy('CompraCab.FechaEmision', 'asc')
            ->get();
    }

    /**
     * Reporte de Rentabilidad por Producto
     */
    public function reporteRentabilidadProductos($fechaInicio, $fechaFin)
    {
        return $this->reporteVentasPorProducto($fechaInicio, $fechaFin);
    }

    /**
     * Exportar a Excel
     */
    public function exportarExcel($tipo, $fechaInicio, $fechaFin)
    {
        // Implementar exportación según tipo
        // Usar PhpSpreadsheet o similar
    }
}
