<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Services\UserService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class UserController extends BaseController
{
    private UserService $userService;

    public function __construct()
    {
        $this->userService = new UserService();
    }

    public function create(Request $request, Response $response): Response
    {
        $user = $this->userService->createUser();
        return $this->jsonResponse($response, $user);
    }
}
