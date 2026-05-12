<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Reservation;
use App\User;
use App\Equipment;
use PHPUnit\Framework\TestCase;

class ReservationTest extends TestCase
{
    public function test_it_can_be_created_for_valid_user(): void
    {
        // Arrange
        $user = new User(1, 'Jan Novák', 'jan@example.com');
        $startDate = new \DateTimeImmutable('2026-06-01');
        $endDate = new \DateTimeImmutable('2026-06-05');

        // Act
        $reservation = new Reservation(1, $user, $startDate, $endDate);

        // Assert
        $this->assertSame(1, $reservation->getId());
        $this->assertSame($user, $reservation->getUser());
        $this->assertEmpty($reservation->getEquipment()); // Zatím je prázdná
    }

    public function test_it_cannot_be_created_for_user_with_unpaid_fines(): void
    {
        // Arrange
        $user = new User(2, 'Karel Průšvihář', 'karel@example.com');
        $user->markAsHavingUnpaidFines(); // Karel dostane pokutu
        
        $startDate = new \DateTimeImmutable('2026-06-01');
        $endDate = new \DateTimeImmutable('2026-06-05');

        // Assert - Očekáváme doménovou výjimku
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('User with unpaid fines cannot make a reservation.');

        // Act - Pokus o vytvoření rezervace
        new Reservation(2, $user, $startDate, $endDate);
    }
	
	public function test_it_can_add_equipment(): void
    {
        // Arrange
        $user = new User(1, 'Jan', 'jan@example.com');
        $reservation = new Reservation(1, $user, new \DateTimeImmutable('2026-06-01'), new \DateTimeImmutable('2026-06-05'));
        $equipment = new Equipment('Horské kolo', 'Cyklistika', 500.0);

        // Act
        $reservation->addEquipment($equipment);

        // Assert
        $this->assertCount(1, $reservation->getEquipment());
        $this->assertSame($equipment, $reservation->getEquipment()[0]);
    }

    public function test_it_cannot_have_more_than_five_items(): void
    {
        // Arrange
        $user = new User(1, 'Jan', 'jan@example.com');
        $reservation = new Reservation(1, $user, new \DateTimeImmutable('2026-06-01'), new \DateTimeImmutable('2026-06-05'));
        $equipment = new Equipment('Helma', 'Příslušenství', 50.0);

        // Přidáme 5 položek (maximální kapacita)
        for ($i = 0; $i < 5; $i++) {
            $reservation->addEquipment($equipment);
        }

        // Assert - Očekáváme výjimku při 6. položce
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Reservation can contain a maximum of 5 items.');

        // Act - Pokus o přidání 6. položky
        $reservation->addEquipment($equipment);
    }
	
	public function test_it_calculates_total_price_without_discount(): void
    {
        // Arrange
        $user = new User(1, 'Jan', 'jan@test.com');
        // Rezervace na 5 dní (od 1. do 6. = 5 nocí/dní)
        $reservation = new Reservation(1, $user, new \DateTimeImmutable('2026-06-01'), new \DateTimeImmutable('2026-06-06'));

        // Vytvoření MOCKU (falešného objektu) pro Equipment
        $equipmentMock1 = $this->createMock(Equipment::class);
        $equipmentMock1->method('getDailyRate')->willReturn(100.0); // Falešné kolo za 100/den

        $equipmentMock2 = $this->createMock(Equipment::class);
        $equipmentMock2->method('getDailyRate')->willReturn(200.0); // Falešné lyže za 200/den

        $reservation->addEquipment($equipmentMock1);
        $reservation->addEquipment($equipmentMock2);

        // Act & Assert
        // Cena celkem za den je 300. Krát 5 dní = 1500.
        $this->assertSame(1500.0, $reservation->calculateTotalPrice());
    }

    public function test_it_calculates_total_price_with_ten_percent_discount_for_long_rentals(): void
    {
        // Arrange
        $user = new User(1, 'Jan', 'jan@test.com');
        // Rezervace na 10 dní
        $reservation = new Reservation(1, $user, new \DateTimeImmutable('2026-06-01'), new \DateTimeImmutable('2026-06-11'));

        $equipmentMock = $this->createMock(Equipment::class);
        $equipmentMock->method('getDailyRate')->willReturn(100.0);

        $reservation->addEquipment($equipmentMock);

        // Act & Assert
        // Cena za den 100. Krát 10 dní = 1000. Sleva 10% = 900.
        $this->assertSame(900.0, $reservation->calculateTotalPrice());
    }
}