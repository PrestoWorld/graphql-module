<?php

declare(strict_types=1);

namespace PrestoWorld\Modules\GraphQL\Facades;

use PrestoWorld\Modules\GraphQL\Contracts\ProviderInterface;
use PrestoWorld\Modules\GraphQL\Providers\ProviderRegistry;

/**
 * @see \PrestoWorld\Modules\GraphQL\Providers\ProviderRegistry
 *
 * @method static void registerProvider(ProviderInterface|string $provider)
 * @method static array schema()
 */
class GraphQL
{
    /**
     * Dynamically pass static calls to the ProviderRegistry instance.
     *
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public static function __callStatic(string $method, array $args)
    {
        return app(ProviderRegistry::class)->{$method}(...$args);
    }
}
