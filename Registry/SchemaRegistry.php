<?php

declare(strict_types=1);

namespace PrestoWorld\Modules\GraphQL\Registry;

use PrestoWorld\Modules\GraphQL\Contracts\ProviderInterface;

class SchemaRegistry
{
    /** @var ProviderInterface[] */
    private array $providers = [];

    private ?array $cachedSchema = null;

    public function registerProvider(ProviderInterface|string $provider): void
    {
        if (is_string($provider) && class_exists($provider)) {
            $provider = app()->make($provider);
        }
        if ($provider instanceof ProviderInterface) {
            $this->providers[] = $provider;
            $this->cachedSchema = null;
        }
    }

    public function getProviders(): array
    {
        return $this->providers;
    }

    public function getSchema(): array
    {
        if ($this->cachedSchema !== null) {
            return $this->cachedSchema;
        }

        $types = [];
        $queries = [];
        $mutations = [];

        foreach ($this->providers as $provider) {
            $types = array_merge($types, $provider->types());
            $queries = array_merge($queries, $provider->queries());
            $mutations = array_merge($mutations, $provider->mutations());
        }

        $this->cachedSchema = [
            'types' => $types,
            'queries' => $queries,
            'mutations' => $mutations,
        ];

        return $this->cachedSchema;
    }

    public function invalidateSchema(): void
    {
        $this->cachedSchema = null;
    }
}
