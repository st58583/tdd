<?php

declare(strict_types=1);

namespace App;

class EquipmentRepository
{
    public function findById(int $id): ?Equipment
    {
        // Pro účely ukázky API tuto metodu necháme prázdnou. 
        // V reálu by zde byl SELECT do databáze.
        return null; 
    }
}