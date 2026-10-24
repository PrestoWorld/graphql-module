<?php

declare(strict_types=1);

namespace PrestoWorld\Modules\GraphQL\Providers;

use PrestoWorld\Modules\GraphQL\Contracts\ProviderInterface;
use PrestoWorld\Modules\GraphQL\Registry\SchemaRegistry;

/**
 * Central registry for GraphQL providers.
 *
 * Other modules register their GraphQL types/queries/mutations here:
 *
 *     ProviderRegistry::register(new MyModuleGraphQLProvider());
 *
 * Providers registered here are merged into the GraphQL schema by SchemaRegistry.
 */
class ProviderRegistry
{
    private static ?self $instance = null;

    /** @var list<ProviderInterface> */
    private array $providers = [];

    /** @var SchemaRegistry|null */
    private ?SchemaRegistry $schema = null;

    private function __construct(
        SchemaRegistry $schema
    ) {
        $this->schema = $schema;
    }

    public static function instance(SchemaRegistry $schema): self
    {
        if (self::$instance === null) {
            self::$instance = new self($schema);
        }

        return self::$instance;
    }

    public static function register(ProviderInterface|string $provider): void
    {
        $schema = app(SchemaRegistry::class);
        $schema->registerProvider($provider);
    }

    public function registerProvider(ProviderInterface|string $provider): void
    {
        if (is_string($provider) && class_exists($provider)) {
            $provider = app()->make($provider);
        }
        if ($provider instanceof ProviderInterface) {
            $this->providers[] = $provider;
            ($this->schema ?? app(SchemaRegistry::class))->registerProvider($provider);
        }
    }

    public function providers(): array
    {
        return $this->providers;
    }

    public function schema(): array
    {
        return ($this->schema ?? app(SchemaRegistry::class))->getSchema();
    }
}
