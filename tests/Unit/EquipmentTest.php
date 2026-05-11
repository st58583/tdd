<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Equipment;
use PHPUnit\Framework\TestCase;

class EquipmentTest extends TestCase
{
    public function test_it_can_be_created_with_valid_data(): void
    {
        // Arrange (Příprava dat)
        $name = 'Mountain Bike';
        $category = 'Bike';
        $dailyRate = 250.0;

        // Act (Akce - vytvoření objektu)
        $equipment = new Equipment($name, $category, $dailyRate);

        // Assert (Ověření)
        $this->assertSame($name, $equipment->getName());
        $this->assertSame($category, $equipment->getCategory());
        $this->assertSame($dailyRate, $equipment->getDailyRate());
    }
	
	public function test_it_cannot_have_negative_daily_rate(): void
    {
        // Assert - řekneme PHPUnitu, že očekáváme výjimku
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Daily rate cannot be negative.');

        // Arrange & Act - pokusíme se vytvořit nevalidní objekt
        new Equipment('Snowboard', 'Winter', -50.0);
    }
}