# DevFinder - Node.js to PHP Migration

> Proof of Concept: Modern PHP Backend using Domain-Driven Design

## 📁 Estrutura do Repositório

```
devfinder-php/
├── src/                              # 🆕 PHP Application (Laravel Minimal + DDD)
│   ├── app/                          # Application code
│   ├── bootstrap/                    # Bootstrap configuration
│   ├── config/                       # Configuration files
│   ├── routes/                       # API routes
│   ├── public/                       # Public entry point
│   ├── index.php                     # Main application entry
│   ├── composer.json                 # PHP dependencies
│   ├── .env                          # Environment variables
│   ├── README.md                     # PHP Project docs
│   └── requests.http                 # API test requests
│
├── src-node-express/                 # Original Node.js/Express (não alterar)
│   ├── package.json                  
│   ├── server.ts
│   ├── controllers/
│   ├── models/
│   ├── routes/
│   └── ...
│
├── agents/
│   └── 101-node-to-php.md           # 📋 Plano de Migração (Detalhado)
│
├── docker-compose.yml                # Docker configuration
├── Dockerfile                        # Docker image build
├── legacy.md                         # Requisitos iniciais
└── README.md                         # Este arquivo
```

## 🎯 Objetivo

Reescrever um backend Node.js/Express em PHP usando:
- **Domain-Driven Design (DDD)**
- **Repository Pattern (In-Memory)**
- **JWT Authentication**
- **Clean Architecture**
- **Docker containerization**

Resultado: Uma POC profissional para portfolio de entrevista.

## ⚡ Quick Start

### Docker (Recomendado)
```bash
# Build and run
docker-compose up --build

# API: http://localhost:8000/v1
```

### Local (sem Docker)
```bash
# Install PHP dependencies
cd src
composer install

# Setup environment
cp .env.example .env

# Run server
php -S localhost:8000
```

### Testar API
```bash
# Via curl
curl http://localhost:8000/v1/devs

# Via REST Client (VS Code)
# Abra: src/requests.http
```

## 📊 Stack Comparison

| Aspecto | Node.js | PHP |
|---------|---------|-----|
| Runtime | Node 18 | PHP 8.2+ |
| Framework | Express.js | Minimalista |
| Arch Pattern | MVC | **DDD** |
| Persistence | MongoDB/Mongoose | **In-Memory** |
| Auth | passport-github + JWT | **JWT Native** |
| Container | Docker | **Docker-compose** |

## 🏗️ Arquitetura DDD

```
Domain Layer (Business Logic)
├── Entities: DevEntity, ChannelEntity, VideoEntity
├── Repositories (interfaces): DevRepository, ChannelRepository, VideoRepository
└── Services: DevService, ChannelService, VideoService

Infrastructure Layer (Technical)
├── Repositories (impl): InMemoryDevRepository, InMemoryChannelRepository, InMemoryVideoRepository
├── Authentication: JWTAuth
└── SimpleRouter

Presentation Layer (HTTP)
├── Controllers: DevController, ChannelController, VideoController
├── Middleware: AuthMiddleware, OptionalAuthMiddleware
└── Routes: API routes
```

## 📚 Endpoints Principais

### Devs
- `GET /v1/devs` - List devs
- `POST /v1/devs` - Create dev
- `GET /v1/devs/:username` - Get dev
- `GET /v1/me` - Get current dev (auth required)
- `POST /v1/devs/:username/like` - Like dev

### Channels
- `GET /v1/channels` - List channels
- `POST /v1/channels` - Create channel
- `GET /v1/channels/:name` - Get channel

### Videos
- `GET /v1/videos` - List videos
- `GET /v1/trending` - Trending videos
- `POST /v1/video` - Create video

Ver `src/requests.http` para exemplos completos.

## 🔐 Authentication

1. Create dev: `POST /v1/devs`
2. Response inclui `token`
3. Use in header: `Authorization: Bearer <token>`

Exemplo:
```bash
curl -X POST http://localhost:8000/v1/devs \
  -H "Content-Type: application/json" \
  -d '{"username": "marcelovilela"}'
```

## 📋 Plano Detalhado

Veja [agents/101-node-to-php.md](agents/101-node-to-php.md) para:
- ✅ Fases de desenvolvimento (todas completadas)
- ✅ Stack escolhida e justificativa
- ✅ Estrutura DDD em PHP
- ✅ Implementação In-Memory Storage
- ✅ JWT Flow
- ✅ Arquivos criados

## 🧪 Testes

### Com REST Client (VS Code)
```
# Instale: REST Client extension
# Abra: src/requests.http
# Click: Send Request
```

### Com curl
```bash
# List devs
curl http://localhost:8000/v1/devs?page=1

# Create dev
curl -X POST http://localhost:8000/v1/devs \
  -H "Content-Type: application/json" \
  -d '{"username":"test","name":"Test User"}'
```

## 📦 Dependências

### PHP
- `firebase/jwt` - JWT token generation/validation

### Docker
- PHP 8.2-fpm-alpine
- Composer

Ver `src/composer.json` para lista completa.

## 🎓 Portfolio Highlights

- ✅ DDD implementation (Domain-Driven Design)
- ✅ Repository Pattern without ORM
- ✅ Custom JWT (não usar pacotes prontos)
- ✅ PHP 8.2 modern features (type hints, match, etc)
- ✅ Clean Architecture & SOLID principles
- ✅ Docker containerization
- ✅ Zero-dependencies core logic

## 🔄 Desenvolvimento

```bash
# Enter container
docker-compose exec api bash

# View logs
docker-compose logs -f api

# Rebuild
docker-compose down && docker-compose up --build
```

## 📝 Documentação

- [PHP Project README](src/README.md) - Detailed API docs
- [Migration Plan](agents/101-node-to-php.md) - Complete migration details
- [API Requests](src/requests.http) - Example requests

## ❓ FAQ

**P: Por que In-Memory e não banco de dados?**
A: É uma POC. In-Memory mostra o padrão Repository sem dependência de BD específico.

**P: Por que DDD?**
A: Demonstra conhecimento em arquitetura enterprise e design patterns avançados.

**P: Dados são persistidos?**
A: Não - ao reiniciar o container, dados são perdidos (esperado para POC).

**P: Como adicionar persistência real?**
A: Apenas trocar `InMemoryDevRepository` pela `DatabaseDevRepository` (mantém interface).

## 🚀 Próximos Passos (Opcional)

- [ ] Adicionar testes unitários (PHPUnit)
- [ ] Swagger/OpenAPI documentation
- [ ] GitHub OAuth integration
- [ ] YouTube API integration
- [ ] Logging & monitoring
- [ ] CI/CD pipeline

## 📄 License

MIT

---

**Status:** ✅ Complete  
**Data:** 2026-04-28  
**Uso:** Portfolio / Interview POC  
**Author:** Marcelo Vilela
