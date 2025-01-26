<?php
declare(strict_types=1);

namespace Tests\Services;

use App\Services\GroupService;
use App\Services\UserService;
use RuntimeException;
use Tests\TestCase;

class GroupServiceTest extends TestCase
{
    private GroupService $groupService;
    private int $userId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->groupService = new GroupService();
        $userService = new UserService();
        $user = $userService->createUser();
        $this->userId = $user['id'];
    }

    public function testCreateGroupSuccess(): void
    {
        $group = $this->groupService->createGroup('Test Group', $this->userId);

        $this->assertArrayHasKey('id', $group);
        $this->assertArrayHasKey('name', $group);
        $this->assertEquals('Test Group', $group['name']);
    }

    public function testCreateGroupWithEmptyName(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Group name cannot be empty');
        
        $this->groupService->createGroup('', $this->userId);
    }

    public function testJoinGroupSuccess(): void
    {
        // Create a group first
        $group = $this->groupService->createGroup('Test Group', $this->userId);
        
        // Create another user
        $userService = new UserService();
        $user2 = $userService->createUser();
        
        // User 2 joins the group
        $membership = $this->groupService->joinGroup($group['id'], $user2['id']);
        
        $this->assertArrayHasKey('id', $membership);
        $this->assertEquals($group['id'], $membership['group_id']);
        $this->assertEquals($user2['id'], $membership['user_id']);
    }

    public function testJoinNonExistentGroup(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Group not found');
        
        $this->groupService->joinGroup(99999, $this->userId);
    }

    public function testJoinGroupTwice(): void
    {
        // Create a group
        $group = $this->groupService->createGroup('Test Group', $this->userId);
        
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('User is already a member of this group');
        
        // Try to join again (user is already a member from createGroup)
        $this->groupService->joinGroup($group['id'], $this->userId);
    }

    public function testLeaveGroupSuccess(): void
    {
        // Create a group
        $group = $this->groupService->createGroup('Test Group', $this->userId);
        
        // Leave the group
        $result = $this->groupService->leaveGroup($group['id'], $this->userId);
        $this->assertTrue($result);
    }

    public function testLeaveNonExistentGroup(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Group not found');
        
        $this->groupService->leaveGroup(99999, $this->userId);
    }

    public function testLeaveGroupNotMember(): void
    {
        // Create a group
        $group = $this->groupService->createGroup('Test Group', $this->userId);
        
        // Create another user
        $userService = new UserService();
        $user2 = $userService->createUser();
        
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('User is not a member of this group');
        
        // Try to leave without being a member
        $this->groupService->leaveGroup($group['id'], $user2['id']);
    }
}
