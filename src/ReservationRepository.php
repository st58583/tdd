<?php

declare(strict_types=1);

namespace App;

class ReservationRepository
{
    public function save(Reservation $reservation): void
    {
        // V reálu by zde byl komplexní INSERT do několika tabulek (vazby M:N)
    }
}