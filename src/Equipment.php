<?php

declare(strict_types=1);

namespace App;

class Equipment
{
    public function __construct(
        private string $name,
        private string $category,
        private float $dailyRate
    ) {
        if ($dailyRate < 0) {
            throw new \InvalidArgumentException('Daily rate cannot be negative.');
        }
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function getDailyRate(): float
    {
        return $this->dailyRate;
    }
}