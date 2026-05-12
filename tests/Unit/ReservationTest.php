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
}