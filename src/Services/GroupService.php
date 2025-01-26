<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Group;
use App\Models\GroupMember;
use RuntimeException;

class GroupService
{
    private Group $groupModel;
    private GroupMember $groupMemberModel;

    public function __construct()
    {
        $this->groupModel = new Group();
        $this->groupMemberModel = new GroupMember();
    }

    public function createGroup(string $name, int $userId): array
    {
        if (empty(trim($name))) {
            throw new RuntimeException('Group name cannot be empty');
        }

        // Create the group
        $group = $this->groupModel->create($name);

        // Auto-join the creator to the group
        $this->groupMemberModel->join($group['id'], $userId);

        return $group;
    }

    public function joinGroup(int $groupId, int $userId): array
    {
        // Check if group exists
        $group = $this->groupModel->findById($groupId);
        if (!$group) {
            throw new RuntimeException('Group not found');
        }

        // Check if already a member
        if ($this->groupMemberModel->isMember($groupId, $userId)) {
            throw new RuntimeException('User is already a member of this group');
        }

        return $this->groupMemberModel->join($groupId, $userId);
    }

    public function leaveGroup(int $groupId, int $userId): bool
    {
        // Check if group exists
        $group = $this->groupModel->findById($groupId);
        if (!$group) {
            throw new RuntimeException('Group not found');
        }

        // Check if member
        if (!$this->groupMemberModel->isMember($groupId, $userId)) {
            throw new RuntimeException('User is not a member of this group');
        }

        return $this->groupMemberModel->leave($groupId, $userId);
    }

    public function getMembers(int $groupId): array
    {
        // Check if group exists
        $group = $this->groupModel->findById($groupId);
        if (!$group) {
            throw new RuntimeException('Group not found');
        }

        return $this->groupMemberModel->getMembers($groupId);
    }

    public function getAllGroups(): array
    {
        return $this->groupModel->getAll();
    }

    public function findById(int $groupId): ?array
    {
        return $this->groupModel->findById($groupId);
    }

    public function isMember(int $groupId, int $userId): bool
    {
        return $this->groupMemberModel->isMember($groupId, $userId);
    }
}
