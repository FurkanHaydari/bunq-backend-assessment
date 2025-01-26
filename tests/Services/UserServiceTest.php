<?php
declare(strict_types=1);

namespace Tests\Services;

use App\Services\UserService;
use Tests\TestCase;

class UserServiceTest extends TestCase
{
    private UserService $userService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userService = new UserService();
    }

    public function testCreateUser(): void
    {
        $user = $this->userService->createUser();

        $this->assertArrayHasKey('id', $user);
        $this->assertArrayHasKey('token', $user);
        $this->assertNotEmpty($user['token']);
    }

    public function testGetUserByValidToken(): void
    {
        // Create a user first
        $user = $this->userService->createUser();
        
        // Try to find the user by token
        $foundUser = $this->userService->findByToken($user['token']);
        
        $this->assertNotNull($foundUser);
        $this->assertEquals($user['id'], $foundUser['id']);
        $this->assertEquals($user['token'], $foundUser['token']);
    }

    public function testGetUserByInvalidToken(): void
    {
        $user = $this->userService->findByToken('invalid_token');
        $this->assertNull($user);
    }

    public function testGetUserByNullToken(): void
    {
        $user = $this->userService->findByToken('');
        $this->assertNull($user);
    }
}
