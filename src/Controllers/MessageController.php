<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Services\MessageService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use RuntimeException;

class MessageController extends BaseController
{
    private MessageService $messageService;

    public function __construct()
    {
        $this->messageService = new MessageService();
    }

    public function send(Request $request, Response $response, array $args): Response
    {
        $groupId = (int)$args['id'];
        $userId = $request->getAttribute('user_id');
        $data = $this->getRequestBody($request);

        if (!isset($data['message']) || empty(trim($data['message']))) {
            return $this->jsonResponse($response, ['error' => 'Message is required'], 400);
        }

        try {
            $message = $this->messageService->sendMessage($groupId, $userId, $data['message']);
            return $this->jsonResponse($response, $message);
        } catch (RuntimeException $e) {
            $status = match (true) {
                str_contains($e->getMessage(), 'not found') => 404,
                str_contains($e->getMessage(), 'must be a member') => 403,
                default => 400,
            };
            return $this->jsonResponse($response, ['error' => $e->getMessage()], $status);
        }
    }

    public function list(Request $request, Response $response, array $args): Response
    {
        $groupId = (int)$args['id'];
        $userId = $request->getAttribute('user_id');

        try {
            $messages = $this->messageService->getGroupMessages($groupId, $userId);
            return $this->jsonResponse($response, $messages);
        } catch (RuntimeException $e) {
            $status = match (true) {
                str_contains($e->getMessage(), 'not found') => 404,
                str_contains($e->getMessage(), 'must be a member') => 403,
                default => 400,
            };
            return $this->jsonResponse($response, ['error' => $e->getMessage()], $status);
        }
    }
}
