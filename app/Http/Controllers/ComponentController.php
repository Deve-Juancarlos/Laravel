<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ComponentController extends Controller
{
    /**
     * COMPONENTES DE INTERFAZ - Gestión de componentes UI para SIFANO
     * Sistema de farmacia/contabilidad
     */

    public function __construct()
    {
        $this->middleware(['auth', 'check.admin']);
    }

    /**
     * Dashboard de componentes
     */
    public function index()
    {
        try {
            $componentes = $this->obtenerListaComponentes();
            $estadisticas = $this->obtenerEstadisticasComponentes();

            return [
                'componentes' => $componentes,
                'estadisticas' => $estadisticas
            ];

        } catch (\Exception $e) {
            \Log::error('Error en ComponentController::index: ' . $e->getMessage());
            return response()->json(['error' => 'Error al cargar componentes'], 500);
        }
    }

    /**
     * Gestión de componentes de formulario
     */
    public function formularios()
    {
        $componentes = [
            'inputs' => [
                [
                    'tipo' => 'text',
                    'nombre' => 'input_texto',
                    'descripcion' => 'Input de texto básico',
                    'ejemplo' => '<input type="text" class="form-control">'
                ],
                [
                    'tipo' => 'number',
                    'nombre' => 'input_numero',
                    'descripcion' => 'Input numérico',
                    'ejemplo' => '<input type="number" class="form-control">'
                ],
                [
                    'tipo' => 'select',
                    'nombre' => 'select_dropdown',
                    'descripcion' => 'Dropdown de selección',
                    'ejemplo' => '<select class="form-select"><option>Seleccionar...</option></select>'
                ]
            ],
            'botones' => [
                [
                    'tipo' => 'primary',
                    'nombre' => 'btn_primary',
                    'descripcion' => 'Botón principal',
                    'ejemplo' => '<button class="btn btn-primary">Guardar</button>'
                ],
                [
                    'tipo' => 'secondary',
                    'nombre' => 'btn_secondary',
                    'descripcion' => 'Botón secundario',
                    'ejemplo' => '<button class="btn btn-secondary">Cancelar</button>'
                ],
                [
                    'tipo' => 'danger',
                    'nombre' => 'btn_danger',
                    'descripcion' => 'Botón de acción destructiva',
                    'ejemplo' => '<button class="btn btn-danger">Eliminar</button>'
                ]
            ],
            'tablas' => [
                [
                    'tipo' => 'basica',
                    'nombre' => 'tabla_basica',
                    'descripcion' => 'Tabla con paginación',
                    'ejemplo' => '<table class="table table-striped">'
                ],
                [
                    'tipo' => 'responsive',
                    'nombre' => 'tabla_responsive',
                    'descripcion' => 'Tabla responsive',
                    'ejemplo' => '<div class="table-responsive"><table class="table">'
                ]
            ]
        ];

        return $componentes;
    }

    /**
     * Gestión de componentes de navegación
     */
    public function navegacion()
    {
        $componentes = [
            'menus' => [
                [
                    'tipo' => 'navbar',
                    'nombre' => 'menu_principal',
                    'descripcion' => 'Barra de navegación principal',
                    'ejemplo' => '<nav class="navbar navbar-expand-lg">'
                ],
                [
                    'tipo' => 'sidebar',
                    'nombre' => 'menu_lateral',
                    'descripcion' => 'Menú lateral colapsible',
                    'ejemplo' => '<div class="sidebar">'
                ]
            ],
            'breadcrumbs' => [
                [
                    'tipo' => 'crumbs',
                    'nombre' => 'breadcrumb',
                    'descripcion' => 'Migas de pan',
                    'ejemplo' => '<nav class="breadcrumb">'
                ]
            ],
            'paginacion' => [
                [
                    'tipo' => 'paginate',
                    'nombre' => 'paginador',
                    'descripcion' => 'Navegación de páginas',
                    'ejemplo' => '<nav class="pagination">'
                ]
            ]
        ];

        return $componentes;
    }

    /**
     * Gestión de componentes de datos
     */
    public function datos()
    {
        $componentes = [
            'charts' => [
                [
                    'tipo' => 'line',
                    'nombre' => 'grafico_linea',
                    'descripcion' => 'Gráfico de líneas',
                    'libreria' => 'Chart.js'
                ],
                [
                    'tipo' => 'bar',
                    'nombre' => 'grafico_barras',
                    'descripcion' => 'Gráfico de barras',
                    'libreria' => 'Chart.js'
                ],
                [
                    'tipo' => 'pie',
                    'nombre' => 'grafico_pie',
                    'descripcion' => 'Gráfico circular',
                    'libreria' => 'Chart.js'
                ]
            ],
            'tables' => [
                [
                    'tipo' => 'datatable',
                    'nombre' => 'tabla_avanzada',
                    'descripcion' => 'Tabla con filtros y ordenamiento',
                    'libreria' => 'DataTables'
                ],
                [
                    'tipo' => 'excel',
                    'nombre' => 'export_excel',
                    'descripcion' => 'Exportación a Excel',
                    'libreria' => 'SheetJS'
                ]
            ]
        ];

        return $componentes;
    }

    /**
     * Gestión de componentes de farmacia específicos
     */
    public function farmacia()
    {
        $componentes = [
            'productos' => [
                [
                    'tipo' => 'search_producto',
                    'nombre' => 'buscador_productos',
                    'descripcion' => 'Buscador de productos con código de barras',
                    'campos' => ['CodPro', 'Nombre', 'Stock', 'Precio']
                ],
                [
                    'tipo' => 'control_stock',
                    'nombre' => 'control_stock',
                    'descripcion' => 'Control de stock en tiempo real',
                    'campos' => ['Stock', 'Stock_Minimo', 'Stock_Maximo']
                ],
                [
                    'tipo' => 'vencimiento',
                    'nombre' => 'control_vencimiento',
                    'descripcion' => 'Control de fechas de vencimiento',
                    'campos' => ['Lote', 'Vencimiento', 'Cantidad']
                ]
            ],
            'clientes' => [
                [
                    'tipo' => 'search_cliente',
                    'nombre' => 'buscador_clientes',
                    'descripcion' => 'Buscador de clientes por RUC o Razón Social',
                    'campos' => ['Codclie', 'Razon', 'Ruc', 'Limite']
                ],
                [
                    'tipo' => 'credito_cliente',
                    'nombre' => 'credito_disponible',
                    'descripcion' => 'Visualización de crédito disponible',
                    'campos' => ['Limite', 'Saldo', 'Dias_Credito']
                ]
            ],
            'facturacion' => [
                [
                    'tipo' => 'factura_boleta',
                    'nombre' => 'selector_factura_boleta',
                    'descripcion' => 'Selector entre factura y boleta',
                    'campos' => ['Tipo_Documento', 'Serie', 'Numero']
                ],
                [
                    'tipo' => 'detalle_venta',
                    'nombre' => 'detalle_productos',
                    'descripcion' => 'Tabla de productos en venta',
                    'campos' => ['Producto', 'Cantidad', 'Precio', 'Total']
                ]
            ]
        ];

        return $componentes;
    }

    /**
     * Gestión de componentes de contabilidad específicos
     */
    public function contabilidad()
    {
        $componentes = [
            'asientos' => [
                [
                    'tipo' => 'libro_diario',
                    'nombre' => 'asientos_contables',
                    'descripcion' => 'Formulario de asientos contables',
                    'campos' => ['Cuenta', 'Debe', 'Haber', 'Concepto']
                ],
                [
                    'tipo' => 'balance',
                    'nombre' => 'balance_comprobacion',
                    'descripcion' => 'Tabla de balance de comprobación',
                    'campos' => ['Cuenta', 'Saldo_Deudor', 'Saldo_Acreedor']
                ]
            ],
            'reportes' => [
                [
                    'tipo' => 'sunat_reportes',
                    'nombre' => 'reportes_sunat',
                    'descripcion' => 'Generación de reportes SUNAT',
                    'campos' => ['Periodo', 'Libro', 'Archivo']
                ],
                [
                    'tipo' => 'estado_resultados',
                    'nombre' => 'estado_resultados',
                    'descripcion' => 'Estado de resultados',
                    'campos' => ['Ingresos', 'Gastos', 'Utilidad']
                ]
            ],
            'cuentas' => [
                [
                    'tipo' => 'plan_cuentas',
                    'nombre' => 'plan_contable',
                    'descripcion' => 'Visualización del plan de cuentas',
                    'campos' => ['Codigo', 'Nombre', 'Tipo', 'Nivel']
                ]
            ]
        ];

        return $componentes;
    }

    /**
     * Personalización de tema
     */
    public function tema()
    {
        $configuracion_actual = [
            'colores' => [
                'primario' => '#007bff',
                'secundario' => '#6c757d',
                'exito' => '#28a745',
                'peligro' => '#dc3545',
                'advertencia' => '#ffc107',
                'informacion' => '#17a2b8'
            ],
            'fuentes' => [
                'primaria' => 'Arial, sans-serif',
                'secundaria' => 'Georgia, serif',
                'codigo' => 'Monaco, Consolas, monospace'
            ],
            'layout' => [
                'sidebar_width' => '250px',
                'navbar_height' => '60px',
                'container_width' => '1200px'
            ]
        ];

        return $configuracion_actual;
    }

    /**
     * Actualizar configuración de tema
     */
    public function actualizarTema(Request $request)
    {
        $request->validate([
            'colores.*' => 'nullable|regex:/^#[a-fA-F0-9]{6}$/',
            'fuentes.*' => 'nullable|string',
            'layout.*' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            // Aquí se guardaría la configuración del tema en BD
            \Log::info('Tema actualizado por: ' . (auth()->user() ? auth()->user()->usuario : 'N/A'));
            \Log::info('Nueva configuración: ' . json_encode($request->all()));

            DB::commit();

            return response()->json([
                'success' => true,
                'mensaje' => 'Tema actualizado exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Error al actualizar tema'], 500);
        }
    }

    /**
     * Vista previa de componentes
     */
    public function preview($tipo)
    {
        $previews = [
            'input_basico' => [
                'codigo' => '<input type="text" class="form-control" placeholder="Ingrese texto">',
                'renderizado' => '<input type="text" class="form-control" placeholder="Ingrese texto">'
            ],
            'tabla_datos' => [
                'codigo' => '<table class="table table-striped"><thead>...</tbody></table>',
                'renderizado' => $this->generarTablaEjemplo()
            ],
            'grafico_ventas' => [
                'codigo' => '<canvas id="ventasChart"></canvas>',
                'renderizado' => '<canvas id="ventasChart" width="400" height="200">Gráfico de ventas</canvas>'
            ]
        ];

        if (!isset($previews[$tipo])) {
            return response()->json(['error' => 'Tipo de preview no encontrado'], 404);
        }

        return $previews[$tipo];
    }

    /**
     * Exportar componentes personalizados
     */
    public function exportar($tipo)
    {
        $archivos = [
            'css_personalizado' => 'css/componentes-personalizados.css',
            'js_utilidades' => 'js/componentes-utilidades.js',
            'templates' => 'templates/componentes/'
        ];

        if (!isset($archivos[$tipo])) {
            return response()->json(['error' => 'Tipo de exportación no válido'], 400);
        }

        try {
            // Generar contenido del archivo
            $contenido = $this->generarContenidoExportacion($tipo);
            
            // Crear archivo temporal
            $filename = "componentes_{$tipo}_" . now()->format('Y-m-d_H-i-s') . ".css";
            
            return response($contenido, 200, [
                'Content-Type' => 'text/css',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"'
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al exportar componentes'], 500);
        }
    }

    /**
     * Importar componentes
     */
    public function importar(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:css,js,json',
            'tipo' => 'required|in:css,js,config'
        ]);

        try {
            $contenido = file_get_contents($request->archivo->path());
            
            // Validar contenido según el tipo
            if ($request->tipo === 'css' && !$this->validarCSS($contenido)) {
                return response()->json(['error' => 'CSS inválido'], 400);
            }

            // Guardar componente importado
            $filename = "importado_" . now()->format('Y-m-d_H-i-s') . ".{$request->tipo}";
            Storage::put("componentes/{$filename}", $contenido);

            return response()->json([
                'success' => true,
                'mensaje' => 'Componentes importados exitosamente',
                'archivo' => $filename
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al importar componentes'], 500);
        }
    }

    /**
     * ===============================================
     * MÉTODOS PRIVADOS DE APOYO
     * ===============================================
     */

    /**
     * Obtiene lista de componentes disponibles
     */
    private function obtenerListaComponentes()
    {
        return [
            'basicos' => 15,
            'formularios' => 8,
            'navegacion' => 5,
            'datos' => 6,
            'farmacia' => 12,
            'contabilidad' => 10
        ];
    }

    /**
     * Obtiene estadísticas de componentes
     */
    private function obtenerEstadisticasComponentes()
    {
        return [
            'total_componentes' => 56,
            'componentes_activos' => 45,
            'componentes_personalizados' => 8,
            'uso_promedio' => 78.5
        ];
    }

    /**
     * Genera tabla de ejemplo para preview
     */
    private function generarTablaEjemplo()
    {
        return '<table class="table table-striped">
                    <thead><tr><th>ID</th><th>Nombre</th><th>Estado</th></tr></thead>
                    <tbody>
                        <tr><td>1</td><td>Producto A</td><td><span class="badge bg-success">Activo</span></td></tr>
                        <tr><td>2</td><td>Producto B</td><td><span class="badge bg-warning">Pendiente</span></td></tr>
                    </tbody>
                </table>';
    }

    /**
     * Genera contenido de exportación
     */
    private function generarContenidoExportacion($tipo)
    {
        $plantillas = [
            'css_personalizado' => '
/* Componentes Personalizados SIFANO */
.componente-farmacia {
    background: linear-gradient(45deg, #007bff, #0056b3);
    border-radius: 8px;
    padding: 1rem;
}

.tabla-con-table-striped tbody tr:nth-of-type(odd) {
    background-color: rgba(0, 123, 255, 0.1);
}
            ',
            'js_utilidades' => '
// Utilidades JavaScript para SIFANO
function buscarProducto(codigo) {
    // Implementación del buscador de productos
    console.log("Buscando producto:", codigo);
}

function calcularTotales(fila) {
    // Implementación del cálculo de totales
    console.log("Calculando totales para fila:", fila);
}
            ',
            'config' => '{
    "tema": {
        "color_primario": "#007bff",
        "color_secundario": "#6c757d"
    },
    "componentes": {
        "tabla_paginada": true,
        "grafico_autoupdate": true
    }
}'
        ];

        return $plantillas[$tipo] ?? '';
    }

    /**
     * Valida contenido CSS
     */
    private function validarCSS($contenido)
    {
        // Validaciones básicas de CSS
        $patrones_validos = [
            '/\{[\s\S]*\}/', // Reglas CSS con llaves
            '/[\w-]+\s*:/'  // Propiedades CSS
        ];

        foreach ($patrones_validos as $patron) {
            if (!preg_match($patron, $contenido)) {
                return false;
            }
        }

        return true;
    }
}