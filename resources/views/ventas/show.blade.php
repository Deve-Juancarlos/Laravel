<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $factura->Tipo == 1 ? 'FACTURA' : 'BOLETA' }} ELECTRÓNICA - {{ $factura->Numero }}</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <style>
        /* Configuraciones personalizadas de Tailwind */
        @layer components {
            .document-box {
                @apply border-4 border-gray-900 text-center p-4 bg-gray-100 rounded-lg;
            }
            .client-info-table td:first-child {
                @apply font-semibold w-32;
            }
        }
        /* Estilos de Impresión */
        @media print {
            body { 
                @apply bg-white m-0; 
            }
            .invoice-container {
                @apply shadow-none m-0 max-w-full border-2 border-gray-900;
            }
            .no-print {
                display: none !important;
            }
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f5f5f5;
        }
        .table-items thead th {
             padding: 0.75rem 0.5rem;
             font-weight: 600;
        }
        /* Estilos para el modal XML */
        .xml-container {
            max-height: 70vh;
            overflow-y: auto;
            white-space: pre-wrap;
            word-break: break-all;
            font-family: 'Courier New', Courier, monospace;
            font-size: 0.8rem;
            background-color: #f7f7f7;
            padding: 1rem;
            border-radius: 0.5rem;
            border: 1px solid #ccc;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">

    <div class="no-print p-4 bg-white shadow-md flex justify-center space-x-3 fixed top-0 left-0 right-0 z-10 overflow-x-auto">
        <a href="{{ route('contador.facturas.index') }}" 
           class="flex items-center px-4 py-2 text-sm font-medium text-white bg-gray-600 rounded-lg hover:bg-gray-700 transition duration-150 shadow-lg whitespace-nowrap">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Volver
        </a>
        <button onclick="window.print()" 
                class="flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition duration-150 shadow-lg whitespace-nowrap">
            <i data-lucide="printer" class="w-4 h-4 mr-2"></i> Imprimir
        </button>
        <button onclick="toggleModal('emailModal')" 
                class="flex items-center px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition duration-150 shadow-lg whitespace-nowrap">
            <i data-lucide="send" class="w-4 h-4 mr-2"></i> Enviar por Email
        </button>
        
        <a href="{{ route('contador.facturas.xml.download', $factura->Numero) }}?tipo={{ $factura->Tipo }}" 
           class="flex items-center px-4 py-2 text-sm font-medium text-white bg-orange-600 rounded-lg hover:bg-orange-700 transition duration-150 shadow-lg whitespace-nowrap">
            <i data-lucide="download" class="w-4 h-4 mr-2"></i> Descargar XML
        </a>

        <a href="{{ route('contador.facturas.pdf.download', $factura->Numero) }}?tipo={{ $factura->Tipo }}" 
           class="flex items-center px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition duration-150 shadow-lg whitespace-nowrap">
            <i data-lucide="file-pdf" class="w-4 h-4 mr-2"></i> Descargar PDF
        </a>
    </div>

    <div class="invoice-container max-w-4xl mx-auto mt-24 mb-10 bg-white border-2 border-gray-900 shadow-xl rounded-lg overflow-hidden">
        
        <div class="p-6 border-b-2 border-gray-900">
            <div class="flex justify-between items-center">
                <div class="w-7/12">
                    <div class="text-3xl font-extrabold text-blue-800 mb-1">
                        {{ $empresa['nombre'] }}
                    </div>
                    <div class="text-sm text-gray-700 space-y-1">
                        <strong class="font-bold">{{ $empresa['giro'] }}</strong><br>
                        <i data-lucide="map-pin" class="w-3 h-3 inline mr-1"></i> {{ $empresa['direccion'] }}<br>
                        <i data-lucide="phone" class="w-3 h-3 inline mr-1"></i> {{ $empresa['telefono'] }} | 
                        <i data-lucide="mail" class="w-3 h-3 inline mr-1"></i> {{ $empresa['email'] }}
                    </div>
                </div>
                <div class="w-5/12 ml-6">
                    <div class="document-box">
                        <div class="text-sm font-bold mb-1">R.U.C. {{ $empresa['ruc'] }}</div>
                        <hr class="border-gray-900 my-2">
                        <h4 class="text-xl font-extrabold text-gray-800">
                            {{ $factura->Tipo == 1 ? 'FACTURA' : 'BOLETA' }} ELECTRÓNICA
                        </h4>
                        <hr class="border-gray-900 my-2">
                        <div class="text-2xl font-bold text-red-600 mt-1">
                            {{ $factura->Numero }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-6">
            <div class="bg-gray-50 p-4 rounded-lg mb-6 border border-gray-200 text-sm">
                <div class="flex">
                    <div class="w-2/3">
                        <table class="w-full client-info-table">
                            <tr>
                                <td><strong>Señor(es):</strong></td>
                                <td>{{ $factura->ClienteNombre }}</td>
                            </tr>
                            <tr>
                                <td><strong>Dirección:</strong></td>
                                <td>{{ $factura->ClienteDireccion }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="w-1/3">
                        <table class="w-full client-info-table">
                            <tr>
                                <td><strong>{{ strlen($factura->ClienteRuc) == 11 ? 'RUC:' : 'DNI:' }}</strong></td>
                                <td>{{ $factura->ClienteRuc }}</td>
                            </tr>
                            <tr>
                                <td><strong>Fecha Emisión:</strong></td>
                                <td>{{ \Carbon\Carbon::parse($factura->Fecha)->format('d/m/Y') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Vencimiento:</strong></td>
                                <td>{{ \Carbon\Carbon::parse($factura->FechaV)->format('d/m/Y') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Condición:</strong></td>
                                <td>{{ $condicionPago }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <table class="table-items w-full border-collapse border border-gray-300 text-sm">
                <thead>
                    <tr class="bg-gray-800 text-white">
                        <th class="text-center w-20">Cant.</th>
                        <th class="text-center w-20">Unidad</th>
                        <th class="w-24">Código</th>
                        <th class="text-left">Descripción</th>
                        <th class="text-right w-24">V. Unitario</th>
                        <th class="text-right w-24">Importe</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($detalles as $item)
                    <tr>
                        <td class="text-center p-2 border-b border-gray-200">{{ number_format($item->Cantidad, 2) }}</td>
                        <td class="text-center p-2 border-b border-gray-200">UNIDAD</td>
                        <td class="p-2 border-b border-gray-200">{{ $item->Codpro }}</td>
                        <td class="p-2 border-b border-gray-200">
                            <strong>{{ $item->ProductoNombre }}</strong>
                            <br><small class="text-gray-500">
                                <i data-lucide="barcode" class="w-3 h-3 inline mr-1"></i>Lote: {{ $item->Lote }} | 
                                <i data-lucide="calendar" class="w-3 h-3 inline mr-1"></i>Venc: {{ \Carbon\Carbon::parse($item->Vencimiento)->format('m/Y') }}
                            </small>
                        </td>
                        <td class="text-right p-2 border-b border-gray-200">{{ number_format($item->Precio, 2) }}</td>
                        <td class="text-right p-2 border-b border-gray-200 font-bold">{{ number_format($item->Subtotal, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mt-6">
                <div class="flex justify-between">
                    <div class="w-7/12 pr-4">
                        <div class="bg-blue-50 p-4 rounded-lg border-l-4 border-blue-500 mb-4 text-sm">
                            <strong class="flex items-center">
                                <span class="text-gray-800 font-bold text-lg mr-2">S/</span> SON:
                            </strong><br>
                            <span class="uppercase font-semibold">{{ $totalEnLetras }}</span>
                        </div>
                        
                        <div class="text-xs text-gray-500">
                            <strong>Información Adicional:</strong><br>
                            <i data-lucide="user" class="w-3 h-3 inline mr-1"></i> <strong>Vendedor:</strong> {{ $factura->VendedorNombre ?? 'N/A' }}<br>
                            <i data-lucide="wallet" class="w-3 h-3 inline mr-1"></i> <strong>Moneda:</strong> {{ $factura->MonedaNombre ?? 'SOLES' }}<br>
                            <i data-lucide="credit-card" class="w-3 h-3 inline mr-1"></i> <strong>Forma de Pago:</strong> {{ $condicionPago }}
                        </div>
                    </div>

                    <div class="w-5/12">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <table class="w-full text-sm">
                                @if($factura->Descuento > 0)
                                <tr class="text-red-600">
                                    <td class="py-2">Descuento Global:</td>
                                    <td class="text-right font-bold py-2">-{{ number_format($factura->Descuento, 2) }}</td>
                                </tr>
                                @endif
                                
                                <tr>
                                    <td class="py-2">Op. Gravada (Base Imponible):</td>
                                    <td class="text-right font-bold py-2">S/ {{ number_format($factura->Subtotal, 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="py-2">Op. Exonerada:</td>
                                    <td class="text-right font-bold py-2">S/ 0.00</td>
                                </tr>
                                <tr>
                                    <td class="py-2">Op. Inafecta:</td>
                                    <td class="text-right font-bold py-2">S/ 0.00</td>
                                </tr>
                                <tr class="border-t border-gray-300">
                                    <td class="py-2">IGV (18%):</td>
                                    <td class="text-right font-bold py-2">S/ {{ number_format($factura->Igv, 2) }}</td>
                                </tr>
                                <tr class="border-t-2 border-gray-900 text-lg font-bold bg-blue-100">
                                    <td class="py-2">IMPORTE TOTAL:</td>
                                    <td class="text-right py-2">S/ {{ number_format($factura->Total, 2) }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="p-4 text-center bg-gray-50 border-t-2 border-gray-900">
            <div class="flex justify-between items-center">
                <div class="w-3/4">         
                    <p class="text-gray-500 text-xs mb-0">
                        Representación Impresa de la {{ $factura->Tipo == 1 ? 'FACTURA' : 'BOLETA DE VENTA' }} Electrónica (SUNAT)<br>
                        Para consultar el comprobante ingrese a: <strong>{{ $empresa['web'] }}</strong>
                    </p>
                </div>
                @if(isset($codigoQr) && !empty($codigoQr))
                <div class="w-1/4 text-center">
                    <img src="data:image/png;base64,{{ $codigoQr }}" 
                         style="width: 120px; height: 120px; border: 1px solid #999;" 
                         alt="Código QR SUNAT">
                    <p class="text-gray-500 text-xs mt-1">Código QR SUNAT</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div id="emailModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50" aria-modal="true" role="dialog">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md m-4">
            <div class="p-5 border-b border-gray-200 flex justify-between items-center">
                <h5 class="text-lg font-bold text-gray-900">Enviar Documento por Email</h5>
                <button type="button" class="text-gray-400 hover:text-gray-600" onclick="toggleModal('emailModal')">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            
            <form action="{{ route('contador.facturas.enviarEmail', ['numero' => $factura->Numero, 'tipo' => $factura->Tipo]) }}" method="POST">
                @csrf
                <div class="p-5">
                    <p class="mb-4 text-gray-700 text-sm">Se enviará el documento <strong>{{ $factura->Numero }}</strong> al siguiente correo:</p>
                    <div class="mb-4">
                        <label for="email_destino" class="block text-sm font-bold text-gray-700 mb-1">Email del Cliente</label>
                        <input type="email" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500" 
                               id="email_destino" name="email_destino" 
                               value="{{ $factura->ClienteEmail ?? '' }}" 
                               placeholder="cliente@email.com" required>
                    </div>
                </div>
                <div class="p-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-2">
                    <button type="button" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300" onclick="toggleModal('emailModal')">Cancelar</button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700 flex items-center">
                        <i data-lucide="paper-plane" class="w-4 h-4 mr-2"></i> Enviar
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <div id="xmlModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50" aria-modal="true" role="dialog">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl m-4">
            <div class="p-5 border-b border-gray-200 flex justify-between items-center">
                <h5 class="text-lg font-bold text-gray-900">Simulación de XML (UBL 2.1)</h5>
                <button type="button" class="text-gray-400 hover:text-gray-600" onclick="toggleModal('xmlModal')">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <div class="p-5">
                <p class="mb-3 text-gray-700 text-sm">Este es un XML simplificado que simula la estructura de tu Comprobante de Pago Electrónico de SUNAT.</p>
                <div id="xmlContent" class="xml-container">
                    </div>
            </div>
            <div class="p-4 bg-gray-50 border-t border-gray-200 flex justify-end">
                <button type="button" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300" onclick="toggleModal('xmlModal')">Cerrar</button>
            </div>
        </div>
    </div>
    
    <div id="messageModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50" aria-modal="true" role="dialog">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-sm m-4">
            <div class="p-5">
                <h5 id="messageTitle" class="text-lg font-bold text-gray-900 mb-2"></h5>
                <p id="messageBody" class="text-sm text-gray-700"></p>
            </div>
            <div class="p-4 bg-gray-50 border-t border-gray-200 flex justify-end">
                <button type="button" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700" onclick="toggleModal('messageModal')">Aceptar</button>
            </div>
        </div>
    </div>


    <script>
        
        lucide.createIcons();

        
        const invoiceData = {
            
            company: @json($empresa),
            invoice: {
                type: {{ $factura->Tipo }}, 
                code: "{{ $factura->Tipo == 1 ? '01' : '03' }}",
                number: "{{ $factura->Numero }}",
                currency: "{{ $factura->MonedaNombre == 'DOLARES' ? 'USD' : 'PEN' }}",
                date: "{{ \Carbon\Carbon::parse($factura->Fecha)->format('Y-m-d') }}",
                dueDate: "{{ \Carbon\Carbon::parse($factura->FechaV)->format('Y-m-d') }}",
                paymentCondition: "{{ $condicionPago }}"
            },
            
            
            client: {
                name: "{{ $factura->ClienteNombre }}",
                ruc: "{{ $factura->ClienteRuc }}",
                docType: "{{ strlen($factura->ClienteRuc) == 11 ? '6' : '1' }}", 
                address: "{{ $factura->ClienteDireccion }}",
                email: "{{ $factura->ClienteEmail ?? '' }}"
            },
            
           
            details: @json($detallesParaJs),
              
            totals: {
                DescuentoGlobal: {{ $factura->Descuento ?? 0 }},
                BaseImponible: {{ $factura->Subtotal ?? 0 }},
                Igv: {{ $factura->Igv ?? 0 }},
                Total: {{ $factura->Total ?? 0 }},
                totalEnLetras: "{{ $totalEnLetras }}"
            }
        };


        function toggleModal(id) {
            const modal = document.getElementById(id);
            if (modal.classList.contains('hidden')) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            } else {
                modal.classList.remove('flex');
                modal.classList.add('hidden');
            }
        }

        function showMessage(title, body) {
            document.getElementById('messageTitle').innerText = title;
            document.getElementById('messageBody').innerText = body;
            toggleModal('messageModal');
        }

        function showXMLModal() {
            try {
                // Obtener XML desde el servidor backend (NO generar en JavaScript)
                const numero = '{{ $factura->Numero }}';
                const tipo = {{ $factura->Tipo }};
                const url = "{{ route('contador.facturas.xml.view', $factura->Numero) }}" + '?tipo=' + tipo;

                
                fetch(url)
                    .then(response => response.text())
                    .then(xmlText => {
                        const escapedXml = xmlText.replace(/</g, '&lt;').replace(/>/g, '&gt;');
                        document.getElementById('xmlContent').innerHTML = escapedXml;
                        toggleModal('xmlModal');
                    })
                    .catch(error => {
                        console.error("Error al obtener XML:", error);
                        showMessage("Error", "No se pudo obtener el XML: " + error.message);
                    });
            } catch (error) {
                console.error("Error:", error);
                showMessage("Error", error.message);
            }
        }

    </script>
</body>
</html>