<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

class Group extends BaseModel
{
    public function create(string $name): array
    {
        $stmt = $this->db->prepare('
            INSERT INTO groups (name)
            VALUES (:name)
            RETURNING id, name
        ');
        
        $stmt->execute([':name' => $name]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('
            SELECT id, name
            FROM groups
            WHERE id = :id
        ');
        
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ?: null;
    }

    public function getAll(): array
    {
        $stmt = $this->db->query('
            SELECT id, name
            FROM groups
            ORDER BY id DESC
        ');
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
