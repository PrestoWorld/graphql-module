<?php

declare(strict_types=1);

namespace PrestoWorld\Modules\GraphQL;

use PrestoWorld\Modules\GraphQL\Contracts\ProviderInterface;
use PrestoWorld\Modules\GraphQL\Providers\ProviderRegistry;
use PrestoWorld\Modules\GraphQL\Registry\SchemaRegistry;
use Witals\Framework\Application;
use Witals\Framework\Module\Module as WitalsModule;

class Module extends WitalsModule
{
    public function __construct(
        protected Application $app,
        protected string $path = '',
        protected array $metadata = [],
    ) {
        if ($path === '') {
            $path = __DIR__;
        }
        if ($metadata === []) {
            $metadata = ['name' => 'graphql'];
        }
        parent::__construct($app, $path, $metadata);
    }

    public function register(): void
    {
        parent::register();
    }

    public function boot(): void
    {
        //
    }

    public function getName(): string { return 'GraphQL'; }

    /**
     * Register a GraphQL provider from another module.
     *
     *     GraphQL::registerProvider(new BookCatalogGraphQLProvider());
     */
    public static function registerProvider(ProviderInterface|string $provider): void
    {
        app(ProviderRegistry::class)->registerProvider($provider);
    }

    /**
     * Return the merged GraphQL schema.
     */
    public static function schema(): array
    {
        return app(ProviderRegistry::class)->schema();
    }

    public function registry(): SchemaRegistry
    {
        return $this->app->make(SchemaRegistry::class);
    }
}
