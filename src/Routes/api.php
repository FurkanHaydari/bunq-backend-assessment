<?php
declare(strict_types=1);

use App\Controllers\HomeController;
use App\Controllers\UserController;
use App\Controllers\GroupController;
use App\Controllers\MessageController;
use App\Middleware\TokenMiddleware;
use Slim\App;

return function (App $app) {
    // Home route
    $app->get('/', [HomeController::class, 'index']);

    // User routes
    $app->post('/users', [UserController::class, 'create']);

    // Protected routes
    $app->group('/api', function ($group) {
        // Group routes
        $group->post('/groups', [GroupController::class, 'create']);
        $group->get('/groups', [GroupController::class, 'getAll']);
        $group->post('/groups/{id}/join', [GroupController::class, 'join']);
        $group->post('/groups/{id}/leave', [GroupController::class, 'leave']);
        $group->get('/groups/{id}/members', [GroupController::class, 'getMembers']);

        // Message routes
        $group->post('/groups/{id}/messages', [MessageController::class, 'send']);
        $group->get('/groups/{id}/messages', [MessageController::class, 'list']);
    })->add(new TokenMiddleware());
};
