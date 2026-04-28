# Plano: Migração DevFinder de Node.js para PHP

## 📋 Resumo do Projeto Node.js
- **Framework**: Express.js
- **Linguagem**: TypeScript
- **BD**: MongoDB com Mongoose
- **Autenticação**: JWT + GitHub OAuth
- **Porta**: 3333

### Stack Atual (package.json)
- express, axios, cors, dotenv
- jsonwebtoken, passport, passport-github
- mongoose, mongoose-paginate-v2
- swagger-ui-express, swagger-autogen

### Modelos de Dados
1. **Dev**: name, user, bio, avatar, likes[], deslikes[], follow[], ignore[]
2. **Channel**: name, link, avatar, userGithub, description, category, tags[], likes[], deslikes[]
3. **Video**: title, url, channel_id, channel, channel_url, channel_icon, thumbnail, viewnum, date

### Endpoints Principais
- `GET /` - App info
- `GET/POST /devs` - List/Create devs (paginated)
- `GET /devs/:username` - Dev by username
- `GET /me` - Profile do dev logado
- `POST /devs/:username/like|dislike` - Manage likes/dislikes
- `GET/POST /channels` - Channel management
- `POST /channels/refresh` - Refresh channel videos
- `POST /channels/:channelName/follow|ignore` - Follow/ignore channels
- `GET/POST /video` - Video management
- `GET /trending|subscriptions` - Trending & subscription videos
- `POST /search` - Search functionality

---

## 🎯 Objetivo PHP 2026
Criar uma POC (Proof of Concept) em PHP para portfolio profissional

### Requisitos Técnicos
✅ **Framework**: Laravel 11+  
✅ **Package Manager**: Composer  
✅ **Arquitetura**: DDD (Domain-Driven Design)  
✅ **Padrões**: Controller → Service → Repository  
✅ **Armazenamento**: In-Memory (sem MongoDB)  
✅ **Containerização**: Docker + docker-compose.yml  
✅ **Localização**: `/src` (não alterar `/src-node-express`)  

---

## 🏗️ Estrutura DDD em PHP

```
src/
├── app/
│   ├── Core/                          # Domain Layer (negócio)
│   │   ├── Dev/
│   │   │   ├── DevEntity.php
│   │   │   ├── DevRepository.php (interface)
│   │   │   └── DevService.php
│   │   ├── Channel/
│   │   │   ├── ChannelEntity.php
│   │   │   ├── ChannelRepository.php (interface)
│   │   │   └── ChannelService.php
│   │   └── Video/
│   │       ├── VideoEntity.php
│   │       ├── VideoRepository.php (interface)
│   │       └── VideoService.php
│   │
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── DevController.php
│   │   │   ├── ChannelController.php
│   │   │   └── VideoController.php
│   │   ├── Middleware/
│   │   │   ├── AuthMiddleware.php
│   │   │   └── OptionalAuthMiddleware.php
│   │   └── Requests/
│   │       ├── CreateDevRequest.php
│   │       ├── CreateChannelRequest.php
│   │       └── CreateVideoRequest.php
│   │
│   └── Infrastructure/
│       ├── Repositories/
│       │   ├── InMemoryDevRepository.php
│       │   ├── InMemoryChannelRepository.php
│       │   └── InMemoryVideoRepository.php
│       ├── Authentication/
│       │   └── JWTAuth.php
│       └── Providers/
│           └── RepositoryServiceProvider.php
│
├── routes/
│   └── api.php
├── config/
│   ├── app.php
│   ├── auth.php
│   └── jwt.php
└── storage/
    └── in_memory_db.json        # Estado em memória (simulado)
```

---

## 🔄 Fluxo de Migração

### Fase 1: Setup Inicial
- [x] Estrutura Laravel base
- [x] Docker + docker-compose.yml
- [x] Composer.json com dependências
- [x] Configuração JWT
- [x] In-Memory Storage Manager

### Fase 2: Domain Layer (Core)
- [x] Entity classes (Dev, Channel, Video)
- [x] Repository interfaces
- [x] Service classes com lógica de negócio

### Fase 3: Infrastructure Layer
- [x] InMemory Repositories (implementações)
- [x] JWT Authentication
- [x] Service Provider bindings

