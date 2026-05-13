<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Equipment;
use App\EquipmentRepository;
use App\ReservationController;
use App\ReservationRepository;
use App\User;
use App\UserRepository;
use PHPUnit\Framework\TestCase;

class ReservationControllerTest extends TestCase
{
    public function test_api_returns_201_on_successful_reservation(): void
    {
        // 1. Arrange: Vytvoříme Mocks pro všechny repozitáře
        $userRepoMock = $this->createMock(UserRepository::class);
        $equipmentRepoMock = $this->createMock(EquipmentRepository::class);
        $reservationRepoMock = $this->createMock(ReservationRepository::class);

        // Nastavíme, co mají Mocks vracet, když se jich Controller zeptá
        $user = new User(1, 'Jan', 'jan@example.com');
        $userRepoMock->method('findById')->willReturn($user);

        $equipment = new Equipment(101, 'Stan', 200.0);
        $equipmentRepoMock->method('findById')->willReturn($equipment);

        // Controller očekává tento tvar dat z REST API
        $requestData = [
            'id' => 1, // ID nové rezervace
            'user_id' => 1,
            'equipment_ids' => [101],
            'start_date' => '2023-06-01',
            'end_date' => '2023-06-05'
        ];

        $controller = new ReservationController($userRepoMock, $equipmentRepoMock, $reservationRepoMock);

        // 2. Act
        $response = $controller->createReservation($requestData);

        // 3. Assert
        $this->assertSame(201, $response['status_code']);
        $this->assertStringContainsString('Reservation created successfully', $response['body']);
    }

    public function test_api_returns_400_when_user_not_found(): void
    {
        $userRepoMock = $this->createMock(UserRepository::class);
        $equipmentRepoMock = $this->createMock(EquipmentRepository::class);
        $reservationRepoMock = $this->createMock(ReservationRepository::class);

        // Uživatel neexistuje (vrátí null)
        $userRepoMock->method('findById')->willReturn(null);

        $requestData = [
            'id' => 1,
            'user_id' => 999, // Neexistující ID
            'equipment_ids' => [101],
            'start_date' => '2023-06-01',
            'end_date' => '2023-06-05'
        ];

        $controller = new ReservationController($userRepoMock, $equipmentRepoMock, $reservationRepoMock);

        $response = $controller->createReservation($requestData);

        $this->assertSame(400, $response['status_code']);
        $this->assertStringContainsString('User not found', $response['body']);
    }
}