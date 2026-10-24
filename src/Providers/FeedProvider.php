<?php

declare(strict_types=1);

namespace PrestoWorld\Modules\GraphQL\Providers;

use App\Services\Frontend\FeedDataProvider;
use PrestoWorld\Modules\GraphQL\Contracts\ProviderInterface;

class FeedProvider implements ProviderInterface
{
    public function __construct(private FeedDataProvider $feed) {}

    public function types(): array
    {
        return [
            'Post' => [
                'fields' => [
                    'id' => ['type' => 'ID'],
                    'content' => ['type' => 'String'],
                    'author' => ['type' => 'Author'],
                    'stats' => ['type' => 'Stats'],
                    'bookCard' => ['type' => 'BookCard'],
                    'timeAgo' => ['type' => 'String'],
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
        ];
    }

    public function queries(): array
    {
        return [
            'feed' => [
                'type' => '[Post]',
                'args' => ['limit' => ['type' => 'Int', 'default' => 10]],
                'resolve' => function ($root, $args) {
                    $limit = (int) ($args['limit'] ?? 10);
                    return $this->feed->feed($limit);
                },
            ],
            'home' => [
                'type' => 'Home',
                'resolve' => function () {
                    return $this->feed->home();
                },
            ],
        ];
    }

    public function mutations(): array
    {
        return [];
    }
}
