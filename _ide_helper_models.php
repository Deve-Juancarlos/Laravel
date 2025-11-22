<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property string|null $usuario
 * @property string|null $tipousuario
 * @property int|null $idusuario
 * @property string|null $password
 * @property string|null $remember_token
 * @property string $created_at
 * @property string $updated_at
 * @property int $id
 * @property string $estado
 * @property string|null $avatar
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LibroDiario> $asientosDiario
 * @property-read int|null $asientos_diario_count
 * @property-read \App\Models\Empleado|null $empleado
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Notificacion> $notificaciones
 * @property-read int|null $notificaciones_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccesoWeb newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccesoWeb newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccesoWeb query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccesoWeb whereAvatar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccesoWeb whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccesoWeb whereEstado($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccesoWeb whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccesoWeb whereIdusuario($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccesoWeb wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccesoWeb whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccesoWeb whereTipousuario($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccesoWeb whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AccesoWeb whereUsuario($value)
 * @mixin \Eloquent
 */
	class AccesoWeb extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $Codclie
 * @property string|null $tipoDoc
 * @property string|null $Documento
 * @property string $Razon
 * @property string|null $Direccion
 * @property string|null $Telefono1
 * @property string|null $Telefono2
 * @property string|null $Fax
 * @property string|null $Celular
 * @property string|null $Nextel
 * @property int $Maymin
 * @property string|null $Fecha
 * @property string $Zona
 * @property int|null $TipoNeg
 * @property int|null $TipoClie
 * @property int|null $Vendedor
 * @property string|null $Email
 * @property string|null $Limite
 * @property bool|null $Activo
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CtaCliente> $cuentasPorCobrar
 * @property-read int|null $cuentas_por_cobrar_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Doccab> $facturas
 * @property-read int|null $facturas_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, NotaCredito> $notasCredito
 * @property-read int|null $notas_credito_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereActivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereCelular($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereCodclie($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereDireccion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereDocumento($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereFax($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereFecha($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereLimite($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereMaymin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereNextel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereRazon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereTelefono1($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereTelefono2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereTipoClie($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereTipoDoc($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereTipoNeg($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereVendedor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cliente whereZona($value)
 * @mixin \Eloquent
 */
	class Cliente extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $Id
 * @property string $Serie
 * @property string $Numero
 * @property string $TipoDoc
 * @property int $CodProv
 * @property string $FechaEmision
 * @property string|null $FechaVencimiento
 * @property int $Moneda
 * @property string|null $Cambio
 * @property string $BaseAfecta
 * @property string $BaseInafecta
 * @property string $Igv
 * @property string $Total
 * @property string $Estado
 * @property string|null $Glosa
 * @property int|null $OrdenCompraId
 * @property int|null $asiento_id
 * @property int|null $UsuarioId
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $MontoPercepcion
 * @property string|null $NroPercepcionSerie
 * @property string|null $NroPercepcionNumero
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CompraDet> $detalles
 * @property-read int|null $detalles_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompraCab newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompraCab newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompraCab query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompraCab whereAsientoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompraCab whereBaseAfecta($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompraCab whereBaseInafecta($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompraCab whereCambio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompraCab whereCodProv($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompraCab whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompraCab whereEstado($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompraCab whereFechaEmision($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompraCab whereFechaVencimiento($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompraCab whereGlosa($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompraCab whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompraCab whereIgv($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompraCab whereMoneda($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompraCab whereMontoPercepcion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompraCab whereNroPercepcionNumero($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompraCab whereNroPercepcionSerie($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompraCab whereNumero($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompraCab whereOrdenCompraId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompraCab whereSerie($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompraCab whereTipoDoc($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompraCab whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompraCab whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompraCab whereUsuarioId($value)
 * @mixin \Eloquent
 */
	class CompraCab extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $Id
 * @property int $CompraId
 * @property string $CodPro
 * @property string $Cantidad
 * @property string $CostoUnitario
 * @property string $Subtotal
 * @property string|null $Lote
 * @property string|null $Vencimiento
 * @property-read \App\Models\CompraCab $cabecera
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompraDet newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompraDet newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompraDet query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompraDet whereCantidad($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompraDet whereCodPro($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompraDet whereCompraId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompraDet whereCostoUnitario($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompraDet whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompraDet whereLote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompraDet whereSubtotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CompraDet whereVencimiento($value)
 * @mixin \Eloquent
 */
	class CompraDet extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $Id
 * @property int $CodClie
 * @property string $NumeroConstancia
 * @property string $FechaPago
 * @property string $Monto
 * @property string $DoccabNumero
 * @property int $DoccabTipo
 * @property int|null $UsuarioId
 * @property string|null $created_at
 * @property-read \App\Models\Cliente $cliente
 * @property-read \App\Models\Doccab $documento
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConstanciaDetraccion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConstanciaDetraccion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConstanciaDetraccion query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConstanciaDetraccion whereCodClie($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConstanciaDetraccion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConstanciaDetraccion whereDoccabNumero($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConstanciaDetraccion whereDoccabTipo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConstanciaDetraccion whereFechaPago($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConstanciaDetraccion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConstanciaDetraccion whereMonto($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConstanciaDetraccion whereNumeroConstancia($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConstanciaDetraccion whereUsuarioId($value)
 * @mixin \Eloquent
 */
	class ConstanciaDetraccion extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $Documento
 * @property int $Tipo
 * @property int $CodClie
 * @property string $FechaF
 * @property string|null $FechaV
 * @property string $Importe
 * @property string $Saldo
 * @property int $NroDeuda
 * @property int|null $cliente_id
 * @property string|null $FechaP
 * @property string|null $Utilidades
 * @property-read \App\Models\Cliente $cliente
 * @property-read \App\Models\Doccab|null $doccab
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CtaCliente newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CtaCliente newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CtaCliente query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CtaCliente whereClienteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CtaCliente whereCodClie($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CtaCliente whereDocumento($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CtaCliente whereFechaF($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CtaCliente whereFechaP($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CtaCliente whereFechaV($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CtaCliente whereImporte($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CtaCliente whereNroDeuda($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CtaCliente whereSaldo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CtaCliente whereTipo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CtaCliente whereUtilidades($value)
 * @mixin \Eloquent
 */
	class CtaCliente extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $Numero
 * @property int $Tipo
 * @property int|null $CodClie
 * @property string $Fecha
 * @property int|null $Dias
 * @property string|null $FechaV
 * @property string|null $Bruto
 * @property string|null $Descuento
 * @property string|null $Flete
 * @property string $Subtotal
 * @property string|null $Igv
 * @property string $Total
 * @property int $Moneda
 * @property string|null $Cambio
 * @property int|null $Vendedor
 * @property string|null $Transporte
 * @property int $Eliminado
 * @property int $Impreso
 * @property string|null $NroPedido
 * @property string|null $NroGuia
 * @property string|null $Usuario
 * @property string|null $estado_sunat
 * @property string|null $hash_cdr
 * @property string|null $mensaje_sunat
 * @property string|null $nombre_archivo
 * @property string|null $qr_data
 * @property string|null $MontoDetraccion
 * @property int|null $asiento_id
 * @property string|null $serie_sunat
 * @property int|null $correlativo_sunat
 * @property string $tipo_documento_sunat
 * @property-read \App\Models\Cliente|null $cliente
 * @property-read \App\Models\CtaCliente|null $cuentaPorCobrar
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Docdet> $detalles
 * @property-read int|null $detalles_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doccab newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doccab newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doccab query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doccab whereAsientoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doccab whereBruto($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doccab whereCambio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doccab whereCodClie($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doccab whereCorrelativoSunat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doccab whereDescuento($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doccab whereDias($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doccab whereEliminado($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doccab whereEstadoSunat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doccab whereFecha($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doccab whereFechaV($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doccab whereFlete($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doccab whereHashCdr($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doccab whereIgv($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doccab whereImpreso($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doccab whereMensajeSunat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doccab whereMoneda($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doccab whereMontoDetraccion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doccab whereNombreArchivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doccab whereNroGuia($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doccab whereNroPedido($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doccab whereNumero($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doccab whereQrData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doccab whereSerieSunat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doccab whereSubtotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doccab whereTipo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doccab whereTipoDocumentoSunat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doccab whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doccab whereTransporte($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doccab whereUsuario($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doccab whereVendedor($value)
 * @mixin \Eloquent
 */
	class Doccab extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $Numero
 * @property int $Tipo
 * @property string $Codpro
 * @property string $Lote
 * @property string $Vencimiento
 * @property int $Unimed
 * @property string $Cantidad
 * @property string|null $Adicional
 * @property string $Precio
 * @property string|null $Unidades
 * @property int|null $Almacen
 * @property string|null $Descuento1
 * @property string|null $Descuento2
 * @property string|null $Descuento3
 * @property string $Subtotal
 * @property string|null $Costo
 * @property string|null $stock
 * @property string|null $Codprom
 * @property string|null $Des_cab
 * @property string|null $CodOferta
 * @property string|null $CodAutoriza
 * @property int $Nbonif
 * @property-read \App\Models\Doccab $doccab
 * @property-read \App\Models\Producto|null $producto
 * @method static Builder<static>|Docdet newModelQuery()
 * @method static Builder<static>|Docdet newQuery()
 * @method static Builder<static>|Docdet query()
 * @method static Builder<static>|Docdet whereAdicional($value)
 * @method static Builder<static>|Docdet whereAlmacen($value)
 * @method static Builder<static>|Docdet whereCantidad($value)
 * @method static Builder<static>|Docdet whereCodAutoriza($value)
 * @method static Builder<static>|Docdet whereCodOferta($value)
 * @method static Builder<static>|Docdet whereCodpro($value)
 * @method static Builder<static>|Docdet whereCodprom($value)
 * @method static Builder<static>|Docdet whereCosto($value)
 * @method static Builder<static>|Docdet whereDesCab($value)
 * @method static Builder<static>|Docdet whereDescuento1($value)
 * @method static Builder<static>|Docdet whereDescuento2($value)
 * @method static Builder<static>|Docdet whereDescuento3($value)
 * @method static Builder<static>|Docdet whereLote($value)
 * @method static Builder<static>|Docdet whereNbonif($value)
 * @method static Builder<static>|Docdet whereNumero($value)
 * @method static Builder<static>|Docdet wherePrecio($value)
 * @method static Builder<static>|Docdet whereStock($value)
 * @method static Builder<static>|Docdet whereSubtotal($value)
 * @method static Builder<static>|Docdet whereTipo($value)
 * @method static Builder<static>|Docdet whereUnidades($value)
 * @method static Builder<static>|Docdet whereUnimed($value)
 * @method static Builder<static>|Docdet whereVencimiento($value)
 * @mixin \Eloquent
 */
	class Docdet extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $Codemp
 * @property string $Nombre
 * @property string|null $Direccion
 * @property string|null $Documento
 * @property string|null $Telefono1
 * @property string|null $Telefono2
 * @property string|null $Celular
 * @property string|null $Nextel
 * @property string|null $Cumpleaños
 * @property int $Tipo
 * @property-read mixed $telefono_formateado
 * @property-read \App\Models\AccesoWeb|null $usuarioWeb
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Empleado newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Empleado newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Empleado query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Empleado whereCelular($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Empleado whereCodemp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Empleado whereCumpleaños($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Empleado whereDireccion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Empleado whereDocumento($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Empleado whereNextel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Empleado whereNombre($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Empleado whereTelefono1($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Empleado whereTelefono2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Empleado whereTipo($value)
 * @mixin \Eloquent
 */
	class Empleado extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $CodLab
 * @property string $Descripcion
 * @property bool|null $Mantiene
 * @property bool|null $Importado
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Producto> $productos
 * @property-read int|null $productos_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Laboratorio newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Laboratorio newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Laboratorio query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Laboratorio whereCodLab($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Laboratorio whereDescripcion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Laboratorio whereImportado($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Laboratorio whereMantiene($value)
 * @mixin \Eloquent
 */
	class Laboratorio extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $numero
 * @property \Illuminate\Support\Carbon $fecha
 * @property string $glosa
 * @property numeric|null $total_debe
 * @property numeric|null $total_haber
 * @property bool|null $balanceado
 * @property string|null $estado
 * @property int|null $usuario_id
 * @property string|null $observaciones
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LibroDiarioDetalle> $detalles
 * @property-read int|null $detalles_count
 * @property-read string $fecha_formateada
 * @property-read \App\Models\AccesoWeb|null $usuario
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LibroDiario newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LibroDiario newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LibroDiario query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LibroDiario whereBalanceado($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LibroDiario whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LibroDiario whereEstado($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LibroDiario whereFecha($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LibroDiario whereGlosa($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LibroDiario whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LibroDiario whereNumero($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LibroDiario whereObservaciones($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LibroDiario whereTotalDebe($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LibroDiario whereTotalHaber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LibroDiario whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LibroDiario whereUsuarioId($value)
 * @mixin \Eloquent
 */
	class LibroDiario extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $asiento_id
 * @property string $cuenta_contable
 * @property numeric|null $debe
 * @property numeric|null $haber
 * @property string $concepto
 * @property string|null $documento_referencia
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\LibroDiario $asiento
 * @property-read \App\Models\PlanCuentas $cuenta
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LibroDiarioDetalle newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LibroDiarioDetalle newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LibroDiarioDetalle query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LibroDiarioDetalle whereAsientoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LibroDiarioDetalle whereConcepto($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LibroDiarioDetalle whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LibroDiarioDetalle whereCuentaContable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LibroDiarioDetalle whereDebe($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LibroDiarioDetalle whereDocumentoReferencia($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LibroDiarioDetalle whereHaber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LibroDiarioDetalle whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LibroDiarioDetalle whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class LibroDiarioDetalle extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $Numero
 * @property int $TipoNota
 * @property \Illuminate\Support\Carbon|null $Fecha
 * @property string|null $Documento
 * @property int|null $TipoDoc
 * @property int $Codclie
 * @property numeric|null $Bruto
 * @property numeric|null $Descuento
 * @property numeric|null $Flete
 * @property numeric $Monto
 * @property numeric $Igv
 * @property numeric $Total
 * @property string|null $Observacion
 * @property int|null $Estado
 * @property bool|null $Anulado
 * @property string|null $GuiaRecojo
 * @property-read \App\Models\Cliente|null $cliente
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotaCredito newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotaCredito newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotaCredito query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotaCredito whereAnulado($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotaCredito whereBruto($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotaCredito whereCodclie($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotaCredito whereDescuento($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotaCredito whereDocumento($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotaCredito whereEstado($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotaCredito whereFecha($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotaCredito whereFlete($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotaCredito whereGuiaRecojo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotaCredito whereIgv($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotaCredito whereMonto($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotaCredito whereNumero($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotaCredito whereObservacion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotaCredito whereTipoDoc($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotaCredito whereTipoNota($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotaCredito whereTotal($value)
 * @mixin \Eloquent
 */
	class NotaCredito extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $usuario_id
 * @property string $tipo
 * @property string $titulo
 * @property string $mensaje
 * @property string|null $icono
 * @property string $color
 * @property string|null $url
 * @property bool $leida
 * @property \Illuminate\Support\Carbon|null $leida_en
 * @property array<array-key, mixed>|null $metadata
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\AccesoWeb|null $usuario
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notificacion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notificacion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notificacion query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notificacion whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notificacion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notificacion whereIcono($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notificacion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notificacion whereLeida($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notificacion whereLeidaEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notificacion whereMensaje($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notificacion whereMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notificacion whereTipo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notificacion whereTitulo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notificacion whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notificacion whereUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notificacion whereUsuarioId($value)
 * @mixin \Eloquent
 */
	class Notificacion extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $codigo
 * @property string $nombre
 * @property string $tipo
 * @property string|null $subtipo
 * @property bool|null $activo
 * @property int|null $nivel
 * @property string|null $cuenta_padre
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LibroDiarioDetalle> $movimientos
 * @property-read int|null $movimientos_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlanCuentas newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlanCuentas newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlanCuentas query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlanCuentas whereActivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlanCuentas whereCodigo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlanCuentas whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlanCuentas whereCuentaPadre($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlanCuentas whereNivel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlanCuentas whereNombre($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlanCuentas whereSubtipo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlanCuentas whereTipo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlanCuentas whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class PlanCuentas extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $CodPro
 * @property string|null $CodBar
 * @property int $Clinea
 * @property int|null $Clase
 * @property string $Nombre
 * @property string|null $CodProv
 * @property string|null $Peso
 * @property string|null $Minimo
 * @property string $Stock
 * @property int $Afecto
 * @property int $Tipo
 * @property string $Costo
 * @property string $PventaMa
 * @property string $PventaMi
 * @property string|null $ComisionH
 * @property string|null $ComisionV
 * @property string|null $ComisionR
 * @property bool $Eliminado
 * @property int $AfecFle
 * @property string|null $CosReal
 * @property string|null $RegSanit
 * @property int|null $TemMax
 * @property int|null $TemMin
 * @property string|null $FecSant
 * @property string|null $Coddigemin
 * @property string|null $CodLab
 * @property string|null $Codlab1
 * @property string|null $Principio
 * @property bool $SujetoADetraccion
 * @property-read \App\Models\Laboratorio|null $laboratorio
 * @method static Builder<static>|Producto newModelQuery()
 * @method static Builder<static>|Producto newQuery()
 * @method static Builder<static>|Producto query()
 * @method static Builder<static>|Producto stockBajo()
 * @method static Builder<static>|Producto whereAfecFle($value)
 * @method static Builder<static>|Producto whereAfecto($value)
 * @method static Builder<static>|Producto whereClase($value)
 * @method static Builder<static>|Producto whereClinea($value)
 * @method static Builder<static>|Producto whereCodBar($value)
 * @method static Builder<static>|Producto whereCodLab($value)
 * @method static Builder<static>|Producto whereCodPro($value)
 * @method static Builder<static>|Producto whereCodProv($value)
 * @method static Builder<static>|Producto whereCoddigemin($value)
 * @method static Builder<static>|Producto whereCodlab1($value)
 * @method static Builder<static>|Producto whereComisionH($value)
 * @method static Builder<static>|Producto whereComisionR($value)
 * @method static Builder<static>|Producto whereComisionV($value)
 * @method static Builder<static>|Producto whereCosReal($value)
 * @method static Builder<static>|Producto whereCosto($value)
 * @method static Builder<static>|Producto whereEliminado($value)
 * @method static Builder<static>|Producto whereFecSant($value)
 * @method static Builder<static>|Producto whereMinimo($value)
 * @method static Builder<static>|Producto whereNombre($value)
 * @method static Builder<static>|Producto wherePeso($value)
 * @method static Builder<static>|Producto wherePrincipio($value)
 * @method static Builder<static>|Producto wherePventaMa($value)
 * @method static Builder<static>|Producto wherePventaMi($value)
 * @method static Builder<static>|Producto whereRegSanit($value)
 * @method static Builder<static>|Producto whereStock($value)
 * @method static Builder<static>|Producto whereSujetoADetraccion($value)
 * @method static Builder<static>|Producto whereTemMax($value)
 * @method static Builder<static>|Producto whereTemMin($value)
 * @method static Builder<static>|Producto whereTipo($value)
 * @mixin \Eloquent
 */
	class Producto extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $codpro
 * @property int $almacen
 * @property string $lote
 * @property string|null $vencimiento
 * @property string|null $saldo
 * @property int $protocolo
 * @property-read \App\Models\Producto $producto
 * @method static Builder<static>|Saldo newModelQuery()
 * @method static Builder<static>|Saldo newQuery()
 * @method static Builder<static>|Saldo query()
 * @method static Builder<static>|Saldo whereAlmacen($value)
 * @method static Builder<static>|Saldo whereCodpro($value)
 * @method static Builder<static>|Saldo whereLote($value)
 * @method static Builder<static>|Saldo whereProtocolo($value)
 * @method static Builder<static>|Saldo whereSaldo($value)
 * @method static Builder<static>|Saldo whereVencimiento($value)
 * @mixin \Eloquent
 */
	class Saldo extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $Codemp
 * @property string $Nombre
 * @property string|null $Direccion
 * @property string|null $Documento
 * @property string|null $Telefono1
 * @property string|null $Telefono2
 * @property string|null $Celular
 * @property string|null $Nextel
 * @property string|null $Cumpleaños
 * @property int $Tipo
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Doccab> $documentos
 * @property-read int|null $documentos_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendedor newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendedor newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendedor query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendedor whereCelular($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendedor whereCodemp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendedor whereCumpleaños($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendedor whereDireccion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendedor whereDocumento($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendedor whereNextel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendedor whereNombre($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendedor whereTelefono1($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendedor whereTelefono2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendedor whereTipo($value)
 * @mixin \Eloquent
 */
	class Vendedor extends \Eloquent {}
}

namespace App\Models\Vistas{
/**
 * @property int|null $Codclie
 * @property string|null $Razon
 * @property string $Documento
 * @property string $FechaF
 * @property string|null $FechaV
 * @property string $Importe
 * @property string $Saldo
 * @property int|null $dias_vencidos
 * @property string $rango
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VistaAgingCartera newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VistaAgingCartera newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VistaAgingCartera query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VistaAgingCartera whereCodclie($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VistaAgingCartera whereDiasVencidos($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VistaAgingCartera whereDocumento($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VistaAgingCartera whereFechaF($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VistaAgingCartera whereFechaV($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VistaAgingCartera whereImporte($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VistaAgingCartera whereRango($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VistaAgingCartera whereRazon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VistaAgingCartera whereSaldo($value)
 * @mixin \Eloquent
 */
	class VistaAgingCartera extends \Eloquent {}
}

namespace App\Models\Vistas{
/**
 * @property string $CodPro
 * @property string $Nombre
 * @property string|null $Laboratorio
 * @property int $Almacen
 * @property string $Lote
 * @property string|null $Vencimiento
 * @property string|null $Stock
 * @property int|null $DiasParaVencer
 * @property string $EstadoVencimiento
 * @property string|null $ValorInventario
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VistaProductosPorVencer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VistaProductosPorVencer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VistaProductosPorVencer query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VistaProductosPorVencer whereAlmacen($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VistaProductosPorVencer whereCodPro($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VistaProductosPorVencer whereDiasParaVencer($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VistaProductosPorVencer whereEstadoVencimiento($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VistaProductosPorVencer whereLaboratorio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VistaProductosPorVencer whereLote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VistaProductosPorVencer whereNombre($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VistaProductosPorVencer whereStock($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VistaProductosPorVencer whereValorInventario($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VistaProductosPorVencer whereVencimiento($value)
 * @mixin \Eloquent
 */
	class VistaProductosPorVencer extends \Eloquent {}
}

