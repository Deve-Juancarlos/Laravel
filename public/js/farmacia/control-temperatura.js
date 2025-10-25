// Control de Temperatura - Farmacia SIFANO
// Sistema de monitoreo y control de temperatura para medicamentos

class ControlTemperatura {
    constructor() {
        this.sensores = [];
        this.alertasActivas = [];
        this.historico = [];
        this.init();
    }

    init() {
        this.cargarSensores();
        this.iniciarMonitoreo();
        this.configurarAlertas();
        this.actualizarDashboard();
        this.configurarChart();
    }

    cargarSensores() {
        this.sensores = [
            {
                id: 1,
                nombre: 'Refrigerador Principal',
                ubicacion: 'Farmacia Principal',
                temperatura: 4.2,
                minTemp: 2,
                maxTemp: 8,
                status: 'normal',
                humedad: 65,
                activo: true
            },
            {
                id: 2,
                nombre: 'Congelador Vacunas',
                ubicacion: 'Almacén Principal',
                temperatura: -18.5,
                minTemp: -25,
                maxTemp: -15,
                status: 'normal',
                humedad: 45,
                activo: true
            },
            {
                id: 3,
                nombre: 'Mostrador Frío',
                ubicacion: 'Área de Ventas',
                temperatura: 12.3,
                minTemp: 8,
                maxTemp: 15,
                status: 'warning',
                humedad: 70,
                activo: true
            }
        ];
    }

    iniciarMonitoreo() {
        // Simular lectura de sensores cada 30 segundos
        setInterval(() => {
            this.actualizarSensores();
        }, 30000);
        
        // Actualizar visualización cada 5 segundos
        setInterval(() => {
            this.actualizarVisualizacion();
        }, 5000);
    }

    actualizarSensores() {
        this.sensores.forEach(sensor => {
            if (sensor.activo) {
                // Simular fluctuación de temperatura
                const variacion = (Math.random() - 0.5) * 0.4;
                const nuevaTemp = sensor.temperatura + variacion;
                
                // Validar límites y simular alertas
                if (nuevaTemp < sensor.minTemp || nuevaTemp > sensor.maxTemp) {
                    this.crearAlerta(sensor, nuevaTemp);
                }
                
                sensor.temperatura = parseFloat(nuevaTemp.toFixed(1));
                this.actualizarStatus(sensor);
                
                // Guardar en histórico
                this.agregarAlHistorico(sensor);
            }
        });
        
        this.actualizarDashboard();
    }

    crearAlerta(sensor, temperatura) {
        const alerta = {
            id: Date.now(),
            sensorId: sensor.id,
            sensorNombre: sensor.nombre,
            temperatura: temperatura,
            timestamp: new Date(),
            tipo: temperatura < sensor.minTemp ? 'critical' : 'warning',
            mensaje: temperatura < sensor.minTemp 
                ? `Temperatura CRÍTICA: ${temperatura}°C (mínimo: ${sensor.minTemp}°C)`
                : `Temperatura FUERA DE RANGO: ${temperatura}°C (máximo: ${sensor.maxTemp}°C)`,
            vista: false
        };
        
        this.alertasActivas.push(alerta);
        this.mostrarNotificacion(alerta);
        this.reproducirSonidoAlerta(alerta.tipo);
    }

    actualizarStatus(sensor) {
        if (sensor.temperatura >= sensor.minTemp && sensor.temperatura <= sensor.maxTemp) {
            sensor.status = 'normal';
        } else if (sensor.temperatura < sensor.minTemp * 0.9 || sensor.temperatura > sensor.maxTemp * 1.1) {
            sensor.status = 'critical';
        } else {
            sensor.status = 'warning';
        }
    }

    agregarAlHistorico(sensor) {
        const registro = {
            sensorId: sensor.id,
            temperatura: sensor.temperatura,
            humedad: sensor.humedad,
            timestamp: new Date()
        };
        
        this.historico.push(registro);
        
        // Mantener solo las últimas 24 horas
        const hace24Horas = new Date(Date.now() - 24 * 60 * 60 * 1000);
        this.historico = this.historico.filter(r => r.timestamp > hace24Horas);
    }

    actualizarDashboard() {
        // Actualizar tarjetas de sensores
        this.sensores.forEach(sensor => {
            this.actualizarTarjetaSensor(sensor);
        });
        
        // Actualizar estadísticas
        this.actualizarEstadisticas();
        
        // Actualizar alertas activas
        this.actualizarAlertasActivas();
    }

