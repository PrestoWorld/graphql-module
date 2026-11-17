<?php

declare(strict_types=1);

namespace PrestoWorld\Modules\GraphQL\Http;

use PrestoWorld\Modules\GraphQL\Registry\SchemaRegistry;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response as Psr7Response;
use Witals\Framework\Http\Response as FrameworkResponse;

class GraphQLController
{
    public function __construct(private SchemaRegistry $registry) {}

    /**
     * @return FrameworkResponse|ResponseInterface
     */
    public function handle(mixed $request)
    {
        $isPsr7 = $request instanceof ServerRequestInterface;
        $body = $isPsr7
            ? (string) $request->getBody()
            : (string) (method_exists($request, 'body') ? $request->body() : '');
        $data = json_decode($body, true) ?? [];
        if ($data === [] && method_exists($request, 'getJson')) {
            $json = $request->getJson();
            if (is_array($json)) {
                $data = $json;
            }
        }
        if ($data === [] && !$isPsr7 && method_exists($request, 'post')) {
            $post = $request->post();
            if (is_array($post) && $post !== []) {
                $data = $post;
            }
        }

        $query = (string) ($data['query'] ?? '');
        $variables = is_array($data['variables'] ?? null) ? $data['variables'] : [];

        try {
            $schema = $this->registry->getSchema();
            $result = $this->execute($query, $variables, $schema);

            if ($isPsr7) {
                $factory = new Psr17Factory();
                return new Psr7Response(
                    200,
                    ['Content-Type' => 'application/json'],
                    $factory->createStream(json_encode($result, JSON_UNESCAPED_UNICODE))
                );
            }

            return FrameworkResponse::json($result, 200);
        } catch (\Throwable $e) {
            $error = ['errors' => [['message' => $e->getMessage()]]];

            if ($isPsr7) {
                $factory = new Psr17Factory();
                return new Psr7Response(
                    500,
                    ['Content-Type' => 'application/json'],
                    $factory->createStream(json_encode($error, JSON_UNESCAPED_UNICODE))
                );
            }

            return FrameworkResponse::json($error, 500);
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
            if ($argStr !== '' && preg_match('/offset\s*:\s*(\d+)/i', $argStr, $om)) {
                $args['offset'] = (int) $om[1];
            }
            if (isset($variables['limit'])) {
                $args['limit'] = (int) $variables['limit'];
            }
            if (isset($variables['offset'])) {
                $args['offset'] = (int) $variables['offset'];
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
