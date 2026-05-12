<?php

declare(strict_types=1);

namespace App;

class Reservation
{
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
}