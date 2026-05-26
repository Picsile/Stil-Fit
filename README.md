# СтильФит — AI-генератор образов

> **Живой проект:** [stilfit.ru](https://stilfit.ru)

Fullstack-приложение для создания стильных образов с помощью искусственного интеллекта. Пользователи добавляют вещи из своего гардероба, а нейросеть генерирует луки на основе их внешности и промпта.

---

## Возможности

- 🤖 **AI-генерация образов** — интеграция с GPT Image 2 через APIMart API
- 👗 **Каталог вещей и образов** — создание постов с изображениями, тегами и ссылками на магазины
- 🗂 **Доски (лукбуки)** — сохранение понравившихся образов в личные коллекции
- ❤️ **Социальные функции** — лайки, избранное, профили пользователей
- 🪙 **Система FitCoins** — внутренняя валюта для генераций
- 🔍 **Поиск по тегам** — умная система тегирования контента
- 🛡 **Модерация контента** — посты проходят модерацию перед публикацией
- 📱 **Адаптивный интерфейс** — оптимизировано под мобильные устройства

---

## Технологии

### Frontend
| Технология | Назначение |
|---|---|
| Vue 3 + TypeScript | SPA, Composition API |
| Vite | Сборка и dev-сервер |
| Tailwind CSS | Стилизация |
| Pinia | Глобальное состояние |
| Vue Router | Навигация |
| vee-validate | Валидация форм |

### Backend
| Технология | Назначение |
|---|---|
| PHP 8.2 + Yii2 | REST API |
| MySQL 8.0 | База данных |
| Nginx | Reverse proxy, раздача статики |
| Docker Compose | Оркестрация контейнеров |

### Инфраструктура
- **VPS-сервер** — продакшен на Linux
- **SSL** — Let's Encrypt
- **SOCKS5-туннель** — autossh для проксирования запросов к AI API
- **CI** — деплой через `git pull` + пересборка Docker-образов

---

## Архитектура

```
┌─────────────────────────────────────────────────┐
│                    Клиент                        │
│              Vue 3 SPA (Vite)                    │
└────────────────────┬────────────────────────────┘
                     │ HTTPS
┌────────────────────▼────────────────────────────┐
│                   Nginx                          │
│    /          → frontend (dist)                  │
│    /backend/  → PHP-FPM (Yii2)                   │
└────────────────────┬────────────────────────────┘
                     │
          ┌──────────┴──────────┐
          │                     │
┌─────────▼──────┐    ┌────────▼────────┐
│   Yii2 API     │    │    MySQL 8.0     │
│  (PHP-FPM)     │    │                 │
└─────────┬──────┘    └─────────────────┘
          │ SOCKS5
┌─────────▼──────┐
│  APIMart API   │
│ (GPT Image 2)  │
└────────────────┘
```

---

## Структура репозитория

```
├── frontend/          # Vue 3 SPA
│   ├── src/
│   │   ├── views/     # Страницы
│   │   ├── modules/   # Функциональные модули (generation, auth...)
│   │   ├── components/# UI-компоненты
│   │   ├── stores/    # Pinia stores
│   │   └── types/     # TypeScript типы
│   └── Dockerfile
├── backend/           # Yii2 REST API
│   ├── controllers/   # PostController, ViewController, GenerationController...
│   ├── models/        # ActiveRecord модели
│   ├── components/    # ProxyCurl и др.
│   └── Dockerfile
├── scripts/
│   └── deploy.sh      # Скрипт деплоя
└── docker-compose.yml
```

---

## Локальный запуск

### Требования
- Node.js 20+
- PHP 8.2+ (OSPanel или Docker)
- MySQL 8.0
- Composer

### Быстрый старт

```bash
# Frontend
cd frontend
npm install
npm run dev        # http://localhost:5173

# Backend (OSPanel)
cd backend
composer install
# Настроить config/db-local.php под свою БД
```

### Продакшен (Docker)

```bash
cp .env.example .env   # заполнить переменные
docker compose build
docker compose up -d
```

Или через скрипт:

```bash
chmod +x scripts/deploy.sh

./scripts/deploy.sh          # обновить backend
./scripts/deploy.sh --all    # обновить всё
./scripts/deploy.sh --db     # накатить дамп БД
./scripts/deploy.sh --logs   # посмотреть ошибки
```

---

## Переменные окружения

Скопируй `.env.example` → `.env` и заполни:

| Переменная | Описание |
|---|---|
| `DB_NAME` | Имя базы данных |
| `DB_ROOT_PASSWORD` | Пароль MySQL root |
| `COOKIE_VALIDATION_KEY` | Секретный ключ Yii2 |
| `SOCKS_PROXY` | Адрес SOCKS5-прокси для AI API |
| `USE_SOCKS_PROXY` | `1` — включить, `0` — выключить |
