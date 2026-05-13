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
        // POUŽITÍ STUBŮ MÍSTO MOCKŮ (řeší to PHPUnit Notices)
        $userRepoStub = $this->createStub(UserRepository::class);
        $equipmentRepoStub = $this->createStub(EquipmentRepository::class);
        $reservationRepoStub = $this->createStub(ReservationRepository::class);

        $user = new User(1, 'Jan', 'jan@example.com');
        $userRepoStub->method('findById')->willReturn($user);

        $equipment = new Equipment('Stan', 'Outdoor', 200.0);
        $equipmentRepoStub->method('findById')->willReturn($equipment);

        $requestData = [
            'id' => 1,
            'user_id' => 1,
            'equipment_ids' => [101],
            'start_date' => '2023-06-01',
            'end_date' => '2023-06-05'
        ];

        $controller = new ReservationController($userRepoStub, $equipmentRepoStub, $reservationRepoStub);
        $response = $controller->createReservation($requestData);

        $this->assertSame(201, $response['status_code']);
    }

    public function test_api_returns_400_when_user_not_found(): void
    {
        $userRepoStub = $this->createStub(UserRepository::class);
        $equipmentRepoStub = $this->createStub(EquipmentRepository::class);
        $reservationRepoStub = $this->createStub(ReservationRepository::class);

        $userRepoStub->method('findById')->willReturn(null);

        $requestData = [
            'id' => 1,
            'user_id' => 999,
            'equipment_ids' => [101],
            'start_date' => '2023-06-01',
            'end_date' => '2023-06-05'
        ];

        $controller = new ReservationController($userRepoStub, $equipmentRepoStub, $reservationRepoStub);
        $response = $controller->createReservation($requestData);

        $this->assertSame(400, $response['status_code']);
    }

    public function test_api_returns_400_when_domain_rule_is_violated(): void
    {
        $userRepoStub = $this->createStub(UserRepository::class);
        $equipmentRepoStub = $this->createStub(EquipmentRepository::class);
        $reservationRepoStub = $this->createStub(ReservationRepository::class);

        // Uživatel má neuhrazené pokuty - nesmí vytvořit rezervaci!
        $user = new User(1, 'Jan', 'jan@example.com');
        $user->markAsHavingUnpaidFines(); 
        $userRepoStub->method('findById')->willReturn($user);

        $requestData = [
            'id' => 1,
            'user_id' => 1,
            'equipment_ids' => [101],
            'start_date' => '2023-06-01',
            'end_date' => '2023-06-05'
        ];

        $controller = new ReservationController($userRepoStub, $equipmentRepoStub, $reservationRepoStub);
        $response = $controller->createReservation($requestData);

        // Očekáváme, že Controller zachytí DomainException a vrátí 400
        $this->assertSame(400, $response['status_code']);
		$this->assertStringContainsString('unpaid fines', $response['body']);
    }
}