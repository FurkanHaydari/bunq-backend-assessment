<?php
declare(strict_types=1);

namespace Tests\Integration;

use App\Services\UserService;
use Tests\TestCase;

class ApiTest extends TestCase
{
    private string $userToken;
    private array $user;
    private array $group;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a test user
        $userService = new UserService();
        $this->user = $userService->createUser();
        $this->userToken = $this->user['token'];

        // Create a test group
        $request = $this->createRequest(
            'POST',
            '/api/groups',
            ['name' => 'Test Group'],
            ['X-User-Token' => $this->userToken]
        );
        $response = $this->app->handle($request);
        $this->group = json_decode((string)$response->getBody(), true);
    }

    public function testHomeEndpoint(): void
    {
        $request = $this->createRequest('GET', '/');
        $response = $this->app->handle($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        
        $data = json_decode((string)$response->getBody(), true);
        $this->assertArrayHasKey('status', $data);
        $this->assertEquals('running', $data['status']);
    }

    public function testCreateUserEndpoint(): void
    {
        $request = $this->createRequest('POST', '/users');
        $response = $this->app->handle($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        
        $data = json_decode((string)$response->getBody(), true);
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('token', $data);
        $this->assertNotEmpty($data['token']);
    }

    public function testGroupSecurityScenarios(): void
    {
        // Try to create group without token
        $request = $this->createRequest('POST', '/api/groups', ['name' => 'Test Group']);
        $response = $this->app->handle($request);
        $this->assertEquals(401, $response->getStatusCode());

        // Try to create group with invalid token
        $request = $this->createRequest(
            'POST',
            '/api/groups',
            ['name' => 'Test Group'],
            ['X-User-Token' => 'invalid_token']
        );
        $response = $this->app->handle($request);
        $this->assertEquals(401, $response->getStatusCode());

        // Try to join group without token
        $request = $this->createRequest('POST', "/api/groups/{$this->group['id']}/join");
        $response = $this->app->handle($request);
        $this->assertEquals(401, $response->getStatusCode());

        // Try to join group with invalid token
        $request = $this->createRequest(
            'POST',
            "/api/groups/{$this->group['id']}/join",
            [],
            ['X-User-Token' => 'invalid_token']
        );
        $response = $this->app->handle($request);
        $this->assertEquals(401, $response->getStatusCode());

        // Try to join non-existent group
        $request = $this->createRequest(
            'POST',
            '/api/groups/99999/join',
            [],
            ['X-User-Token' => $this->userToken]
        );
        $response = $this->app->handle($request);
        $this->assertEquals(404, $response->getStatusCode());

        // Try to list members without token
        $request = $this->createRequest('GET', "/api/groups/{$this->group['id']}/members");
        $response = $this->app->handle($request);
        $this->assertEquals(401, $response->getStatusCode());

        // Try to list members with invalid token
        $request = $this->createRequest(
            'GET',
            "/api/groups/{$this->group['id']}/members",
            [],
            ['X-User-Token' => 'invalid_token']
        );
        $response = $this->app->handle($request);
        $this->assertEquals(401, $response->getStatusCode());

        // Try to list members of non-existent group
        $request = $this->createRequest(
            'GET',
            '/api/groups/99999/members',
            [],
            ['X-User-Token' => $this->userToken]
        );
        $response = $this->app->handle($request);
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testMessageSecurityScenarios(): void
    {
        // Create another user that's not a member
        $request = $this->createRequest('POST', '/users');
        $response = $this->app->handle($request);
        $nonMember = json_decode((string)$response->getBody(), true);

        // Try to send message without token
        $request = $this->createRequest(
            'POST',
            "/api/groups/{$this->group['id']}/messages",
            ['message' => 'Test message']
        );
        $response = $this->app->handle($request);
        $this->assertEquals(401, $response->getStatusCode());

        // Try to send message with invalid token
        $request = $this->createRequest(
            'POST',
            "/api/groups/{$this->group['id']}/messages",
            ['message' => 'Test message'],
            ['X-User-Token' => 'invalid_token']
        );
        $response = $this->app->handle($request);
        $this->assertEquals(401, $response->getStatusCode());

        // Try to send message to non-existent group
        $request = $this->createRequest(
            'POST',
            '/api/groups/99999/messages',
            ['message' => 'Test message'],
            ['X-User-Token' => $this->userToken]
        );
        $response = $this->app->handle($request);
        $this->assertEquals(404, $response->getStatusCode());

        // Try to send message as non-member
        $request = $this->createRequest(
            'POST',
            "/api/groups/{$this->group['id']}/messages",
            ['message' => 'Test message'],
            ['X-User-Token' => $nonMember['token']]
        );
        $response = $this->app->handle($request);
        $this->assertEquals(403, $response->getStatusCode());

        // Try to list messages without token
        $request = $this->createRequest('GET', "/api/groups/{$this->group['id']}/messages");
        $response = $this->app->handle($request);
        $this->assertEquals(401, $response->getStatusCode());

        // Try to list messages with invalid token
        $request = $this->createRequest(
            'GET',
            "/api/groups/{$this->group['id']}/messages",
            [],
            ['X-User-Token' => 'invalid_token']
        );
        $response = $this->app->handle($request);
        $this->assertEquals(401, $response->getStatusCode());

        // Try to list messages as non-member
        $request = $this->createRequest(
            'GET',
            "/api/groups/{$this->group['id']}/messages",
            [],
            ['X-User-Token' => $nonMember['token']]
        );
        $response = $this->app->handle($request);
        $this->assertEquals(403, $response->getStatusCode());

        // Try to list messages from non-existent group
        $request = $this->createRequest(
            'GET',
            '/api/groups/99999/messages',
            [],
            ['X-User-Token' => $this->userToken]
        );
        $response = $this->app->handle($request);
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testCompleteGroupFlow(): void
    {
        // Create another user
        $request = $this->createRequest('POST', '/users');
        $response = $this->app->handle($request);
        $user2 = json_decode((string)$response->getBody(), true);

        // User 2 joins the group
        $request = $this->createRequest(
            'POST',
            "/api/groups/{$this->group['id']}/join",
            [],
            ['X-User-Token' => $user2['token']]
        );
        $response = $this->app->handle($request);
        $this->assertEquals(200, $response->getStatusCode());

        // List group members
        $request = $this->createRequest(
            'GET',
            "/api/groups/{$this->group['id']}/members",
            [],
            ['X-User-Token' => $this->userToken]
        );
        $response = $this->app->handle($request);
        $this->assertEquals(200, $response->getStatusCode());
        
        $members = json_decode((string)$response->getBody(), true);
        $this->assertCount(2, $members);
    }

    public function testCompleteMessageFlow(): void
    {
        // Create another user
        $request = $this->createRequest('POST', '/users');
        $response = $this->app->handle($request);
        $user2 = json_decode((string)$response->getBody(), true);

        // User 2 joins the group
        $request = $this->createRequest(
            'POST',
            "/api/groups/{$this->group['id']}/join",
            [],
            ['X-User-Token' => $user2['token']]
        );
        $response = $this->app->handle($request);

        // User 1 sends a message
        $request = $this->createRequest(
            'POST',
            "/api/groups/{$this->group['id']}/messages",
            ['message' => 'Hello from user 1'],
            ['X-User-Token' => $this->userToken]
        );
        $response = $this->app->handle($request);
        $this->assertEquals(200, $response->getStatusCode());
        
        $message1 = json_decode((string)$response->getBody(), true);
        $this->assertEquals('Hello from user 1', $message1['message']);

        // User 2 sends a message
        $request = $this->createRequest(
            'POST',
            "/api/groups/{$this->group['id']}/messages",
            ['message' => 'Hello from user 2'],
            ['X-User-Token' => $user2['token']]
        );
        $response = $this->app->handle($request);
        $this->assertEquals(200, $response->getStatusCode());
        
        $message2 = json_decode((string)$response->getBody(), true);
        $this->assertEquals('Hello from user 2', $message2['message']);

        // List messages
        $request = $this->createRequest(
            'GET',
            "/api/groups/{$this->group['id']}/messages",
            [],
            ['X-User-Token' => $this->userToken]
        );
        $response = $this->app->handle($request);
        $this->assertEquals(200, $response->getStatusCode());
        
        $messages = json_decode((string)$response->getBody(), true);
        $this->assertCount(2, $messages);
    }

    public function testNonMemberCannotSendMessage(): void
    {
        // Create a group
        $request = $this->createRequest(
            'POST',
            '/api/groups',
            ['name' => 'Private Group'],
            ['X-User-Token' => $this->userToken]
        );
        $response = $this->app->handle($request);
        $group = json_decode((string)$response->getBody(), true);

        // Create another user
        $request = $this->createRequest('POST', '/users');
        $response = $this->app->handle($request);
        $user2 = json_decode((string)$response->getBody(), true);

        // User 2 tries to send a message without joining
        $request = $this->createRequest(
            'POST',
            "/api/groups/{$group['id']}/messages",
            ['message' => 'This should fail'],
            ['X-User-Token' => $user2['token']]
        );
        $response = $this->app->handle($request);
        
        $this->assertEquals(403, $response->getStatusCode());
        
        $error = json_decode((string)$response->getBody(), true);
        $this->assertArrayHasKey('error', $error);
    }
}