    actualizarTarjetaSensor(sensor) {
        const tarjeta = document.getElementById(`sensor-${sensor.id}`);
        if (!tarjeta) return;
        
        const temperaturaElement = tarjeta.querySelector('.temperatura-valor');
        const statusElement = tarjeta.querySelector('.status-indicador');
        const humedadElement = tarjeta.querySelector('.humedad-valor');
        
        if (temperaturaElement) {
            temperaturaElement.textContent = `${sensor.temperatura}°C`;
            temperaturaElement.className = `temperatura-valor ${sensor.status}`;
        }
        
        if (statusElement) {
            statusElement.className = `status-indicador ${sensor.status}`;
            statusElement.innerHTML = this.getStatusIcon(sensor.status);
        }
        
        if (humedadElement) {
            humedadElement.textContent = `${sensor.humedad}%`;
        }
    }

    getStatusIcon(status) {
        const icons = {
            normal: '<i class="fas fa-check-circle text-success"></i>',
            warning: '<i class="fas fa-exclamation-triangle text-warning"></i>',
            critical: '<i class="fas fa-times-circle text-danger"></i>'
        };
        return icons[status] || icons.normal;
    }

    actualizarEstadisticas() {
        const sensoresActivos = this.sensores.filter(s => s.activo);
        const sensoresNormales = sensoresActivos.filter(s => s.status === 'normal');
        const alertasActivas = this.alertasActivas.filter(a => !a.vista).length;
        
        // Actualizar elementos del DOM
        const totalSensores = document.getElementById('total-sensores');
        const sensoresNormalesEl = document.getElementById('sensores-normales');
        const alertasEl = document.getElementById('alertas-activas');
        
        if (totalSensores) totalSensores.textContent = sensoresActivos.length;
        if (sensoresNormalesEl) sensoresNormalesEl.textContent = sensoresNormales.length;
        if (alertasEl) alertasEl.textContent = alertasActivas;
    }

    configurarAlertas() {
        // Configurar阈值 de alertas automáticas
        this.umbrales = {
            warning: {
                temperaturaMin: 0.9, // 90% del mínimo
                temperaturaMax: 1.1  // 110% del máximo
            },
            critical: {
                temperaturaMin: 0.8, // 80% del mínimo
                temperaturaMax: 1.2  // 120% del máximo
            }
        };
    }

