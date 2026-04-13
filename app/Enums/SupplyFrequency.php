<?php

namespace App\Enums;

enum SupplyFrequency: string
{
    case Daily = 'daily';
    case Weekly = 'weekly';
    case Monthly = 'monthly';
    case Seasonal = 'seasonal';
}
