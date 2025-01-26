<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

abstract class BaseModel
{
    protected PDO $db;

    public function __construct()
    {
        $dbPath = __DIR__ . '/../../database/chat.db';
        $this->db = new PDO("sqlite:$dbPath");
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }
}
