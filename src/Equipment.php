<?php

declare(strict_types=1);

namespace App;

class Equipment
{
    private string $name;
    private string $category;
    private float $dailyRate;

    public function __construct(string $name, string $category, float $dailyRate)
    {
        $this->name = $name;
        $this->category = $category;
        $this->dailyRate = $dailyRate;
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