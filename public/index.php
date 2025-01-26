<?php
declare(strict_types=1);

use DI\ContainerBuilder;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

// Create Container
$containerBuilder = new ContainerBuilder();
$container = $containerBuilder->build();

// Create App
$app = AppFactory::createFromContainer($container);

// Add Error Middleware
$app->addErrorMiddleware(true, true, true);

// Add routes
(require __DIR__ . '/../src/Routes/api.php')($app);

$app->run();
