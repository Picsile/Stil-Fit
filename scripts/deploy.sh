#!/usr/bin/env bash
# Деплой LookFit на сервере (/opt/lookfit)
#
# Использование:
#   ./scripts/deploy.sh           — обновить код + перезапустить backend
#   ./scripts/deploy.sh --all     — пересобрать все контейнеры (frontend тоже)
#   ./scripts/deploy.sh --db      — сбросить БД из lookfit.sql (ОСТОРОЖНО: данные удалятся)
#   ./scripts/deploy.sh --all --db
#   ./scripts/deploy.sh --logs    — только показать последние ошибки

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"
DB_DUMP="$PROJECT_DIR/lookfit.sql"
DB_NAME="lookfit"
DB_USER="root"
DB_PASSWORD="${DB_ROOT_PASSWORD:-}"

# Цвета
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

info()    { echo -e "${GREEN}▶ $*${NC}"; }
warn()    { echo -e "${YELLOW}⚠ $*${NC}"; }
error()   { echo -e "${RED}✗ $*${NC}"; exit 1; }

# Флаги
BUILD_ALL=false
RESET_DB=false
LOGS_ONLY=false

for arg in "$@"; do
  case $arg in
    --all)   BUILD_ALL=true ;;
    --db)    RESET_DB=true ;;
    --logs)  LOGS_ONLY=true ;;
  esac
done

cd "$PROJECT_DIR"

# ──────────────── Только логи ────────────────
if $LOGS_ONLY; then
  info "Последние ошибки из app.log:"
  docker exec lookfit_backend grep -E "\[error\]" ./backend/runtime/logs/app.log 2>/dev/null | tail -30 || warn "Логов нет"
  exit 0
fi

# ──────────────── Git pull ────────────────
info "Обновляем код..."
git pull

# ──────────────── Сброс БД ────────────────
if $RESET_DB; then
  [[ -f "$DB_DUMP" ]] || error "Файл $DB_DUMP не найден"
  [[ -n "$DB_PASSWORD" ]] || { read -rsp "DB root password: " DB_PASSWORD; echo; }

  warn "Сброс базы данных $DB_NAME..."
  docker exec lookfit_db mysql -u "$DB_USER" -p"$DB_PASSWORD" -e \
    "DROP DATABASE IF EXISTS $DB_NAME; CREATE DATABASE $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

  info "Импортируем дамп..."
  docker exec -i lookfit_db mysql -u "$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" < "$DB_DUMP"
  info "БД восстановлена"
fi

# ──────────────── Сборка ────────────────
if $BUILD_ALL; then
  info "Пересобираем все контейнеры..."
  docker compose build
else
  info "Пересобираем backend..."
  docker compose build backend
fi

# ──────────────── Запуск ────────────────
info "Поднимаем контейнеры..."
docker compose up -d

# ──────────────── Туннель ────────────────
info "Проверяем SOCKS-туннель..."
if systemctl is-active --quiet apimart-tunnel.service; then
  echo "  Туннель активен"
else
  warn "Туннель не запущен, перезапускаем..."
  systemctl restart apimart-tunnel.service
  sleep 2
  if systemctl is-active --quiet apimart-tunnel.service; then
    info "Туннель запущен"
  else
    warn "Туннель не стартовал, проверьте: systemctl status apimart-tunnel.service"
  fi
fi

# Проверяем что порт 1080 слушает
if ss -tlnp | grep -q ':1080'; then
  echo "  Порт 1080 открыт ✓"
else
  warn "Порт 1080 не слушает! Туннель может быть недоступен."
fi

# ──────────────── Статус ────────────────
info "Статус контейнеров:"
docker compose ps

echo ""
info "Последние ошибки (последние 10 строк):"
docker exec lookfit_backend grep -E "\[error\]" ./backend/runtime/logs/app.log 2>/dev/null | tail -10 || echo "  Ошибок нет"

echo ""
echo -e "${GREEN}✓ Деплой завершён${NC}"
