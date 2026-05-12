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
}