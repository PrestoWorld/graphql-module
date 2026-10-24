<?php

declare(strict_types=1);

namespace PrestoWorld\Modules\GraphQL\Providers;

use PrestoWorld\Modules\GraphQL\Contracts\ProviderInterface;
use PrestoWorld\Modules\GraphQL\Registry\SchemaRegistry;
use Witals\Framework\Support\ServiceProvider;

class GraphQLServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SchemaRegistry::class, function () {
            return new SchemaRegistry();
        });

        $this->app->singleton(ProviderRegistry::class, function ($app) {
            return new ProviderRegistry($app->make(SchemaRegistry::class));
        });
    }

    public function boot(): void
    {
        $registry = $this->app->make(SchemaRegistry::class);

        foreach ((array) $this->app->config('graphql.providers', []) as $provider) {
            $registry->registerProvider($provider);
        }
    }
}