### Fase 4: HTTP Layer
- [x] Controllers (Dev, Channel, Video)
- [x] Request classes (validação)
- [x] Middleware (Auth, OptionalAuth)
- [x] Routes API

### Fase 5: Testes & Documentação
- [x] Swagger/OpenAPI (requests.http)
- [x] Docker build & test
- [x] README.md
- [ ] Unit Tests

---

## 🔐 Autenticação

### JWT Token Flow
```
1. GitHub OAuth Login → Token gerado
2. Bearer token em Authorization header
3. Middleware valida token
4. req->user = DevEntity
```

### Implementação
- Usar `firebase/jwt` ou `namshi/jwt`
- Armazenar token em memória com dev_id
- Validar em cada request autenticado

---

## 💾 In-Memory Storage

### Estratégia
1. Array collection por modelo (DevRepository, ChannelRepository, VideoRepository)
2. Auto-increment ID simulado (counter)
3. Implementar: create, read, update, delete, findBy*, paginate
4. **Importante**: Dados perdidos ao reiniciar container (é esperado para POC)

### Exemplo Structure
```php
// app/Infrastructure/Repositories/InMemoryDevRepository.php
class InMemoryDevRepository implements DevRepository {
    private static $devs = [];
    private static $nextId = 1;
    
    public function create(DevEntity $dev): DevEntity {
        $dev->setId($this->nextId++);
        self::$devs[$dev->getId()] = $dev;
        return $dev;
    }
}
```

---

## 📦 Dependências PHP

```
require:
  - php: ^8.2
  - laravel/framework: ^11.0
  - firebase/jwt: ^6.9
  - symfony/http-foundation: ^6.0 (incluído no Laravel)
  - symfony/var-dumper: ^6.0

require-dev:
  - phpunit/phpunit: ^11.0
  - laravel/pint: ^1.0
```

---

## 🐳 Docker Setup

### docker-compose.yml
```yaml
version: '3.9'
services:
  api:
    build: .
    ports:
      - "8000:8000"
    environment:
      - APP_ENV=local
      - JWT_SECRET=your-secret-key
      - APP_DEBUG=true
    volumes:
      - .:/app
    command: php artisan serve --host=0.0.0.0 --port=8000
```

### Dockerfile
```dockerfile
FROM php:8.2-fpm
RUN apt-get update && apt-get install -y \
    curl git unzip
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
WORKDIR /app
COPY . .
RUN composer install
EXPOSE 8000
```

---

## ✨ Destaques para Portfolio

1. **DDD Implementação**: Separação clara entre Domain, Infrastructure e Presentation
2. **In-Memory Repository Pattern**: Demonstra abstração sem dependência de BD
3. **JWT Nativo**: Sem frameworks de auth prontos (custom implementation)
4. **Docker**: Containerização profissional
5. **PHP Moderno**: PHP 8.2+ com type hints strict
6. **Clean Architecture**: Controllers enxutos, lógica em Services

---

## 📝 Próximas Etapas
1. ✅ Criar plano (ESTE DOCUMENTO)
2. ✅ Setup Docker + Laravel skeleton
3. ✅ Criar Domain Layer (Entities, Interfaces)
4. ✅ Implementar Repositories In-Memory
5. ✅ Controllers e HTTP routes
6. ✅ Testes básicos (requests.http)
7. ✅ Documentação Swagger/OpenAPI

---

## 🚀 Como Executar

### Com Docker (Recomendado)
```bash
# Na raiz do projeto
docker-compose up --build

# API estará disponível em: http://localhost:8000/v1
```

### Localmente (sem Docker)
```bash
cd src
composer install
cp .env.example .env

# Rodar servidor
php -S localhost:8000
```

### Testar Endpoints
1. **VS Code REST Client**
   - Instale extensão: REST Client
   - Abra `src/requests.http`
   - Clique em "Send Request"

2. **Curl**
   ```bash
   curl http://localhost:8000/v1/devs
   ```

3. **Postman/Insomnia**
   - Use URL base: `http://localhost:8000/v1`
   - Endpoints em `src/requests.http`

---

## 📂 Arquivos Criados

