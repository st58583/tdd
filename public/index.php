<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\UserController;
use App\UserRepository;

// 1. Nastavíme, že naše API vrací vždy JSON
header('Content-Type: application/json');

// 2. Zjistíme, kam se uživatel ptá a jakou metodou
$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// 3. Připojení k reálné databázi (pomocí proměnných prostředí)
try {
    // Načteme konfiguraci z prostředí, nebo použijeme lokální výchozí hodnoty
    $dbHost = getenv('DB_HOST') ?: 'db';
    $dbName = getenv('DB_NAME') ?: 'rental_db';
    $dbUser = getenv('DB_USER') ?: 'root';
    $dbPass = getenv('DB_PASSWORD') ?: 'root';

    $dsn = "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4";
    
    $pdo = new PDO($dsn, $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// 4. Jednoduché směrování (Routing)
if ($method === 'POST' && $uri === '/users') {
    $repository = new UserRepository($pdo);
    $controller = new UserController($repository);

    // Přečteme si JSON, který nám uživatel poslal v těle (body) požadavku
    $inputJSON = file_get_contents('php://input');
    $requestData = json_decode($inputJSON, true) ?? [];

    // Pošleme to do našeho controlleru, který už známe
    $response = $controller->createUser($requestData);

    // Odpovíme správným HTTP kódem a daty
    http_response_code($response['status_code']);
    echo $response['body'];
    exit;
}

// Pokud endpoint neexistuje
http_response_code(404);
echo json_encode(['error' => 'Endpoint not found']);