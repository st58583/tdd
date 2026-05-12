<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function test_it_can_be_created_with_valid_data(): void
    {
        // Arrange & Act
        $user = new User(1, 'Jan Novák', 'jan@example.com');

        // Assert
        $this->assertSame(1, $user->getId());
        $this->assertSame('Jan Novák', $user->getName());
        $this->assertSame('jan@example.com', $user->getEmail());
        $this->assertFalse($user->hasUnpaidFines()); // Výchozí stav by měl být false
    }

    public function test_it_can_record_unpaid_fines(): void
    {
        // Arrange
        $user = new User(2, 'Petr Svetr', 'petr@example.com');
        
        // Act - zákazník dostane pokutu
        $user->markAsHavingUnpaidFines();

        // Assert
        $this->assertTrue($user->hasUnpaidFines());
    }
}