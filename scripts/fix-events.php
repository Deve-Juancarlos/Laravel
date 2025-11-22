<?php
/**
 * ============================================
 * SIFANO - Verificador de Eventos (Windows)
 * ============================================
 * Uso: php scripts/fix-events.php
 * ============================================
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🔍 =======================================\n";
echo "   SIFANO - Análisis de Eventos\n";
echo "=========================================\n\n";

// ============================================
// 1. BUSCAR EVENTOS DISPARADOS EN EL CÓDIGO
// ============================================
echo "📡 Buscando eventos disparados...\n\n";

$projectPath = __DIR__ . '/../app';
$events = [];
$listeners = [];

// Buscar archivos PHP recursivamente
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($projectPath, RecursiveDirectoryIterator::SKIP_DOTS)
);

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $content = file_get_contents($file->getRealPath());
        
        // Buscar event() o Event::dispatch()
        preg_match_all('/event\s*\(\s*new\s+([A-Za-z\\\\]+)/', $content, $matches1);
        preg_match_all('/Event::dispatch\s*\(\s*new\s+([A-Za-z\\\\]+)/', $content, $matches2);
        
        $foundEvents = array_merge($matches1[1] ?? [], $matches2[1] ?? []);
        
        foreach ($foundEvents as $event) {
            $fullEventName = $event;
            if (!str_starts_with($event, 'App\\')) {
                $fullEventName = 'App\\Events\\' . $event;
            }
            $events[$fullEventName] = true;
        }
    }
}

// ============================================
// 2. BUSCAR LISTENERS EN app/Listeners
// ============================================
$listenersPath = __DIR__ . '/../app/Listeners';

if (is_dir($listenersPath)) {
    $listenerIterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($listenersPath, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    
    foreach ($listenerIterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $content = file_get_contents($file->getRealPath());
            
            // Buscar método handle con type-hint
            preg_match('/public\s+function\s+handle\s*\(\s*([A-Za-z\\\\]+)/', $content, $match);
            
            if (isset($match[1])) {
                $eventType = $match[1];
                if (!str_starts_with($eventType, 'App\\')) {
                    $eventType = 'App\\Events\\' . $eventType;
                }
                
                $listenerName = basename($file->getRealPath(), '.php');
                $listeners[$eventType][] = 'App\\Listeners\\' . $listenerName;
            }
        }
    }
}

// ============================================
// 3. REPORTE
// ============================================
echo "📊 RESULTADOS:\n";
echo "=========================================\n\n";

$hasIssues = false;

if (empty($events)) {
    echo "ℹ️  No se encontraron eventos disparados.\n";
    echo "   (Normal si no usas sistema de eventos)\n\n";
} else {
    foreach (array_keys($events) as $event) {
        $eventName = class_basename($event);
        
        if (isset($listeners[$event])) {
            echo "✅ {$eventName}\n";
            foreach ($listeners[$event] as $listener) {
                echo "   → " . class_basename($listener) . "\n";
            }
        } else {
            echo "❌ {$eventName}\n";
            echo "   ⚠️  SIN LISTENERS - Se dispara pero nadie lo escucha\n";
            $hasIssues = true;
        }
        echo "\n";
    }
}

// ============================================
// 4. COMANDOS ÚTILES
// ============================================
echo "=========================================\n";
echo "🔧 COMANDOS ÚTILES:\n";
echo "=========================================\n\n";

if ($hasIssues) {
    echo "⚠️  Eventos sin listeners encontrados.\n\n";
    echo "Para solucionarlo:\n";
    echo "1. php artisan make:listener NombreListener\n";
    echo "2. Implementar método handle(\$event)\n";
    echo "3. Laravel los detecta automáticamente\n\n";
}

echo "Cachear eventos:\n";
echo "   php artisan event:cache\n\n";

echo "Ver lista completa:\n";
echo "   php artisan event:list\n\n";

echo "=========================================\n";
echo $hasIssues ? "⚠️  ACCIÓN REQUERIDA\n" : "✅ TODO OK\n";
echo "=========================================\n";
