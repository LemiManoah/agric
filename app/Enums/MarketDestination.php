<?php

namespace App\Enums;

enum MarketDestination: string
{
    case FarmGate = 'farm_gate';
    case LocalMarket = 'local_market';
    case Cooperative = 'cooperative';
    case Processor = 'processor';
    case Exporter = 'exporter';
}
