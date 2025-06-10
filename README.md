# Beauty Core

The `beauty-framework/core` package is the foundation of the Beauty Framework — a PSR-compliant, high-performance runtime for building clean and maintainable REST and gRPC (todo) services. It brings together routing, middleware, dependency injection, event system, and configuration management into a modular and lightweight package optimized for use with RoadRunner.

## Features

* Clean architecture: Separation of concerns, modular components.
* PSR standards: PSR-7, PSR-11, PSR-14, PSR-15 compliance.
* Route registration via PHP Attributes.
* Simple and extendable DI container using `php-di`.
* Attribute-based and runtime middleware pipeline.
* Event dispatcher and listener registration (PSR-14 compatible).
* Lightweight config system with runtime registry.
* CLI kernel with support for custom commands.

---

## Installation

```bash
composer require beauty-framework/core
```

---

## Usage

### App Kernel

`Kernel\App` is the main entry point for your application. It boots the container, registers routes, middleware, events, and config, and starts request handling.

```php
use Beauty\Kernel\App;

require 'vendor/autoload.php';

$container = ContainerManager::bootFrom([
    \App\Container\Base::class,
    \App\Container\DI::class,
]);

$app = new App(
    container: $container,
);

$app->handle($request);
```

See in `workers/http-worker.php` in `beauty-framework/app` for a real-world example.

---

## Routing System

`beauty-framework/core` includes an attribute-driven router with support for method-based route definitions and route-specific middleware.

### Basic Example

```php
use Beauty\Core\Router\Route;

class UserController
{
    #[Route(method: \Beauty\Http\Enums\HttpMethodsEnum::GET, path: '/users')]
    public function index(HttpRequest $request): \Psr\Http\Message\ResponseInterface
    {
        return new JsonResponse(200, [
            'name' => $request->json('name'),
        ]);
    }
}
```

Generate via CLI:

```bash
./beauty generate:controller UserController
```

```bash
./beauty generate:request UserRequest
```

---

## Dependency Injection (Container)

The framework uses a custom `ContainerManager` that wraps `php-di` and provides:

* Singleton bindings
* Auto-wiring
* Runtime container access via `ContainerRegistry`
* `ContainerAwareInterface` for injecting container where needed

```php
use Beauty\Container\ContainerManager;

$container = ContainerManager::bootFrom([
    \App\Container\Base::class,
    \App\Container\DI::class,
]);

$service = $container->get(SomeService::class);
```

---

## Configuration

Configuration is loaded from PHP files in `config/*.php`, and accessed via `config` function:

```php
use Beauty\Config\ConfigRepository;

$config = config('app.debug');
```

At runtime, the `ConfigRegistry` provides in-memory updates or overrides.

---

## Event System

`beauty-framework/core` ships with a PSR-14-compatible event dispatcher. You can define events and listeners:

### Event

```php
class UserRegisteredEvent 
{
    public function __construct(
        public string $email
    ) {}
}
```

### Listener

```php
class SendWelcomeEmailListener 
{
    public function handle(UserRegisteredEvent $event): void 
    {
        // Send email
    }
}
```

### Registering

Use `ListenerRegistry`:

Or generate via CLI:

```bash
./beauty generate:event UserRegistered
./beauty generate:listener SendWelcomeEmail
```

---

## 🧵 Middleware

The router supports global and route-specific middleware using PSR-15-style interfaces.

```php
use Beauty\Http\Middleware\MiddlewareInterface;

class AuthMiddleware implements MiddlewareInterface 
{
    public function process(HttpRequest $request, RequestHandlerInterface $handler): ResponseInterface 
    {
        // Auth check
        return $handler->handle($request);
    }
}
```

You can also use attributes like `#[Middleware([AuthMiddleware::class])]` on controller classes or methods.

Generate with stubs:

```bash
./beauty generate:middleware Hello
```
---

## CLI Kernel

Define and register commands in `config/commands.php`, and they’ll be loaded automatically by the console kernel:

```php
class SomeCommand extends AbstractCommand 
{
    protected string $name = 'hello';

    public function handle(array $args): int 
    {
        $this->line('Hello, world!');
        
        return CLI::SUCCESS;
    }
}
```

Or generate with stubs:

```bash
./beauty generate:command Hello
```

---

## Testing

Coming soon: feature and unit tests for routing, DI, config, and event dispatching.

---

## PSR Compliance

| Standard | Status |
|----------|--------|
| PSR-1    | ✅      |
| PSR-4    | ✅      |
| PSR-7    | ✅      |
| PSR-11   | ✅      |
| PSR-15   | ✅      |
| PSR-14   | ✅      |

---

## Helpers

* `helpers/di.php` – short helpers to access DI bindings.
* `helpers/env.php` – `.env` parsing and usage functions.

---

## License

MIT

---

## Contributing

Pull requests and discussions are welcome! Let's make this the most developer-friendly framework for building modern PHP services.

---

## Related Modules

* [`beauty-framework/http`](https://github.com/beauty-framework/http): PSR-7 request/response wrappers
* [`beauty-framework/validation`](https://github.com/beauty-framework/validation): Laravel-like request validation
* [`beauty-framework/database`](https://github.com/beauty-framework/database): Lightweight query builder
* [`beauty-framework/cache`](https://github.com/beauty-framework/cache): PSR-6 cache adapter
* [`beauty-framework/jobs`](https://github.com/beauty-framework/jobs): Fiber-based job runner with RoadRunner support
* [`beauty-framework/cli`](https://github.com/beauty-framework/cli): Framework-aware CLI kernel
* [`beauty-framework/parallels`](https://github.com/beauty-framework/parallels): Parallel processing with Fibers (todo: RoadRunner)