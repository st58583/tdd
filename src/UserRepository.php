<?php

declare(strict_types=1);

namespace App;

use PDO;

class UserRepository
{
    public function __construct(
        private readonly PDO $pdo
    ) {
    }

    public function save(User $user): void
    {
        // Používáme připravené dotazy (prepared statements) pro bezpečnost proti SQL injection
        $stmt = $this->pdo->prepare('
            INSERT INTO users (id, name, email, has_unpaid_fines) 
            VALUES (:id, :name, :email, :has_unpaid_fines)
            ON DUPLICATE KEY UPDATE 
                name = VALUES(name), 
                email = VALUES(email), 
                has_unpaid_fines = VALUES(has_unpaid_fines)
        ');

        $stmt->execute([
            'id' => $user->getId(),
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'has_unpaid_fines' => $user->hasUnpaidFines() ? 1 : 0,
        ]);
    }

    public function findById(int $id): ?User
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null; // Uživatel nebyl nalezen
        }

        $user = new User(
            (int) $row['id'],
            $row['name'],
            $row['email']
        );

        if ((bool) $row['has_unpaid_fines']) {
            $user->markAsHavingUnpaidFines();
        }

        return $user;
    }
}