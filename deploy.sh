#!/bin/bash
set -e

echo "========================================="
echo "  Deploy Sistema JR - Producao"
echo "========================================="

# Cores
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

step() { echo -e "\n${GREEN}[>>] $1${NC}"; }
warn() { echo -e "${YELLOW}[!!] $1${NC}"; }

APP_DIR="/var/www/jr"
COMPOSE="docker compose -f docker-compose.prod.yml"

cd "$APP_DIR"

# 1. Pull latest code
step "Atualizando codigo..."
git pull origin main

# 2. Install PHP dependencies (production)
step "Instalando dependencias PHP..."
$COMPOSE run --rm app composer install --no-dev --optimize-autoloader --no-interaction

# 3. Build frontend assets
step "Compilando assets frontend..."
$COMPOSE run --rm app npm ci --production=false
$COMPOSE run --rm app npx vite build

# 4. Build/rebuild containers
step "Reconstruindo containers..."
$COMPOSE build --pull

# 5. Stop old containers
step "Reiniciando servicos..."
$COMPOSE down

# 6. Start new containers
$COMPOSE up -d

# 7. Run migrations
step "Rodando migrations..."
$COMPOSE exec app php artisan migrate --force

# 8. Optimize Laravel
step "Otimizando Laravel..."
$COMPOSE exec app php artisan optimize
$COMPOSE exec app php artisan view:cache
$COMPOSE exec app php artisan event:cache

# 9. Storage link
$COMPOSE exec app php artisan storage:link 2>/dev/null || true

# 10. Fix permissions
step "Corrigindo permissoes..."
$COMPOSE exec app chmod -R 775 storage bootstrap/cache

echo ""
echo "========================================="
echo -e "  ${GREEN}Deploy concluido com sucesso!${NC}"
echo "========================================="
echo ""
$COMPOSE ps
