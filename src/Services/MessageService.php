<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Message;
use App\Models\GroupMember;
use RuntimeException;

class MessageService
{
    private Message $messageModel;
    private GroupMember $groupMemberModel;
    private GroupService $groupService;

    public function __construct()
    {
        $this->messageModel = new Message();
        $this->groupMemberModel = new GroupMember();
        $this->groupService = new GroupService();
    }

    public function sendMessage(int $groupId, int $userId, string $message): array
    {
        // Validate message
        if (empty(trim($message))) {
            throw new RuntimeException('Message cannot be empty');
        }

        // Check if group exists
        $group = $this->groupService->findById($groupId);
        if (!$group) {
            throw new RuntimeException('Group not found');
        }

        // Check if user is member of the group
        if (!$this->groupMemberModel->isMember($groupId, $userId)) {
            throw new RuntimeException('You must be a member of the group to send messages');
        }

        return $this->messageModel->create($groupId, $userId, $message);
    }

    public function getGroupMessages(int $groupId, int $userId): array
    {
        // Check if group exists
        $group = $this->groupService->findById($groupId);
        if (!$group) {
            throw new RuntimeException('Group not found');
        }

        // Check if user is member of the group
        if (!$this->groupMemberModel->isMember($groupId, $userId)) {
            throw new RuntimeException('You must be a member of the group to view messages');
        }

        return $this->messageModel->getByGroupId($groupId);
    }
}
