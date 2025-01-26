<?php
declare(strict_types=1);

namespace Tests;

use DI\ContainerBuilder;
use PDO;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\StreamFactory;

class TestCase extends BaseTestCase
{
    protected App $app;
    protected PDO $db;
    protected string $dbFile;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Set up test database
        $this->dbFile = __DIR__ . '/../database/test.db';
        if (file_exists($this->dbFile)) {
            unlink($this->dbFile);
        }

        // Create database directory if it doesn't exist
        $dbDir = dirname($this->dbFile);
        if (!file_exists($dbDir)) {
            mkdir($dbDir, 0777, true);
        }

        // Initialize database
        $this->db = new PDO("sqlite:{$this->dbFile}");
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Run schema
        $schema = file_get_contents(__DIR__ . '/../database/schema.sql');
        $this->db->exec($schema);

        // Override database path in BaseModel
        putenv("DB_PATH={$this->dbFile}");

        // Create app instance
        $containerBuilder = new ContainerBuilder();
        $container = $containerBuilder->build();
        $this->app = AppFactory::createFromContainer($container);
        
        // Add error middleware
        $this->app->addErrorMiddleware(true, true, true);
        
        // Add routes
        (require __DIR__ . '/../src/Routes/api.php')($this->app);
    }

    protected function createRequest(
        string $method,
        string $path,
        array $body = [],
        array $headers = []
    ) {
        $request = (new ServerRequestFactory())->createServerRequest($method, $path);
        
        // Add Content-Type header for requests with body
        if (!empty($body)) {
            $headers['Content-Type'] = 'application/json';
            $streamFactory = new StreamFactory();
            $stream = $streamFactory->createStream(json_encode($body));
            $request = $request->withBody($stream);
        }
        
        foreach ($headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }
        
        return $request;
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        
        // Clean up test database
        if (file_exists($this->dbFile)) {
            unlink($this->dbFile);
        }
    }
}
