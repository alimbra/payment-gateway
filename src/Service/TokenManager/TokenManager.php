<?php

declare(strict_types=1);

namespace App\Service\TokenManager;

class TokenManager
{
    public function generateToken(string $data): string
    {
        return hash('sha256', $data.uniqid(more_entropy: true));
    }
}
