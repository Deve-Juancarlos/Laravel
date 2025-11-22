# ============================================
# SIFANO - Script de Deployment (Windows)
# ============================================
# Uso: .\scripts\deployment.ps1
# ============================================

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "   SIFANO - Deployment a Producción" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

$ErrorCount = 0

# ============================================
# 1. VERIFICAR APP_KEY
# ============================================
Write-Host "🔐 [1/8] Verificando APP_KEY..." -ForegroundColor Yellow

if (!(Test-Path ".env")) {
    Write-Host "   ❌ CRÍTICO: Archivo .env no existe" -ForegroundColor Red
    $ErrorCount++
} else {
    $envContent = Get-Content ".env" -Raw
    if ($envContent -match "APP_KEY=\s*$" -or $envContent -notmatch "APP_KEY=") {
        Write-Host "   ⚠️  APP_KEY faltante, generando..." -ForegroundColor Yellow
        php artisan key:generate --force
        Write-Host "   ✅ APP_KEY generado" -ForegroundColor Green
    } else {
        Write-Host "   ✅ APP_KEY configurado" -ForegroundColor Green
    }
}

# ============================================
# 2. VERIFICAR CONEXIÓN SQL SERVER
# ============================================
Write-Host ""
Write-Host "🗄️  [2/8] Verificando conexión a SQL Server..." -ForegroundColor Yellow

$dbTest = php artisan db:monitor 2>&1
if ($LASTEXITCODE -eq 0) {
    Write-Host "   ✅ Conexión a base de datos OK" -ForegroundColor Green
} else {
    Write-Host "   ❌ CRÍTICO: No se puede conectar a SQL Server" -ForegroundColor Red
    Write-Host "   Verifica en .env:" -ForegroundColor Yellow
    Write-Host "   - DB_HOST" -ForegroundColor Yellow
    Write-Host "   - DB_DATABASE=SIFANO" -ForegroundColor Yellow
    Write-Host "   - DB_USERNAME / DB_PASSWORD" -ForegroundColor Yellow
    $ErrorCount++
}

# ============================================
# 3. LIMPIAR CACHÉS ANTIGUOS
# ============================================
Write-Host ""
Write-Host "🧹 [3/8] Limpiando cachés antiguos..." -ForegroundColor Yellow

php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
php artisan event:clear 2>$null

Write-Host "   ✅ Cachés limpiados" -ForegroundColor Green

# ============================================
# 4. OPTIMIZAR COMPOSER
# ============================================
Write-Host ""
Write-Host "📦 [4/8] Optimizando autoload de Composer..." -ForegroundColor Yellow

composer install --optimize-autoloader --no-dev --quiet
if ($LASTEXITCODE -eq 0) {
    Write-Host "   ✅ Composer optimizado" -ForegroundColor Green
} else {
    Write-Host "   ⚠️  Revisar composer" -ForegroundColor Yellow
}

# ============================================
# 5. MIGRACIONES
# ============================================
Write-Host ""
Write-Host "📊 [5/8] Ejecutando migraciones..." -ForegroundColor Yellow

php artisan migrate --force
if ($LASTEXITCODE -eq 0) {
    Write-Host "   ✅ Migraciones ejecutadas" -ForegroundColor Green
} else {
    Write-Host "   ⚠️  Revisar migraciones" -ForegroundColor Yellow
}

# ============================================
# 6. CACHEAR CONFIGURACIONES
# ============================================
Write-Host ""
Write-Host "⚡ [6/8] Generando cachés optimizados..." -ForegroundColor Yellow

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

Write-Host "   ✅ Cachés optimizados generados" -ForegroundColor Green

# ============================================
# 7. VERIFICAR PERMISOS (storage)
# ============================================
Write-Host ""
Write-Host "🔒 [7/8] Verificando permisos de storage..." -ForegroundColor Yellow

if (Test-Path "storage\logs") {
    Write-Host "   ✅ Directorio storage/logs existe" -ForegroundColor Green
} else {
    Write-Host "   ⚠️  Crear storage/logs manualmente" -ForegroundColor Yellow
}

# ============================================
# 8. RESUMEN
# ============================================
Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan

if ($ErrorCount -eq 0) {
    Write-Host "✅ DEPLOYMENT COMPLETADO EXITOSAMENTE" -ForegroundColor Green
} else {
    Write-Host "⚠️  $ErrorCount ERRORES ENCONTRADOS" -ForegroundColor Red
}

Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "📋 Próximos pasos:" -ForegroundColor Yellow
Write-Host "   1. Ejecutar: .\scripts\verify-production.ps1" -ForegroundColor White
Write-Host "   2. Verificar logs: Get-Content storage\logs\laravel.log -Tail 20" -ForegroundColor White
Write-Host ""
