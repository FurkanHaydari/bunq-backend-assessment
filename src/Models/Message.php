<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

class Message extends BaseModel
{
    public function create(int $groupId, int $userId, string $message): array
    {
        $stmt = $this->db->prepare('
            INSERT INTO messages (group_id, user_id, message)
            VALUES (:group_id, :user_id, :message)
            RETURNING id, group_id, user_id, message, timestamp
        ');
        
        $stmt->execute([
            ':group_id' => $groupId,
            ':user_id' => $userId,
            ':message' => $message
        ]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByGroupId(int $groupId): array
    {
        $stmt = $this->db->prepare('
            SELECT m.*, u.token as user_token
            FROM messages m
            JOIN users u ON u.id = m.user_id
            WHERE m.group_id = :group_id
            ORDER BY m.timestamp DESC
        ');
        
        $stmt->execute([':group_id' => $groupId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
