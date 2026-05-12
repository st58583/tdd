<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Reservation;
use App\User;
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
}