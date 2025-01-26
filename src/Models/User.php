<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

class User extends BaseModel
{
    public function create(string $token): array
    {
        $stmt = $this->db->prepare('
            INSERT INTO users (token)
            VALUES (:token)
            RETURNING id, token
        ');
        
        $stmt->execute([':token' => $token]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findByToken(string $token): ?array
    {
        $stmt = $this->db->prepare('
            SELECT id, token
            FROM users
            WHERE token = :token
        ');
        
        $stmt->execute([':token' => $token]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ?: null;
    }
}
