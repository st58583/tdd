<?php

declare(strict_types=1);

namespace App;

class Reservation
{
    public const MAX_EQUIPMENT_ITEMS = 5;

    /** @var Equipment[] */
    private array $equipment = [];

    public function __construct(
        private readonly int $id,
        private readonly User $user,
        private readonly \DateTimeImmutable $startDate,
        private readonly \DateTimeImmutable $endDate
    ) {
        if ($user->hasUnpaidFines()) {
            throw new \DomainException('User with unpaid fines cannot make a reservation.');
        }
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @return Equipment[]
     */
    public function getEquipment(): array
    {
        return $this->equipment;
    }

    public function addEquipment(Equipment $equipment): void
    {
        if (count($this->equipment) >= self::MAX_EQUIPMENT_ITEMS) {
            throw new \DomainException('Reservation can contain a maximum of ' . self::MAX_EQUIPMENT_ITEMS . ' items.');
        }

        $this->equipment[] = $equipment;
    }
	
	public function calculateTotalPrice(): float
    {
        // Zjištění počtu dní (rozdíl mezi daty)
        $days = $this->startDate->diff($this->endDate)->days;
        
        // Pokud si někdo půjčí a vrátí ve stejný den, účtujeme minimálně 1 den
        if ($days === 0) {
            $days = 1;
        }

        $dailyTotal = 0.0;
        foreach ($this->equipment as $item) {
            $dailyTotal += $item->getDailyRate();
        }

        $totalPrice = $dailyTotal * $days;

        // Aplikace slevy 10 % pro rezervace delší než 7 dní
        if ($days > 7) {
            $totalPrice *= 0.9;
        }

        return $totalPrice;
    }
}