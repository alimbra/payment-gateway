<?php

declare(strict_types=1);

namespace App\Service\DbManager;

use Exception;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;

class CacheAsDb implements DbManagerInterface
{
    public function __construct(
        protected CacheItemPoolInterface $cacheItemPool,
    ) {
    }

    /**
     * @throws Exception
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
