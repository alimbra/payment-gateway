<?php

declare(strict_types=1);

namespace App\Service\DbManager;

use Psr\Cache\CacheItemPoolInterface;

readonly class CacheAsDb implements DbManagerInterface
{
    public function __construct(
        private CacheItemPoolInterface $cacheItemPool,
    ) {
    }

    /**
     * @throws \Exception
     */
    public function save(string $id, object $object): void
    {
        $item = $this->cacheItemPool->getItem($id);
        $item->set($object);
        $this->cacheItemPool->save($item);
    }

    public function get(string $id): ?object
    {
        $item = $this->cacheItemPool->getItem($id);

        return is_object($item->get()) ? $item->get() : null;
    }
}
