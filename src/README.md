# DevFinder PHP API

Modern PHP Backend (2026) - Rewrite from Node.js/Express to Laravel

## 🎯 Overview

DevFinder é um projeto de portfolio profissional que demonstra uma migração bem-arquitetada de uma API Node.js/Express para PHP, utilizando **Domain-Driven Design (DDD)** e padrões modernos de 2026.

**Stack:**
- PHP 8.2+
- Laravel 11 (minimal setup)
- Domain-Driven Design (DDD)
- In-Memory Repository Pattern
- JWT Authentication
- Docker + docker-compose

## 📋 Estrutura do Projeto

```
src/
├── app/
│   ├── Core/                    # Domain Layer (negócio)
│   │   ├── Dev/
│   │   ├── Channel/
│   │   └── Video/
│   ├── Infrastructure/          # Infrastructure Layer
│   │   ├── Repositories/        # In-Memory implementations
│   │   └── Authentication/      # JWT
│   └── Http/                    # Presentation Layer
│       ├── Controllers/
│       ├── Middleware/
│       └── Requests/
├── bootstrap/                   # App initialization
├── config/                      # Configuration files
├── routes/                      # API Routes
└── public/index.php             # Entry point
```

## 🚀 Quick Start

### Option 1: Docker (Recommended)

```bash
# Clone or setup
cd devfinder-php

# Build and run with Docker
docker-compose up --build

# API será acessível em: http://localhost:8000/v1
```

### Option 2: Local PHP

```bash
# Install dependencies
cd src
composer install

# Setup environment
cp .env.example .env

# Run application
php artisan serve --host=0.0.0.0 --port=8000
```

## 📚 API Endpoints

### App Info
```http
GET /v1/
```

### Devs
```http
GET /v1/devs?page=1                    # List devs (paginated)
GET /v1/devs/:username                 # Get dev by username
GET /v1/me                             # Get current dev (auth required)
POST /v1/devs                          # Create dev (auth required)
POST /v1/devs/:username/like           # Like dev (auth required)
DELETE /v1/devs/:username/like         # Unlike dev (auth required)
POST /v1/devs/:username/dislike        # Dislike dev (auth required)
DELETE /v1/devs/:username/dislike      # Remove dislike (auth required)
GET /v1/devs/:username/likes           # Get dev's likes
```

### Channels
```http
GET /v1/channels                       # List channels
GET /v1/channels/:name                 # Get channel by name
POST /v1/channels                      # Create channel (auth required)
POST /v1/channels/:id/like             # Like channel (auth required)
DELETE /v1/channels/:id/like           # Unlike channel (auth required)
POST /v1/channels/:id/follow           # Follow channel (auth required)
POST /v1/channels/refresh              # Refresh channels (auth required)
```

### Videos
```http
GET /v1/videos?page=1                  # List videos (paginated)
GET /v1/video/:id                      # Get video by ID
GET /v1/trending                       # Get trending videos
GET /v1/subscriptions                  # Get subscription videos (auth required)
POST /v1/video                         # Create video (auth required)
POST /v1/video/:id/refresh             # Refresh video (auth required)
```

## 🔐 Authentication

Todos os endpoints autenticados requerem header JWT:

```http
Authorization: Bearer <your_jwt_token>
```

### Gerar Token

```bash
curl -X POST http://localhost:8000/v1/devs \
  -H "Content-Type: application/json" \
  -d '{"username": "seu_usuario"}'
```

Resposta incluirá `token` para usar em requisições subsequentes.

## 🎨 Architecture: DDD (Domain-Driven Design)

### Layers:

1. **Domain Layer** (`app/Core/`)
   - Business Logic
   - Entities (DevEntity, ChannelEntity, VideoEntity)
   - Repository Interfaces
   - Services
   - Independente de frameworks

2. **Infrastructure Layer** (`app/Infrastructure/`)
   - Repository Implementations (In-Memory)
   - Authentication (JWT)
   - External Services

3. **Presentation Layer** (`app/Http/`)
   - Controllers
   - Middleware
   - Request/Response handling

### In-Memory Storage

Dados são armazenados em memória (arrays PHP estáticos) - perfeito para POC:

```php
// Demonstração
InMemoryDevRepository::$devs = [];
```

**Nota:** Dados são perdidos ao reiniciar o container (esperado para POC)

## 🧪 Testing Endpoints

### Com curl

```bash
# Get devs
curl http://localhost:8000/v1/devs

# Create dev (without auth, just for demo)
curl -X POST http://localhost:8000/v1/devs \
  -H "Content-Type: application/json" \
  -d '{"username": "marcelovilela", "name": "Marcelo Vilela"}'
```

### Com REST Client (VS Code)

Crie arquivo `requests.http`:

```http
@baseUrl = http://localhost:8000/v1

### Get app info
GET {{baseUrl}}/

### List devs
GET {{baseUrl}}/devs?page=1

### List channels
GET {{baseUrl}}/channels
```

## 🔄 Migração do Node.js

Comparação arquitetura:

| Node.js | PHP |
|---------|-----|
| Express.js | Laravel (minimal) |
| TypeScript | PHP 8.2 (typed) |
| Mongoose | In-Memory Repository |
| controllers/ | Controllers/ |
| services/ | Services/ |
| models/ | Entities/ |
| middleware/ | Middleware/ |

## 📦 Dependencies

- `firebase/jwt` - JWT Authentication
- `symfony/http-foundation` - HTTP utilities

## 🛠️ Development

### Environment Variables

```bash
APP_ENV=local
APP_DEBUG=true
JWT_SECRET=your-secret-key
JWT_EXPIRES_IN=604800
```

### Commands

```bash
# Enter container
docker-compose exec api bash

# View logs
docker-compose logs -f api

# Rebuild
docker-compose up --build
```

## 📝 Portfolio Highlights

✅ **DDD Implementation** - Clear separation of concerns  
✅ **Modern PHP** - Type hints, strict types, PHP 8.2+  
✅ **JWT Auth** - Custom implementation without external packages  
✅ **In-Memory Repositories** - Abstraction pattern without DB  
✅ **Docker Ready** - Production-ready containerization  
✅ **Clean Code** - SOLID principles, PSR standards  

## 🔗 Related Files

- [Plano de Migração](../../agents/101-node-to-php.md)
- [Node.js Original](../../src-node-express/)
- [docker-compose.yml](../../docker-compose.yml)
- [Dockerfile](../../Dockerfile)

## 📄 License

MIT

---

**Autor:** Marcelo Vilela | **Data:** 2026 | **Status:** POC / Portfolio
