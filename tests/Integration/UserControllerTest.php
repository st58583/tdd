<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\UserController;
use App\UserRepository;
use PDO;
use PHPUnit\Framework\TestCase;

class UserControllerTest extends TestCase
{
    private PDO $pdo;
    private UserRepository $repository;
    private UserController $controller;

    protected function setUp(): void
    {
        $dsn = 'mysql:host=db;dbname=rental_db;charset=utf8mb4';
        $this->pdo = new PDO($dsn, 'root', 'root');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->pdo->exec('DROP TABLE IF EXISTS users');
        $this->pdo->exec('
            CREATE TABLE users (
                id INT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL,
                has_unpaid_fines TINYINT(1) DEFAULT 0
            )
        ');

        $this->repository = new UserRepository($this->pdo);
        // Do controlleru pošleme naše úložiště, aby mohl uživatele reálně ukládat
        $this->controller = new UserController($this->repository);
    }

    public function test_api_returns_201_on_successful_creation(): void
    {
        // Arrange: Simulace validních dat z REST API požadavku
        $requestData = [
            'id' => 10,
            'name' => 'API Zákazník',
            'email' => 'api@example.com'
        ];

        // Act
        $response = $this->controller->createUser($requestData);

        // Assert: Očekáváme HTTP 201 Created
        $this->assertSame(201, $response['status_code']);
        $this->assertStringContainsString('User created successfully', $response['body']);
        
        // Ověříme, že to Controller opravdu uložil do DB
        $this->assertNotNull($this->repository->findById(10));
    }

    public function test_api_returns_400_on_validation_error_missing_data(): void
    {
        // Arrange: Chybí ID a email
        $requestData = [
            'name' => 'Špatný Zákazník'
        ];

        // Act
        $response = $this->controller->createUser($requestData);

        // Assert: Očekáváme HTTP 400 Bad Request
        $this->assertSame(400, $response['status_code']);
        $this->assertStringContainsString('Validation error: missing required fields', $response['body']);
    }

    public function test_api_returns_400_on_invalid_email_format(): void
    {
        // Arrange: Špatný formát emailu
        $requestData = [
            'id' => 11,
            'name' => 'Test',
            'email' => 'tohle-neni-email'
        ];

        // Act
        $response = $this->controller->createUser($requestData);

        // Assert: Očekáváme HTTP 400 Bad Request
        $this->assertSame(400, $response['status_code']);
        $this->assertStringContainsString('Validation error: invalid email format', $response['body']);
    }
}