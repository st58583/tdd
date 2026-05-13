<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\User;
use App\UserRepository;
use PDO;
use PHPUnit\Framework\TestCase;

class UserRepositoryTest extends TestCase
{
    private PDO $pdo;
    private UserRepository $repository;

    protected function setUp(): void
    {
        // Tento kód se spustí PŘED každým testem
        $dsn = 'mysql:host=db;dbname=rental_db;charset=utf8mb4';
        $this->pdo = new PDO($dsn, 'root', 'root');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Vytvoříme si čistou testovací tabulku
        $this->pdo->exec('DROP TABLE IF EXISTS users');
        $this->pdo->exec('
            CREATE TABLE users (
                id INT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL,
                has_unpaid_fines TINYINT(1) DEFAULT 0
            )
        ');

        // Inicializujeme naše budoucí úložiště
        $this->repository = new UserRepository($this->pdo);
    }

    public function test_it_can_save_and_find_user(): void
    {
        // Arrange
        $user = new User(1, 'Jana Nová', 'jana@example.com');
        $user->markAsHavingUnpaidFines(); // Rovnou otestujeme i tento stav

        // Act - Uložení do DB
        $this->repository->save($user);

        // Act - Načtení z DB
        $foundUser = $this->repository->findById(1);

        // Assert
        $this->assertNotNull($foundUser);
        $this->assertSame(1, $foundUser->getId());
        $this->assertSame('Jana Nová', $foundUser->getName());
        $this->assertSame('jana@example.com', $foundUser->getEmail());
        $this->assertTrue($foundUser->hasUnpaidFines());
    }
}