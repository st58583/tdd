<?php

declare(strict_types=1);

namespace Tests\Integration;

use PDO;
use PHPUnit\Framework\TestCase;

class DatabaseTest extends TestCase
{
    public function test_it_can_connect_to_database(): void
    {
        // Připojovací údaje odpovídají našemu docker-compose.yml
        $dsn = 'mysql:host=db;dbname=rental_db;charset=utf8mb4';
        $user = 'root';
        $password = 'root';

        // Zkusíme se připojit
        $pdo = new PDO($dsn, $user, $password);
        
        // Zkusíme jednoduchý dotaz, abychom ověřili, že DB opravdu odpovídá
        $stmt = $pdo->query('SELECT 1 as result');
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertEquals(1, $row['result']);
    }
}