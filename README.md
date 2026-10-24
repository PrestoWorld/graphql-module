# GraphQL Module

## Overview
GraphQL module provides a provider-based schema registration system. Other modules can register providers to extend the schema with their own types, queries, and mutations.

## Architecture
- `Registry/SchemaRegistry`: Collects providers and merges schema definitions
- `Contracts/ProviderInterface`: Interface for schema providers
- `Http/GraphQLController`: Handles `/graphql` POST/GET requests
- `Providers/GraphQLServiceProvider`: Auto-registers configured providers
- `Module`: Registers services and routes

## Usage
Send POST to `/graphql`:
```json
{
  "query": "{ feed(limit: 10) { id content author { name } } }",
  "variables": {}
}
```

Response includes extensions.schema with registered types/queries/mutations.

## Extending
Create a provider in your module:
```php
<?php
namespace PrestoWorld\Modules\YourModule\GraphQL;

use PrestoWorld\Modules\GraphQL\Contracts\ProviderInterface;

class YourProvider implements ProviderInterface
{
    public function types(): array { return []; }
    public function queries(): array { return ['yourQuery' => [...]]; }
    public function mutations(): array { return []; }
}
```

Register in config:
```php
// config/graphql.php
'providers' => [
    \PrestoWorld\Modules\GraphQL\Providers\FeedProvider::class,
    \PrestoWorld\Modules\YourModule\GraphQL\YourProvider::class,
],
```

Or register dynamically:
```php
app(\PrestoWorld\Modules\GraphQL\Registry\SchemaRegistry::class)
    ->registerProvider(YourProvider::class);
```

## Module Declaration
Add to `modules/YourModule/module.json` or let discovery find it. Module must autoload classes (composer dump-autoload). For provider registration, either add to `config/graphql.php` or boot from module.

## Notes
Current implementation is minimal (schema introspection via extensions). For production, integrate with `webonyx/graphql-php` for full GraphQL execution.
