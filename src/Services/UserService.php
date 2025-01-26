<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\User;

class UserService
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function createUser(): array
    {
        // Generate a secure random token
        $token = bin2hex(random_bytes(32));
        return $this->userModel->create($token);
    }

    public function findByToken(string $token): ?array
    {
        return $this->userModel->findByToken($token);
    }
}
