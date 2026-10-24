<?php

declare(strict_types=1);

namespace PrestoWorld\Modules\GraphQL\Http;

use PrestoWorld\Modules\GraphQL\Registry\SchemaRegistry;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Witals\Framework\Http\Response;

class GraphQLController
{
    public function __construct(private SchemaRegistry $registry) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $body = (string) $request->getBody();
        $data = json_decode($body, true) ?? [];
        $query = (string) ($data['query'] ?? '');
        $variables = is_array($data['variables'] ?? null) ? $data['variables'] : [];

        try {
            $schema = $this->registry->getSchema();
            $result = $this->execute($query, $variables, $schema);

            return Response::json($result);
        } catch (\Throwable $e) {
            return Response::json([
                'errors' => [['message' => $e->getMessage()]],
            ], 500);
        }
    }

    /**
     * @param array<string, mixed> $schema
     * @return array<string, mixed>
     */
    private function execute(string $query, array $variables, array $schema): array
    {
        $queries = $schema['queries'] ?? [];

        if (preg_match('/\bfeed\s*(\(([^()]*)\))?/is', $query, $m)) {
            $args = [];
            $argStr = $m[2] ?? '';
            if ($argStr !== '' && preg_match('/limit\s*:\s*(\d+)/i', $argStr, $lm)) {
                $args['limit'] = (int) $lm[1];
            }
            if (isset($variables['limit'])) {
                $args['limit'] = (int) $variables['limit'];
            }
            if (isset($queries['feed'])) {
                $resolver = $queries['feed']['resolve'] ?? null;
                if (is_callable($resolver)) {
                    $data = $resolver(null, $args);

                    return ['data' => ['feed' => $data]];
                }
            }
        }

        if (stripos($query, 'home') !== false && isset($queries['home'])) {
            $resolver = $queries['home']['resolve'] ?? null;
            if (is_callable($resolver)) {
                $data = $resolver(null, $variables);

                return ['data' => ['home' => $data]];
            }
        }

        return [
            'data' => null,
            'extensions' => [
                'schema' => [
                    'types' => array_keys($schema['types'] ?? []),
                    'queries' => array_keys($schema['queries'] ?? []),
                    'mutations' => array_keys($schema['mutations'] ?? []),
                ],
            ],
        ];
    }
}
