<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Services\GroupService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use RuntimeException;

class GroupController extends BaseController
{
    private GroupService $groupService;

    public function __construct()
    {
        $this->groupService = new GroupService();
    }

    public function create(Request $request, Response $response): Response
    {
        $data = $this->getRequestBody($request);
        $userId = $request->getAttribute('user_id');

        if (!isset($data['name']) || empty(trim($data['name']))) {
            return $this->jsonResponse($response, ['error' => 'Name is required'], 400);
        }

        try {
            $group = $this->groupService->createGroup($data['name'], $userId);
            return $this->jsonResponse($response, $group);
        } catch (RuntimeException $e) {
            return $this->jsonResponse($response, ['error' => $e->getMessage()], 400);
        }
    }

    public function join(Request $request, Response $response, array $args): Response
    {
        $groupId = (int)$args['id'];
        $userId = $request->getAttribute('user_id');

        try {
            $membership = $this->groupService->joinGroup($groupId, $userId);
            return $this->jsonResponse($response, $membership);
        } catch (RuntimeException $e) {
            $status = str_contains($e->getMessage(), 'not found') ? 404 : 400;
            return $this->jsonResponse($response, ['error' => $e->getMessage()], $status);
        }
    }

    public function leave(Request $request, Response $response, array $args): Response
    {
        $groupId = (int)$args['id'];
        $userId = $request->getAttribute('user_id');

        try {
            $this->groupService->leaveGroup($groupId, $userId);
            return $this->jsonResponse($response, ['status' => 'success']);
        } catch (RuntimeException $e) {
            $status = str_contains($e->getMessage(), 'not found') ? 404 : 400;
            return $this->jsonResponse($response, ['error' => $e->getMessage()], $status);
        }
    }

    public function getMembers(Request $request, Response $response, array $args): Response
    {
        $groupId = (int)$args['id'];

        try {
            $members = $this->groupService->getMembers($groupId);
            return $this->jsonResponse($response, $members);
        } catch (RuntimeException $e) {
            return $this->jsonResponse($response, ['error' => $e->getMessage()], 404);
        }
    }

    public function getAll(Request $request, Response $response): Response
    {
        $groups = $this->groupService->getAllGroups();
        return $this->jsonResponse($response, $groups);
    }
}