```
src/
├── app/
│   ├── Core/
│   │   ├── Dev/
│   │   │   ├── DevEntity.php           ✅
│   │   │   ├── DevRepository.php       ✅
│   │   │   └── DevService.php          ✅
│   │   ├── Channel/
│   │   │   ├── ChannelEntity.php       ✅
│   │   │   ├── ChannelRepository.php   ✅
│   │   │   └── ChannelService.php      ✅
│   │   └── Video/
│   │       ├── VideoEntity.php         ✅
│   │       ├── VideoRepository.php     ✅
│   │       └── VideoService.php        ✅
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── DevController.php       ✅
│   │   │   ├── ChannelController.php   ✅
│   │   │   └── VideoController.php     ✅
│   │   ├── Middleware/
│   │   │   ├── AuthMiddleware.php      ✅
│   │   │   └── OptionalAuthMiddleware.php ✅
│   │   └── Illuminate.php              ✅
│   ├── Infrastructure/
│   │   ├── Repositories/
│   │   │   ├── InMemoryDevRepository.php ✅
│   │   │   ├── InMemoryChannelRepository.php ✅
│   │   │   └── InMemoryVideoRepository.php ✅
│   │   ├── Authentication/
│   │   │   └── JWTAuth.php             ✅
│   │   └── SimpleRouter.php            ✅
│   ├── Helpers/
│   │   └── helpers.php                 ✅
│   └── ...
├── bootstrap/
│   └── app.php                         ✅
├── config/
│   ├── app.php                         ✅
│   └── auth.php                        ✅
├── routes/
│   └── api.php                         ✅
├── public/
│   └── index.php                       ✅
├── index.php                           ✅ (Main entry point)
├── composer.json                       ✅
├── .env.example                        ✅
├── .gitignore                          ✅
├── requests.http                       ✅
└── README.md                           ✅

Dockerfile                               ✅
docker-compose.yml                       ✅
agents/101-node-to-php.md               ✅
```

---

## 🎓 Aprendizados & Destaques para Portfolio

### DDD Implementado
- ✅ **Domain Layer**: Entities, Repositories (interfaces), Services
- ✅ **Infrastructure Layer**: In-Memory repositories, JWT Auth
- ✅ **Presentation Layer**: Controllers, Middleware, Routes

### Padrões Modernos PHP 8.2
- ✅ Type hints strict
- ✅ Constructor Property Promotion
- ✅ Match expressions
- ✅ Named arguments
- ✅ Union types
- ✅ Nullable types

### Boas Práticas
- ✅ SOLID principles (especialmente Dependency Injection)
- ✅ PSR-4 autoloading
- ✅ Separation of Concerns
- ✅ Clean Code
- ✅ Docker containerization

### Pontos de Destaque para Entrevista
1. **Arquitetura DDD**: Demonstra conhecimento em design patterns enterprise
2. **In-Memory Storage**: Padrão repository sem dependência de BD específico
3. **JWT Customizado**: Não usar pacotes prontos mostra competência
4. **Tipagem forte**: PHP 8.2 com type hints demonstra code quality
5. **Docker**: Containerização profissional
6. **SemFramework Completo**: Mostra compreensão de fundamentos vs frameworks

---

## 🔗 Comparação Node.js → PHP

| Aspecto | Node.js | PHP |
|---------|---------|-----|
| **Runtime** | Node 18.x | PHP 8.2+ |
| **Framework** | Express.js | Minimalista + DDD |
| **Persistência** | Mongoose + MongoDB | In-Memory Repository |
| **Autenticação** | passport-github + JWT | JWT Customizado |
| **Typing** | TypeScript | PHP 8.2 type hints |
| **Padrões** | Controllers + Services | DDD + Clean Architecture |
| **Containerização** | Dockerfile | Dockerfile + docker-compose |

---

## 📚 Referências

- PHP 8.2 Docs: https://www.php.net/docs.php
- JWT.io: https://jwt.io
- DDD Concepts: https://martinfowler.com/bliki/DomainDrivenDesign.html
- SOLID Principles: https://en.wikipedia.org/wiki/SOLID
- Node project original: `src-node-express/`

---

**Status:** ✅ **CONCLUÍDO**
**Data:** 2026-04-28
**Autor:** Marcelo Vilela
**Uso:** Portfolio de Entrevista / Proof of Concept
