# Architecture Documentation - DevFinder PHP

## System Design Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                      HTTP REQUEST                               │
└────────────────┬────────────────────────────────────────────────┘
                 │
                 ▼
        ┌─────────────────┐
        │  SimpleRouter   │  (Entry point - index.php)
        │  (url matching) │
        └────────┬────────┘
                 │
        ┌────────▼─────────┐
        │  Middleware      │
        │  (Auth checks)   │
        └────────┬─────────┘
                 │
        ┌────────▼──────────────┐
        │  HTTP Controller      │  ◄─── Presentation Layer
        │  (DevController, etc) │
        └────────┬──────────────┘
                 │
        ┌────────▼──────────────┐
        │  Domain Service       │  ◄─── Domain Layer (Business Logic)
        │  (DevService, etc)    │
        └────────┬──────────────┘
                 │
        ┌────────▼──────────────┐
        │  Repository Impl      │  ◄─── Infrastructure Layer
        │  (InMemoryDev, etc)   │
        └────────┬──────────────┘
                 │
        ┌────────▼──────────────┐
        │  In-Memory Storage    │
        │  (PHP static arrays)  │
        └───────────────────────┘
```

## Layer Responsibilities

### 1. Presentation Layer (HTTP)
**Location:** `app/Http/Controllers/`, `app/Http/Middleware/`

**Responsibilities:**
- Handle HTTP requests/responses
- Deserialize request data
- Serialize response data
- Call appropriate services

**Example:** `DevController::store()`
```php
public function store(Request $request) {
    // 1. Validate auth
    // 2. Extract request data
    // 3. Call service
    // 4. Return JSON response
}
```

### 2. Domain Layer (Business Logic)
**Location:** `app/Core/*/`

**Responsibilities:**
- Implement business rules
- Coordinate between entities and repositories
- Enforce domain constraints
- Independent of infrastructure

**Components:**
- **Entities**: `DevEntity`, `ChannelEntity`, `VideoEntity`
  - Represent domain objects
  - Encapsulate business logic
  - Immutable data + methods

- **Repositories (Interfaces)**: `DevRepository`, `ChannelRepository`, `VideoRepository`
  - Define contract for data access
  - Domain shouldn't know HOW data is stored
  - Enable polymorphism (swap implementations)

- **Services**: `DevService`, `ChannelService`, `VideoService`
  - Orchestrate business workflows
  - Use repositories for data access
  - Validate business rules

### 3. Infrastructure Layer
**Location:** `app/Infrastructure/`

**Responsibilities:**
- Implement repository interfaces
- Handle external services (JWT, APIs)
- Manage technical concerns
- Isolated from domain logic

**Components:**
- **Repository Implementations**: `InMemoryDevRepository`, etc.
  - Store/retrieve entities
  - Handle pagination
  - Implement domain repository contracts

- **Authentication**: `JWTAuth`
  - Generate/verify tokens
  - Handle security concerns

- **Router**: `SimpleRouter`
  - Route HTTP requests to controllers

## Data Flow Example: Create Dev

```
1. HTTP POST /v1/devs
   │
   ├─ SimpleRouter matches route
   │
   ├─ AuthMiddleware validates token
   │
   ├─ DevController::store(Request)
   │  │
   │  ├─ Extract request data
   │  │
   │  └─ Call DevService::create()
   │     │
   │     ├─ Validate business rules
   │     │  (e.g., username unique)
   │     │
   │     ├─ Create DevEntity
   │     │
   │     ├─ Call DevRepository::create()
   │     │  │
   │     │  └─ InMemoryDevRepository
   │     │     │
   │     │     ├─ Generate ID
   │     │     │
   │     │     ├─ Store in static $devs[]
   │     │     │
   │     │     └─ Return DevEntity
   │     │
   │     └─ Generate JWT token
   │
   └─ DevController returns JSON response
      {
        "dev": {...},
        "token": "eyJhbGc..."
      }
```

## Repository Pattern Benefits

### Problem Without Repository:
```php
// Direct DB access scattered everywhere
class DevService {
    public function create() {
        // Tight coupling to MongoDB
        Dev.create(...);
        Dev.save(...);
    }
}
```

### Solution With Repository:
```php
// Abstract data access
interface DevRepository {
    public function create(DevEntity $dev): DevEntity;
}

class DevService {
    public function __construct(DevRepository $repo) {
        // Dependency injection - can swap implementations
        $this->repo = $repo;
    }
}

// Can swap implementations
$service = new DevService(new InMemoryDevRepository());
$service = new DevService(new DatabaseDevRepository());
$service = new DevService(new ElasticsearchDevRepository());
```

## In-Memory Storage Pattern

### How It Works:
```php
class InMemoryDevRepository implements DevRepository {
    private static array $devs = [];           // Static = class-level, shared across requests
    private static int $nextId = 1;            // Auto-increment

    public function create(DevEntity $dev): DevEntity {
        $id = (string) self::$nextId++;        // Generate ID
        $dev->setId($id);
        self::$devs[$id] = $dev;               // Store in static array
        return $dev;
    }

    public function findById(string $id): ?DevEntity {
        return self::$devs[$id] ?? null;       // Retrieve from static array
    }
}
```

### Trade-offs:
✅ **Pros:**
- No DB setup needed
- Fast for development
- Shows repository abstraction
- Easy to understand (no SQL complexity)

❌ **Cons:**
- Data lost on restart
- Not persistent
- Single process (no multi-server)
- Not production-ready

## Dependency Injection Pattern

### How Controllers Get Services:
```php
class DevController {
    public function __construct(
        private DevService $service,
        private JWTAuth $jwt
    ) {}
    // Services are injected automatically by SimpleRouter
}
```

### How Services Get Repositories:
```php
class DevService {
    public function __construct(
        private DevRepository $repository
    ) {}
    // Repository is injected - could be any implementation
}
```

### SimpleRouter Resolution:
```php
private function resolveDependencies(string $class): array {
    // Reflect on constructor parameters
    // Instantiate each dependency
    // Return array of instances
}

// In real Laravel: Service Container handles this
```

## Entity Design Pattern

### Entity vs Value Object:
```php
class DevEntity {
    private ?string $id = null;        // Identity
    private string $username;          // Core attributes
    private array $likes = [];         // Collections
    private DateTimeImmutable $createdAt;

    // Methods enforce business rules
    public function addLike(string $devId): void {
        if (!in_array($devId, $this->likes)) {
            $this->likes[] = $devId;  // Duplicate prevention
            $this->updatedAt = new DateTimeImmutable();
        }
    }
}
```

### Key Characteristics:
1. **Identity**: Has unique ID
2. **Mutable**: Can change state
3. **Lifecycle**: Created, modified, deleted
4. **Business Logic**: Methods for domain operations
5. **Validation**: Prevents invalid states

## Authentication Flow

### JWT Token Generation:
```
1. Client: POST /v1/devs {username: "marcelo"}
   │
2. Server: Create DevEntity
   │
3. Server: Generate JWT
   {
     "iat": 1234567890,
     "exp": 1234567890 + 604800,
     "id": "1",
     "username": "marcelo"
   }
   │
4. Server: Sign with secret key
   │
5. Client: Get token in response
```

### JWT Token Validation:
```
1. Client: POST /v1/devs/:username/like
   Header: Authorization: Bearer eyJhbGc...
   │
2. AuthMiddleware:
   │
   ├─ Extract token from header
   │
   ├─ Call JWTAuth::verifyToken()
   │  │
   │  ├─ Verify signature (using secret key)
   │  │
   │  ├─ Check expiration
   │  │
   │  └─ Return decoded payload
   │
   ├─ Store decoded user in request
   │
   └─ Proceed to controller
```

## Error Handling

### Exception Types:
```php
// Domain exceptions
throw new \DomainException("Dev not found");      // 404 Not Found

// Business logic violations
throw new \DomainException("Dev already exists"); // 409 Conflict

// Invalid input
throw new \InvalidArgumentException("...");       // 400 Bad Request

// Unauthorized
return response()->json(['error' => 'Unauthorized'], 401); // 401
```

### Controller Catches:
```php
try {
    return $this->service->create(...);
} catch (\DomainException $e) {
    return response()->json(['error' => $e->getMessage()], 404);
} catch (\Exception $e) {
    return response()->json(['error' => $e->getMessage()], 400);
}
```

## Testing Strategy

### Unit Tests (Domain Layer):
```php
// Test DevService in isolation
$service = new DevService(new InMemoryDevRepository());
$dev = $service->create("username", "name", "avatar");
$this->assertEquals("username", $dev->getUsername());
```

### Integration Tests (HTTP Layer):
```php
// Test full request/response cycle
$response = $this->post('/v1/devs', [
    'username' => 'test',
    'name' => 'Test User'
]);
$this->assertEquals(201, $response->status());
```

## Performance Considerations

### Current (In-Memory):
- ✅ O(1) lookup by ID: `$devs[$id]`
- ⚠️ O(n) lookup by username: iterate all devs
- ⚠️ O(n log n) pagination: sort + slice

### With Database:
- Could add indexes for faster lookups
- Connection pooling for concurrent requests
- Caching layer for frequently accessed data

### Scaling:
- Add Redis cache layer
- Switch to distributed storage
- Implement read replicas

## Design Patterns Used

1. **Repository Pattern** - Abstract data access
2. **Dependency Injection** - Loose coupling
3. **Service Locator** - SimpleRouter resolves dependencies
4. **Entity Pattern** - Domain objects
5. **Middleware Pattern** - Cross-cutting concerns (auth)
6. **Factory Pattern** - Repository implementations
7. **Singleton Pattern** - In-memory storage (static)

## SOLID Principles Applied

✅ **S**ingle Responsibility:
- Each class has one reason to change
- DevService focuses on dev business logic
- InMemoryDevRepository focuses on storage

✅ **O**pen/Closed:
- Open for extension (new repository impl)
- Closed for modification (Repository interface)

✅ **L**iskov Substitution:
- Any repository can replace another
- Behavior remains consistent

✅ **I**nterface Segregation:
- Focused interfaces (DevRepository, ChannelRepository)
- Not fat, monolithic interfaces

✅ **D**ependency Inversion:
- Depend on abstractions (DevRepository)
- Not concrete implementations

---

## Quick Reference

| Layer | Location | Concern |
|-------|----------|---------|
| **Presentation** | `app/Http/Controllers/` | HTTP handling |
| **Domain** | `app/Core/*/` | Business logic |
| **Infrastructure** | `app/Infrastructure/` | Technical details |

| Component | Purpose |
|-----------|---------|
| **Entity** | Domain object with ID |
| **Repository** | Data access abstraction |
| **Service** | Business logic orchestration |
| **Controller** | HTTP request handler |
| **Middleware** | Cross-cutting concern |

---

**Last Updated:** 2026-04-28
**For:** Portfolio / Interview
