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
}