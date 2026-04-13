<?php

namespace App\Enums;

enum IrrigationAvailability: string
{
    case None = 'none';
    case Seasonal = 'seasonal';
    case YearRound = 'year_round';
}