    configurarChart() {
        const ctx = document.getElementById('temperaturaChart');
        if (!ctx) return;
        
        this.chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [],
                datasets: this.sensores.map(sensor => ({
                    label: sensor.nombre,
                    data: [],
                    borderColor: this.getColorForSensor(sensor.id),
                    backgroundColor: this.getColorForSensor(sensor.id) + '20',
                    fill: true,
                    tension: 0.4
                }))
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: false,
                        title: {
                            display: true,
                            text: 'Temperatura (°C)'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Tiempo'
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top'
                    },
                    title: {
                        display: true,
                        text: 'Histórico de Temperaturas'
                    }
                },
                animation: {
                    duration: 750,
                    easing: 'easeInOutQuart'
                }
            }
        });
    }

    getColorForSensor(sensorId) {
        const colors = [
            '#FF6384', '#36A2EB', '#FFCE56', 
            '#4BC0C0', '#9966FF', '#FF9F40'
        ];
        return colors[sensorId % colors.length];
    }

    actualizarChart() {
        if (!this.chart) return;
        
        const ultimosRegistros = this.obtenerUltimosRegistros(30); // Últimos 30 puntos
        
        this.chart.data.labels = ultimosRegistros.map(r => 
            r.timestamp.toLocaleTimeString()
        );
        
        this.sensores.forEach((sensor, index) => {
            const datosSensor = ultimosRegistros
                .filter(r => r.sensorId === sensor.id)
                .map(r => r.temperatura);
            
            this.chart.data.datasets[index].data = datosSensor;
        });
        
        this.chart.update('none');
    }

    obtenerUltimosRegistros(cantidad) {
        return this.historico
            .slice(-cantidad)
            .sort((a, b) => a.timestamp - b.timestamp);
    }

    actualizarVisualizacion() {
        this.actualizarChart();
    }

    marcarAlertaVista(alertaId) {
        const alerta = this.alertasActivas.find(a => a.id === alertaId);
        if (alerta) {
            alerta.vista = true;
            this.actualizarAlertasActivas();
        }
    }

    resolverAlerta(alertaId) {
        this.alertasActivas = this.alertasActivas.filter(a => a.id !== alertaId);
        this.actualizarAlertasActivas();
    }

    actualizarAlertasActivas() {
        const container = document.getElementById('alertas-activas-container');
        if (!container) return;
        
        const alertasNoVistas = this.alertasActivas.filter(a => !a.vista);
        
        if (alertasNoVistas.length === 0) {
            container.innerHTML = `
                <div class="alert alert-success d-flex align-items-center">
                    <i class="fas fa-check-circle me-2"></i>
                    <span>Todas las temperaturas están dentro del rango normal</span>
                </div>
            `;
            return;
        }
        
        container.innerHTML = alertasNoVistas.map(alerta => `
            <div class="alert alert-${alerta.tipo === 'critical' ? 'danger' : 'warning'} alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-${alerta.tipo === 'critical' ? 'exclamation-triangle' : 'info-circle'} me-2"></i>
                    <div>
                        <strong>${alerta.sensorNombre}</strong><br>
                        <small>${alerta.mensaje}</small><br>
                        <small class="text-muted">${alerta.timestamp.toLocaleString()}</small>
                    </div>
                </div>
                <button type="button" class="btn-close" onclick="controlTemperatura.marcarAlertaVista(${alerta.id})"></button>
                <button type="button" class="btn btn-sm btn-outline-secondary ms-2" onclick="controlTemperatura.resolverAlerta(${alerta.id})">
                    <i class="fas fa-check"></i>
                </button>
            </div>
        `).join('');
    }

    mostrarNotificacion(alerta) {
        // Usar SweetAlert2 para notificaciones importantes
        if (alerta.tipo === 'critical') {
            Swal.fire({
                title: '¡ALERTA CRÍTICA!',
                text: `${alerta.sensorNombre}: ${alerta.mensaje}`,
                icon: 'error',
                timer: 10000,
                timerProgressBar: true,
                showConfirmButton: true,
                confirmButtonText: 'Revisar',
                toast: true,
                position: 'top-end'
            });
        }
    }

    reproducirSonidoAlerta(tipo) {
        // Crear audio context para reproducir sonidos de alerta
        if (typeof AudioContext !== 'undefined' || typeof webkitAudioContext !== 'undefined') {
            const audioContext = new (AudioContext || webkitAudioContext)();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();
            
            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);
            
            oscillator.frequency.setValueAtTime(tipo === 'critical' ? 800 : 600, audioContext.currentTime);
            gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 1);
            
            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 1);
        }
    }

    // Método para agregar nuevo sensor
    agregarSensor(configuracion) {
        const nuevoSensor = {
            id: this.sensores.length + 1,
            ...configuracion,
            activo: true,
            temperatura: configuracion.temperaturaInicial || 0,
            status: 'normal'
        };
        
        this.sensores.push(nuevoSensor);
        
        // Agregar dataset al chart
        if (this.chart) {
            this.chart.data.datasets.push({
                label: nuevoSensor.nombre,
                data: [],
                borderColor: this.getColorForSensor(nuevoSensor.id),
                backgroundColor: this.getColorForSensor(nuevoSensor.id) + '20',
                fill: true,
                tension: 0.4
            });
            this.chart.update();
        }
        
        this.actualizarDashboard();
    }

    // Método para generar reporte
    generarReporte() {
        const reporte = {
            fecha: new Date(),
            sensores: this.sensores.map(sensor => ({
                nombre: sensor.nombre,
                ubicacion: sensor.ubicacion,
                temperaturaPromedio: this.calcularPromedioTemperatura(sensor.id),
                alertas: this.alertasActivas.filter(a => a.sensorId === sensor.id).length,
                cumplimiento: this.calcularCumplimiento(sensor.id)
            })),
            estadisticasGenerales: {
                totalSensores: this.sensores.length,
                sensoresActivos: this.sensores.filter(s => s.activo).length,
                alertasTotales: this.alertasActivas.length,
                tiempoFuncionamiento: '24h 0m' // Simulado
            }
        };
        
        return reporte;
    }

    calcularPromedioTemperatura(sensorId) {
        const registrosSensor = this.historico.filter(r => r.sensorId === sensorId);
        if (registrosSensor.length === 0) return 0;
        
        const suma = registrosSensor.reduce((acc, r) => acc + r.temperatura, 0);
        return parseFloat((suma / registrosSensor.length).toFixed(2));
    }

    calcularCumplimiento(sensorId) {
        const registrosSensor = this.historico.filter(r => r.sensorId === sensorId);
        if (registrosSensor.length === 0) return 100;
        
        const sensor = this.sensores.find(s => s.id === sensorId);
        const registrosEnRango = registrosSensor.filter(r => 
            r.temperatura >= sensor.minTemp && r.temperatura <= sensor.maxTemp
        );
        
        return parseFloat(((registrosEnRango.length / registrosSensor.length) * 100).toFixed(1));
    }
}

