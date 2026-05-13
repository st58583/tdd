<?php

declare(strict_types=1);

namespace App;

class UserController
{
    public function __construct(
        private readonly UserRepository $repository
    ) {
    }

    /**
     * Simulace REST API endpointu POST /users
     */
    public function createUser(array $requestData): array
    {
        // 1. Validace: Jsou přítomna všechna povinná data?
        if (!isset($requestData['id'], $requestData['name'], $requestData['email'])) {
            return $this->jsonResponse(400, ['error' => 'Validation error: missing required fields']);
        }

        // 2. Validace: Je email ve správném formátu?
        if (!filter_var($requestData['email'], FILTER_VALIDATE_EMAIL)) {
            return $this->jsonResponse(400, ['error' => 'Validation error: invalid email format']);
        }

        // 3. Vytvoření doménového objektu
        $user = new User(
            (int) $requestData['id'],
            (string) $requestData['name'],
            (string) $requestData['email']
        );

        // 4. Uložení do databáze
        $this->repository->save($user);

        // 5. Vrácení úspěšné odpovědi (201 Created)
        return $this->jsonResponse(201, [
            'message' => 'User created successfully',
            'id' => $user->getId()
        ]);
    }

    /**
     * Pomocná metoda pro formátování HTTP odpovědi
     */
    private function jsonResponse(int $statusCode, array $data): array
    {
        return [
            'status_code' => $statusCode,
            // V reálné aplikaci by se zde nastavovaly HTTP hlavičky, 
            // pro účely naší ukázky vracíme pole, aby se to snadno testovalo
            'body' => json_encode($data) 
        ];
    }
}