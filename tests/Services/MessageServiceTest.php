<?php
declare(strict_types=1);

namespace Tests\Services;

use App\Services\GroupService;
use App\Services\MessageService;
use App\Services\UserService;
use RuntimeException;
use Tests\TestCase;

class MessageServiceTest extends TestCase
{
    private MessageService $messageService;
    private int $userId;
    private array $group;

    protected function setUp(): void
    {
        parent::setUp();
        $this->messageService = new MessageService();
        
        // Create a user and group for testing
        $userService = new UserService();
        $user = $userService->createUser();
        $this->userId = $user['id'];
        
        $groupService = new GroupService();
        $this->group = $groupService->createGroup('Test Group', $this->userId);
    }

    public function testSendMessageSuccess(): void
    {
        $message = $this->messageService->sendMessage(
            $this->group['id'],
            $this->userId,
            'Test message'
        );

        $this->assertArrayHasKey('id', $message);
        $this->assertEquals($this->group['id'], $message['group_id']);
        $this->assertEquals($this->userId, $message['user_id']);
        $this->assertEquals('Test message', $message['message']);
    }

    public function testSendEmptyMessage(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Message cannot be empty');
        
        $this->messageService->sendMessage($this->group['id'], $this->userId, '');
    }

    public function testSendMessageToNonExistentGroup(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Group not found');
        
        $this->messageService->sendMessage(99999, $this->userId, 'Test message');
    }

    public function testSendMessageAsNonMember(): void
    {
        // Create another user that's not a member
        $userService = new UserService();
        $nonMember = $userService->createUser();
        
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('You must be a member of the group to send messages');
        
        $this->messageService->sendMessage(
            $this->group['id'],
            $nonMember['id'],
            'Test message'
        );
    }

    public function testGetGroupMessages(): void
    {
        // Send a test message first
        $this->messageService->sendMessage(
            $this->group['id'],
            $this->userId,
            'Test message'
        );
        
        // Get messages
        $messages = $this->messageService->getGroupMessages($this->group['id'], $this->userId);
        
        $this->assertIsArray($messages);
        $this->assertCount(1, $messages);
        $this->assertEquals('Test message', $messages[0]['message']);
    }

    public function testGetMessagesFromNonExistentGroup(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Group not found');
        
        $this->messageService->getGroupMessages(99999, $this->userId);
    }

    public function testGetMessagesAsNonMember(): void
    {
        // Create another user that's not a member
        $userService = new UserService();
        $nonMember = $userService->createUser();
        
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('You must be a member of the group to view messages');
        
        $this->messageService->getGroupMessages($this->group['id'], $nonMember['id']);
    }
}
