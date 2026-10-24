<?php

declare(strict_types=1);

namespace PrestoWorld\Modules\GraphQL\Contracts;

interface ProviderInterface
{
    public function types(): array;
    public function queries(): array;
    public function mutations(): array;
}
