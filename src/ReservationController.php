<?php

declare(strict_types=1);

namespace App;

class ReservationController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EquipmentRepository $equipmentRepository,
        private readonly ReservationRepository $reservationRepository
    ) {
    }

    public function createReservation(array $requestData): array
    {
        // 1. Validace povinných polí
        $requiredFields = ['id', 'user_id', 'equipment_ids', 'start_date', 'end_date'];
        foreach ($requiredFields as $field) {
            if (!isset($requestData[$field])) {
                return $this->jsonResponse(400, ['error' => "Validation error: missing required field '$field'"]);
            }
        }

        // 2. Načtení uživatele z databáze (přes repozitář)
        $user = $this->userRepository->findById((int) $requestData['user_id']);
        if (!$user) {
            return $this->jsonResponse(400, ['error' => 'User not found']);
        }

        try {
            // 3. Příprava datových typů pro doménu
            $startDate = new \DateTimeImmutable($requestData['start_date']);
            $endDate = new \DateTimeImmutable($requestData['end_date']);
            
            // 4. Vytvoření rezervace 
            // Zde může vyskočit \DomainException, pokud má uživatel nezaplacené pokuty!
            $reservation = new Reservation((int) $requestData['id'], $user, $startDate, $endDate);

            // 5. Načtení a přidání vybavení
            foreach ($requestData['equipment_ids'] as $equipmentId) {
                $equipment = $this->equipmentRepository->findById((int) $equipmentId);
                
                if (!$equipment) {
                    return $this->jsonResponse(400, ['error' => "Equipment with ID $equipmentId not found"]);
                }
                
                // Zde může vyskočit \DomainException, pokud překročíme limit 5 kusů!
                $reservation->addEquipment($equipment); 
            }

            // 6. Uložení plně sestavené rezervace do databáze
            $this->reservationRepository->save($reservation);

        } catch (\DomainException $e) {
            // Skvělé využití vlastních výjimek: Business chyby vracíme jako 400 Bad Request
            return $this->jsonResponse(400, ['error' => $e->getMessage()]);
        } catch (\Exception $e) {
            // Pokud selže parsování data (DateTimeImmutable)
            return $this->jsonResponse(400, ['error' => 'Invalid date format']);
        }

        // 7. Vše prošlo na jedničku
        return $this->jsonResponse(201, [
            'message' => 'Reservation created successfully',
            'id' => $reservation->getId()
        ]);
    }

    private function jsonResponse(int $statusCode, array $data): array
    {
        return [
            'status_code' => $statusCode,
            'body' => json_encode($data)
        ];
    }
}