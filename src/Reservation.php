<?php

declare(strict_types=1);

namespace App;

class Reservation
{
    public const MAX_EQUIPMENT_ITEMS = 5;

    /** @var Equipment[] */
    private array $equipment = [];
    
    // Nová rezervace má vždy výchozí stav CREATED
    private ReservationStatus $status = ReservationStatus::CREATED;

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
        $days = $this->startDate->diff($this->endDate)->days;
        
        if ($days === 0) {
            $days = 1;
        }

        $dailyTotal = 0.0;
        foreach ($this->equipment as $item) {
            $dailyTotal += $item->getDailyRate();
        }

        $totalPrice = $dailyTotal * $days;

        if ($days > 7) {
            $totalPrice *= 0.9;
        }

        return $totalPrice;
    }

    // --- NOVÉ METODY PRO STAVY ---

    public function getStatus(): ReservationStatus
    {
        return $this->status;
    }

    public function markAsPickedUp(): void
    {
        $this->status = ReservationStatus::PICKED_UP;
    }

    public function markAsReturned(): void
    {
        // Hlídání business pravidla: Nelze vrátit to, co nebylo vyzvednuto
        if ($this->status !== ReservationStatus::PICKED_UP) {
            throw new \DomainException('Cannot return a reservation that has not been picked up.');
        }

        $this->status = ReservationStatus::RETURNED;
    }
}