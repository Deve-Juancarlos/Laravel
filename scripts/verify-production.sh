#!/bin/bash
# ============================================
# SIFANO - Health Check Post-Deployment
# ============================================
# Uso: bash scripts/verify-production.sh
# ============================================

echo "🏥 ======================================"
echo "   SIFANO - Health Check"
echo "========================================"
echo ""

ERRORS=0

# Colores
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

# ============================================
# 1. APP_KEY
# ============================================
echo -n "🔐 APP_KEY configurado... "
if grep -q "APP_KEY=base64:" .env 2>/dev/null; then
    echo -e "${GREEN}✅${NC}"
else
    echo -e "${RED}❌ FALTA${NC}"
    ERRORS=$((ERRORS + 1))
fi

# ============================================
# 2. BASE DE DATOS
# ============================================
echo -n "🗄️  Conexión SQL Server (SIFANO)... "
if php artisan db:monitor 2>/dev/null; then
    echo -e "${GREEN}✅${NC}"
else
    echo -e "${RED}❌ ERROR${NC}"
    ERRORS=$((ERRORS + 1))
fi

# ============================================
# 3. CACHÉS
# ============================================
echo -n "⚡ Config cacheado... "
if [ -f "bootstrap/cache/config.php" ]; then
    echo -e "${GREEN}✅${NC}"
else
    echo -e "${YELLOW}⚠️  No cacheado${NC}"
fi

echo -n "⚡ Rutas cacheadas... "
if [ -f "bootstrap/cache/routes-v7.php" ]; then
    echo -e "${GREEN}✅${NC}"
else
    echo -e "${YELLOW}⚠️  No cacheado${NC}"
fi

# ============================================
# 4. PERMISOS
# ============================================
echo -n "🔒 storage/ escribible... "
if [ -w "storage/logs" ]; then
    echo -e "${GREEN}✅${NC}"
else
    echo -e "${RED}❌ SIN PERMISOS${NC}"
    ERRORS=$((ERRORS + 1))
fi

# ============================================
# 5. VARIABLES CRÍTICAS
# ============================================
echo -n "📧 Mail configurado... "
if grep -q "MAIL_HOST=" .env 2>/dev/null && ! grep -q "MAIL_HOST=$" .env; then
    echo -e "${GREEN}✅${NC}"
else
    echo -e "${YELLOW}⚠️  Revisar${NC}"
fi

echo -n "🔄 Queue configurado... "
if grep -q "QUEUE_CONNECTION=" .env 2>/dev/null && ! grep -q "QUEUE_CONNECTION=$" .env; then
    echo -e "${GREEN}✅${NC}"
else
    echo -e "${YELLOW}⚠️  Revisar${NC}"
fi

# ============================================
# 6. ENDPOINT TEST
# ============================================
echo ""
echo "🌐 Probando endpoint..."
if command -v curl &> /dev/null; then
    HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" http://localhost 2>/dev/null || echo "000")
    if [ "$HTTP_CODE" = "200" ]; then
        echo -e "   ${GREEN}✅ HTTP 200 OK${NC}"
    else
        echo -e "   ${YELLOW}⚠️  HTTP $HTTP_CODE${NC}"
    fi
else
    echo -e "   ${YELLOW}⚠️  curl no disponible${NC}"
fi

# ============================================
# RESUMEN
# ============================================
echo ""
echo "========================================"
if [ $ERRORS -eq 0 ]; then
    echo -e "${GREEN}✅ SISTEMA SALUDABLE${NC}"
    echo "   Todo funcionando correctamente"
else
    echo -e "${RED}⚠️  $ERRORS ERRORES CRÍTICOS ENCONTRADOS${NC}"
    echo "   Revisar logs: tail -f storage/logs/laravel.log"
fi
echo "========================================"
echo ""
