<?php

declare(strict_types=1);

namespace App;

enum ReservationStatus: string
{
    case CREATED = 'created';
    case PICKED_UP = 'picked_up';
    case RETURNED = 'returned';
}