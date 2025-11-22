<?php

namespace App\Services;

use DOMDocument;
use DOMElement;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Servicio para generar XML UBL 2.1 según estándar SUNAT
 * Cumple con formato de Comprobante de Pago Electrónico
 */
class XmlSunatService
{
    protected $empresa;
    protected $factura;
    protected $detalles;
    protected $documento;

    public function __construct()
    {
        $this->documento = new DOMDocument('1.0', 'utf-8');
        $this->documento->formatOutput = true;
    }

    /**
     * Genera XML UBL 2.1 para una factura
     * 
     * @param array $empresa Datos de la empresa
     * @param array $factura Datos de la factura
     * @param array $detalles Líneas de detalle
     * @return string XML generado
     */
    public function generarXmlFactura($empresa, $factura, $detalles)
    {
        try {
            $this->empresa = $empresa;
            $this->factura = $factura;
            $this->detalles = $detalles;

            // Crear elemento raíz
            $invoiceElement = $this->documento->createElement('Invoice');
            $invoiceElement->setAttribute('xmlns', 'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2');
            $invoiceElement->setAttribute('xmlns:cac', 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
            $invoiceElement->setAttribute('xmlns:cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
            $invoiceElement->setAttribute('xmlns:ext', 'urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2');

            $this->documento->appendChild($invoiceElement);

            // Información básica
            $this->agregarInformacionBasica($invoiceElement);

            // Empresa emisora
            $this->agregarEmisor($invoiceElement);

            // Cliente receptor
            $this->agregarReceptor($invoiceElement);

            // Totales y moneda
            $this->agregarTotales($invoiceElement);

            // Líneas de detalle
            $this->agregarLineas($invoiceElement);

            $xml = $this->documento->saveXML();
            Log::info('XML generado exitosamente para factura ' . $factura['numero']);

            return $xml;

        } catch (\Exception $e) {
            Log::error('Error al generar XML: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Información básica de la factura
     */
    private function agregarInformacionBasica($parent)
    {
        $tipoComprobante = (int)$this->factura['tipo'] === 1 ? '01' : '03'; // 01=Factura, 03=Boleta
        $fecha = Carbon::parse($this->factura['fecha'])->format('Y-m-d');
        $horaEmision = Carbon::now()->format('H:i:s');

        $this->agregarElemento($parent, 'cbc:UBLVersionID', '2.1');
        $this->agregarElemento($parent, 'cbc:CustomizationID', '1.0');
        $this->agregarElemento($parent, 'cbc:ID', $this->factura['numero']);
        $this->agregarElemento($parent, 'cbc:IssueDate', $fecha);
        $this->agregarElemento($parent, 'cbc:IssueTime', $horaEmision);

        // Tipo de comprobante
        $tipoElement = $this->documento->createElement('cbc:InvoiceTypeCode');
        $tipoElement->setAttribute('listID', '03');
        $tipoElement->nodeValue = $tipoComprobante;
        $parent->appendChild($tipoElement);

        // Moneda
        $monedaCode = $this->factura['moneda'] === 'DOLARES' ? 'USD' : 'PEN';
        $this->agregarElemento($parent, 'cbc:DocumentCurrencyCode', $monedaCode);

        // Nota
        if (!empty($this->factura['observacion'])) {
            $this->agregarElemento($parent, 'cbc:Note', $this->factura['observacion']);
        }
    }

    /**
     * Datos del emisor (empresa)
     */
    private function agregarEmisor($parent)
    {
        $supplier = $this->documento->createElement('cac:AccountingSupplierParty');
        $party = $this->documento->createElement('cac:Party');

        // Identificación
        $partyId = $this->documento->createElement('cac:PartyIdentification');
        $id = $this->documento->createElement('cbc:ID');
        $id->setAttribute('schemeID', '6'); // 6 = RUC
        $id->nodeValue = $this->empresa['ruc'];
        $partyId->appendChild($id);
        $party->appendChild($partyId);

        // Nombre de la empresa
        $partyLegal = $this->documento->createElement('cac:PartyLegalEntity');
        $this->agregarElementoA($partyLegal, 'cbc:RegistrationName', $this->empresa['nombre']);
        $party->appendChild($partyLegal);

        $supplier->appendChild($party);
        $parent->appendChild($supplier);
    }

    /**
     * Datos del receptor (cliente)
     */
    private function agregarReceptor($parent)
    {
        $customer = $this->documento->createElement('cac:AccountingCustomerParty');
        $party = $this->documento->createElement('cac:Party');

        // Tipo de documento del cliente
        $docType = strlen(trim($this->factura['cliente_ruc'])) === 11 ? '6' : '1'; // 6=RUC, 1=DNI
        $partyId = $this->documento->createElement('cac:PartyIdentification');
        $id = $this->documento->createElement('cbc:ID');
        $id->setAttribute('schemeID', $docType);
        $id->nodeValue = $this->factura['cliente_ruc'];
        $partyId->appendChild($id);
        $party->appendChild($partyId);

        // Nombre del cliente
        $partyLegal = $this->documento->createElement('cac:PartyLegalEntity');
        $this->agregarElementoA($partyLegal, 'cbc:RegistrationName', $this->factura['cliente_nombre']);
        $party->appendChild($partyLegal);

        $customer->appendChild($party);
        $parent->appendChild($customer);
    }

    /**
     * Totales y cálculos
     */
    private function agregarTotales($parent)
    {
        $monedaCode = $this->factura['moneda'] === 'DOLARES' ? 'USD' : 'PEN';

        // Descuento global (si existe)
        if ($this->factura['descuento'] > 0) {
            $descuento = $this->documento->createElement('cac:AllowanceCharge');
            $chargeIndicator = $this->documento->createElement('cbc:ChargeIndicator');
            $chargeIndicator->nodeValue = 'false';
            $descuento->appendChild($chargeIndicator);

            $amount = $this->documento->createElement('cbc:Amount');
            $amount->setAttribute('currencyID', $monedaCode);
            $amount->nodeValue = number_format($this->factura['descuento'], 2, '.', '');
            $descuento->appendChild($amount);

            $parent->appendChild($descuento);
        }

        // Totales de impuestos
        $taxTotal = $this->documento->createElement('cac:TaxTotal');
        
        // Total de IGV
        $taxAmount = $this->documento->createElement('cbc:TaxAmount');
        $taxAmount->setAttribute('currencyID', $monedaCode);
        $taxAmount->nodeValue = number_format($this->factura['igv'], 2, '.', '');
        $taxTotal->appendChild($taxAmount);

        // Subtotal por tipo de impuesto
        $taxSubtotal = $this->documento->createElement('cac:TaxSubtotal');
        
        $taxableAmount = $this->documento->createElement('cbc:TaxableAmount');
        $taxableAmount->setAttribute('currencyID', $monedaCode);
        $taxableAmount->nodeValue = number_format($this->factura['subtotal'], 2, '.', '');
        $taxSubtotal->appendChild($taxableAmount);

        $subtotalTaxAmount = $this->documento->createElement('cbc:TaxAmount');
        $subtotalTaxAmount->setAttribute('currencyID', $monedaCode);
        $subtotalTaxAmount->nodeValue = number_format($this->factura['igv'], 2, '.', '');
        $taxSubtotal->appendChild($subtotalTaxAmount);

        // Información del tipo de impuesto (IGV)
        $taxCategory = $this->documento->createElement('cac:TaxCategory');
        $this->agregarElementoA($taxCategory, 'cbc:ID', 'S');
        $this->agregarElementoA($taxCategory, 'cbc:Percent', '18');

        $taxScheme = $this->documento->createElement('cac:TaxScheme');
        $this->agregarElementoA($taxScheme, 'cbc:ID', '1000');
        $this->agregarElementoA($taxScheme, 'cbc:Name', 'IGV');
        $this->agregarElementoA($taxScheme, 'cbc:TaxTypeCode', 'VAT');

        $taxCategory->appendChild($taxScheme);
        $taxSubtotal->appendChild($taxCategory);

        $taxTotal->appendChild($taxSubtotal);
        $parent->appendChild($taxTotal);

        // Totales monetarios
        $monetaryTotal = $this->documento->createElement('cac:LegalMonetaryTotal');

        $lineExtension = $this->documento->createElement('cbc:LineExtensionAmount');
        $lineExtension->setAttribute('currencyID', $monedaCode);
        $lineExtension->nodeValue = number_format($this->factura['subtotal'], 2, '.', '');
        $monetaryTotal->appendChild($lineExtension);

        $taxInclusiveAmount = $this->documento->createElement('cbc:TaxInclusiveAmount');
        $taxInclusiveAmount->setAttribute('currencyID', $monedaCode);
        $taxInclusiveAmount->nodeValue = number_format($this->factura['total'], 2, '.', '');
        $monetaryTotal->appendChild($taxInclusiveAmount);

        $payableAmount = $this->documento->createElement('cbc:PayableAmount');
        $payableAmount->setAttribute('currencyID', $monedaCode);
        $payableAmount->nodeValue = number_format($this->factura['total'], 2, '.', '');
        $monetaryTotal->appendChild($payableAmount);

        $parent->appendChild($monetaryTotal);
    }

    /**
     * Líneas de detalle
     */
    private function agregarLineas($parent)
    {
        $monedaCode = $this->factura['moneda'] === 'DOLARES' ? 'USD' : 'PEN';

        foreach ($this->detalles as $index => $item) {
            $line = $this->documento->createElement('cac:InvoiceLine');

            // ID de línea
            $this->agregarElementoA($line, 'cbc:ID', (string)($index + 1));

            // Cantidad
            $cantidadElement = $this->documento->createElement('cbc:InvoicedQuantity');
            $cantidadElement->setAttribute('unitCode', 'UN');
            $cantidadElement->nodeValue = number_format($item['cantidad'], 2, '.', '');
            $line->appendChild($cantidadElement);

            // Importe de línea (sin IGV)
            $lineExtensionAmount = $this->documento->createElement('cbc:LineExtensionAmount');
            $lineExtensionAmount->setAttribute('currencyID', $monedaCode);
            
            // Calcular base sin IGV
            $subtotalLinea = $item['cantidad'] * $item['precio_unitario'];
            $baseLinea = $subtotalLinea / 1.18; // Quitar IGV
            
            $lineExtensionAmount->nodeValue = number_format($baseLinea, 2, '.', '');
            $line->appendChild($lineExtensionAmount);

            // Información de precios
            $pricingReference = $this->documento->createElement('cac:PricingReference');
            $alternativePrice = $this->documento->createElement('cac:AlternativeConditionPrice');
            
            $priceAmount = $this->documento->createElement('cbc:PriceAmount');
            $priceAmount->setAttribute('currencyID', $monedaCode);
            $precioSinIgv = $item['precio_unitario'] / 1.18;
            $priceAmount->nodeValue = number_format($precioSinIgv, 2, '.', '');
            $alternativePrice->appendChild($priceAmount);
            
            $this->agregarElementoA($alternativePrice, 'cbc:PriceTypeCode', '01');
            $pricingReference->appendChild($alternativePrice);
            $line->appendChild($pricingReference);

            // Impuestos de línea
            $lineTaxTotal = $this->documento->createElement('cac:TaxTotal');
            
            $igvLinea = $subtotalLinea - $baseLinea;
            $lineTaxAmount = $this->documento->createElement('cbc:TaxAmount');
            $lineTaxAmount->setAttribute('currencyID', $monedaCode);
            $lineTaxAmount->nodeValue = number_format($igvLinea, 2, '.', '');
            $lineTaxTotal->appendChild($lineTaxAmount);

            $lineTaxSubtotal = $this->documento->createElement('cac:TaxSubtotal');
            
            $lineTaxableAmount = $this->documento->createElement('cbc:TaxableAmount');
            $lineTaxableAmount->setAttribute('currencyID', $monedaCode);
            $lineTaxableAmount->nodeValue = number_format($baseLinea, 2, '.', '');
            $lineTaxSubtotal->appendChild($lineTaxableAmount);

            $lineSubTaxAmount = $this->documento->createElement('cbc:TaxAmount');
            $lineSubTaxAmount->setAttribute('currencyID', $monedaCode);
            $lineSubTaxAmount->nodeValue = number_format($igvLinea, 2, '.', '');
            $lineTaxSubtotal->appendChild($lineSubTaxAmount);

            $lineTaxCategory = $this->documento->createElement('cac:TaxCategory');
            $this->agregarElementoA($lineTaxCategory, 'cbc:ID', 'S');
            $this->agregarElementoA($lineTaxCategory, 'cbc:Percent', '18');

            $lineTaxScheme = $this->documento->createElement('cac:TaxScheme');
            $this->agregarElementoA($lineTaxScheme, 'cbc:ID', '1000');
            $this->agregarElementoA($lineTaxScheme, 'cbc:Name', 'IGV');
            $this->agregarElementoA($lineTaxScheme, 'cbc:TaxTypeCode', 'VAT');
            $lineTaxCategory->appendChild($lineTaxScheme);

            $lineTaxSubtotal->appendChild($lineTaxCategory);
            $lineTaxTotal->appendChild($lineTaxSubtotal);
            $line->appendChild($lineTaxTotal);

            // Descripción del producto
            $item_element = $this->documento->createElement('cac:Item');
            $this->agregarElementoA($item_element, 'cbc:Description', $item['descripcion']);
            
            $sellersId = $this->documento->createElement('cac:SellersItemIdentification');
            $this->agregarElementoA($sellersId, 'cbc:ID', $item['codigo']);
            $item_element->appendChild($sellersId);
            
            $line->appendChild($item_element);

            // Precio
            $price = $this->documento->createElement('cac:Price');
            $priceAmountFinal = $this->documento->createElement('cbc:PriceAmount');
            $priceAmountFinal->setAttribute('currencyID', $monedaCode);
            $priceAmountFinal->nodeValue = number_format($item['precio_unitario'], 2, '.', '');
            $price->appendChild($priceAmountFinal);
            $line->appendChild($price);

            $parent->appendChild($line);
        }
    }

    /**
     * Helper para agregar elemento con texto
     */
    private function agregarElemento($parent, $nombre, $valor)
    {
        $elemento = $this->documento->createElement($nombre);
        $elemento->nodeValue = $valor;
        $parent->appendChild($elemento);
        return $elemento;
    }

    /**
     * Helper para agregar elemento sin CDATA
     */
    private function agregarElementoA($parent, $nombre, $valor)
    {
        $elemento = $this->documento->createElement($nombre);
        $elemento->nodeValue = htmlspecialchars($valor);
        $parent->appendChild($elemento);
        return $elemento;
    }

    /**
     * Valida que el XML sea válido
     */
    public function validarXml($xmlString)
    {
        $documento = new DOMDocument();
        return @$documento->loadXML($xmlString);
    }

    /**
     * Obtiene el documento XML como string
     */
    public function getXmlString()
    {
        return $this->documento->saveXML();
    }
}
