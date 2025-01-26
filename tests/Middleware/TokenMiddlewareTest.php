<?php
declare(strict_types=1);

namespace Tests\Middleware;

use App\Middleware\TokenMiddleware;
use App\Services\UserService;
use Tests\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

class TokenMiddlewareTest extends TestCase
{
    private TokenMiddleware $middleware;
    private string $userToken;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new TokenMiddleware();
        
        // Create a test user
        $userService = new UserService();
        $user = $userService->createUser();
        $this->userToken = $user['token'];
    }

    public function testMiddlewareWithoutToken(): void
    {
        $request = $this->createRequest('GET', '/');
        $response = $this->middleware->__invoke($request, $this->createRequestHandler());
        
        $this->assertEquals(401, $response->getStatusCode());
        
        $data = json_decode((string)$response->getBody(), true);
        $this->assertEquals('Token is required', $data['error']);
    }

    public function testMiddlewareWithInvalidToken(): void
    {
        $request = $this->createRequest('GET', '/', [], ['X-User-Token' => 'invalid_token']);
        $response = $this->middleware->__invoke($request, $this->createRequestHandler());
        
        $this->assertEquals(401, $response->getStatusCode());
        
        $data = json_decode((string)$response->getBody(), true);
        $this->assertEquals('Invalid token', $data['error']);
    }

    public function testMiddlewareWithValidToken(): void
    {
        $request = $this->createRequest('GET', '/', [], ['X-User-Token' => $this->userToken]);
        $response = $this->middleware->__invoke($request, $this->createRequestHandler());
        
        $this->assertEquals(200, $response->getStatusCode());
        
        $data = json_decode((string)$response->getBody(), true);
        $this->assertEquals('success', $data['status']);
    }

    private function createRequestHandler(): RequestHandlerInterface
    {
        return new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): Response
            {
                $response = new Response();
                $response->getBody()->write(json_encode(['status' => 'success']));
                return $response->withHeader('Content-Type', 'application/json');
            }
        };
    }
}
