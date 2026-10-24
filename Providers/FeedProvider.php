<?php

declare(strict_types=1);

namespace PrestoWorld\Modules\GraphQL\Providers;

use App\Services\Frontend\FeedDataProvider;
use PrestoWorld\Modules\GraphQL\Contracts\ProviderInterface;

class FeedProvider implements ProviderInterface
{
    public function __construct(private ?FeedDataProvider $feed = null) {}

    public function types(): array
    {
        return [
            'Post' => [
                'fields' => [
                    'id' => ['type' => 'ID'],
                    'type' => ['type' => 'String'],
                    'content' => ['type' => 'String'],
                    'rawContent' => ['type' => 'String'],
                    'author' => ['type' => 'Author'],
                    'stats' => ['type' => 'Stats'],
                    'bookCard' => ['type' => 'BookCard'],
                    'timeAgo' => ['type' => 'String'],
                    'images' => ['type' => '[String]'],
                    'media_url' => ['type' => 'String'],
                    'embed_url' => ['type' => 'String'],
                    'thumbnail_url' => ['type' => 'String'],
                    'payload' => ['type' => 'JSON'],
                ],
            ],
            'Author' => [
                'fields' => [
                    'name' => ['type' => 'String'],
                    'avatar' => ['type' => 'String'],
                    'verified' => ['type' => 'Boolean'],
                    'badge' => ['type' => 'String'],
                ],
            ],
            'Stats' => [
                'fields' => [
                    'likes' => ['type' => 'Int'],
                    'commentsCount' => ['type' => 'Int'],
                    'shares' => ['type' => 'Int'],
                    'isLiked' => ['type' => 'Boolean'],
                    'isSaved' => ['type' => 'Boolean'],
                ],
            ],
            'BookCard' => [
                'fields' => [
                    'title' => ['type' => 'String'],
                    'author' => ['type' => 'String'],
                    'price' => ['type' => 'Int'],
                    'image' => ['type' => 'String'],
                ],
            ],
            'Home' => [
                'fields' => [
                    'greeting' => ['type' => 'String'],
                    'posts' => ['type' => '[Post]'],
                ],
            ],
            'JSON' => ['scalar' => true],
        ];
    }

    public function queries(): array
    {
        $feed = $this->feed ?? (function_exists('app') ? app(FeedDataProvider::class) : null);

        return [
            'feed' => [
                'type' => '[Post]',
                'args' => ['limit' => ['type' => 'Int', 'default' => 10]],
                'resolve' => function ($root, $args) use ($feed) {
                    $limit = (int) ($args['limit'] ?? 10);
                    if ($feed instanceof FeedDataProvider) {
                        return $feed->feed($limit);
                    }
                    return [];
                },
            ],
            'home' => [
                'type' => 'Home',
                'resolve' => function () use ($feed) {
                    if ($feed instanceof FeedDataProvider) {
                        return $feed->home();
                    }
                    return ['greeting' => '', 'posts' => []];
                },
            ],
        ];
    }

    public function mutations(): array
    {
        return [];
    }
}
