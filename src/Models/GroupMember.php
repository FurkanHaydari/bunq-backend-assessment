<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

class GroupMember extends BaseModel
{
    public function join(int $groupId, int $userId): array
    {
        $stmt = $this->db->prepare('
            INSERT INTO group_members (group_id, user_id)
            VALUES (:group_id, :user_id)
            RETURNING id, group_id, user_id, joined_at
        ');
        
        $stmt->execute([
            ':group_id' => $groupId,
            ':user_id' => $userId
        ]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function leave(int $groupId, int $userId): bool
    {
        $stmt = $this->db->prepare('
            DELETE FROM group_members
            WHERE group_id = :group_id AND user_id = :user_id
        ');
        
        return $stmt->execute([
            ':group_id' => $groupId,
            ':user_id' => $userId
        ]);
    }

    public function isMember(int $groupId, int $userId): bool
    {
        $stmt = $this->db->prepare('
            SELECT 1
            FROM group_members
            WHERE group_id = :group_id AND user_id = :user_id
        ');
        
        $stmt->execute([
            ':group_id' => $groupId,
            ':user_id' => $userId
        ]);
        
        return (bool)$stmt->fetch(PDO::FETCH_COLUMN);
    }

    public function getMembers(int $groupId): array
    {
        $stmt = $this->db->prepare('
            SELECT gm.*, u.token
            FROM group_members gm
            JOIN users u ON u.id = gm.user_id
            WHERE gm.group_id = :group_id
            ORDER BY gm.joined_at DESC
        ');
        
        $stmt->execute([':group_id' => $groupId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
