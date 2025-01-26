<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Services\UserService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class TokenMiddleware
{
    private UserService $userService;

    public function __construct()
    {
        $this->userService = new UserService();
    }

    public function __invoke(Request $request, RequestHandler $handler): ResponseInterface
    {
        $token = $request->getHeaderLine('X-User-Token');

        if (empty($token)) {
            $response = new Response();
            $response->getBody()->write(json_encode(['error' => 'Token is required']));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(401);
        }

        $user = $this->userService->findByToken($token);
        if (!$user) {
            $response = new Response();
            $response->getBody()->write(json_encode(['error' => 'Invalid token']));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(401);
        }

        return $handler->handle($request->withAttribute('user_id', $user['id']));
    }
}