// Inicializar el sistema de control de temperatura
let controlTemperatura;

document.addEventListener('DOMContentLoaded', function() {
    controlTemperatura = new ControlTemperatura();
    
    // Configurar botones
    document.getElementById('btn-agregar-sensor')?.addEventListener('click', function() {
        // Mostrar modal para agregar sensor
        Swal.fire({
            title: 'Agregar Nuevo Sensor',
            html: `
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">Nombre del Sensor</label>
                        <input type="text" id="nombre-sensor" class="form-control" placeholder="Ej: Refrigerador Secundario">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Ubicación</label>
                        <input type="text" id="ubicacion-sensor" class="form-control" placeholder="Ej: Almacén B">
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-4">
                        <label class="form-label">Temp. Mínima (°C)</label>
                        <input type="number" id="temp-min" class="form-control" step="0.1" value="2">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Temp. Máxima (°C)</label>
                        <input type="number" id="temp-max" class="form-control" step="0.1" value="8">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Temp. Inicial (°C)</label>
                        <input type="number" id="temp-inicial" class="form-control" step="0.1" value="5">
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Agregar Sensor',
            cancelButtonText: 'Cancelar',
            preConfirm: () => {
                const nombre = document.getElementById('nombre-sensor').value;
                const ubicacion = document.getElementById('ubicacion-sensor').value;
                const minTemp = parseFloat(document.getElementById('temp-min').value);
                const maxTemp = parseFloat(document.getElementById('temp-max').value);
                const tempInicial = parseFloat(document.getElementById('temp-inicial').value);
                
                if (!nombre || !ubicacion) {
                    Swal.showValidationMessage('Por favor complete todos los campos');
                    return false;
                }
                
                return { nombre, ubicacion, minTemp, maxTemp, tempInicial };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                controlTemperatura.agregarSensor(result.value);
                Swal.fire('¡Sensor Agregado!', 'El nuevo sensor ha sido configurado exitosamente.', 'success');
            }
        });
    });
    
    // Botón para generar reporte
    document.getElementById('btn-generar-reporte')?.addEventListener('click', function() {
        const reporte = controlTemperatura.generarReporte();
        
        // Crear ventana para mostrar reporte
        const ventanaReporte = window.open('', '_blank', 'width=800,height=600');
        ventanaReporte.document.write(`
            <html>
                <head>
                    <title>Reporte de Control de Temperatura</title>
                    <style>
                        body { font-family: Arial, sans-serif; padding: 20px; }
                        .header { text-align: center; border-bottom: 2px solid #007bff; padding-bottom: 20px; margin-bottom: 30px; }
                        .sensor { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 8px; }
                        .stats { background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 20px 0; }
                        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
                        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                        th { background-color: #f2f2f2; }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <h1>Reporte de Control de Temperatura</h1>
                        <p><strong>Fecha:</strong> ${reporte.fecha.toLocaleString()}</p>
                    </div>
                    
                    <div class="stats">
                        <h3>Estadísticas Generales</h3>
                        <p><strong>Total de Sensores:</strong> ${reporte.estadisticasGenerales.totalSensores}</p>
                        <p><strong>Sensores Activos:</strong> ${reporte.estadisticasGenerales.sensoresActivos}</p>
                        <p><strong>Alertas Totales:</strong> ${reporte.estadisticasGenerales.alertasTotales}</p>
                        <p><strong>Tiempo de Funcionamiento:</strong> ${reporte.estadisticasGenerales.tiempoFuncionamiento}</p>
                    </div>
                    
                    <h3>Detalle por Sensor</h3>
                    ${reporte.sensores.map(sensor => `
                        <div class="sensor">
                            <h4>${sensor.nombre}</h4>
                            <p><strong>Ubicación:</strong> ${sensor.ubicacion}</p>
                            <p><strong>Temperatura Promedio:</strong> ${sensor.temperaturaPromedio}°C</p>
                            <p><strong>Alertas Generadas:</strong> ${sensor.alertas}</p>
                            <p><strong>Cumplimiento:</strong> ${sensor.cumplimiento}%</p>
                        </div>
                    `).join('')}
                </body>
            </html>
        `);
        ventanaReporte.document.close();
    });
});

// Exportar para uso global
window.ControlTemperatura = ControlTemperatura;