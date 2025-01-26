<?php
declare(strict_types=1);

use Slim\App;

return function (App $app) {
    $app->get('/', function ($request, $response) {
        $response->getBody()->write(json_encode(['status' => 'running']));
        return $response->withHeader('Content-Type', 'application/json');
    });
};
