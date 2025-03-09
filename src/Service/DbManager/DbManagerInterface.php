<?php

declare(strict_types=1);

namespace App\Service\DbManager;

interface DbManagerInterface
{
    public function save(string $id, object $object): void;

    public function get(string $id): ?object;
}
