<?php

namespace App\Enums;

enum ProductionScale: string
{
    case Smallholder = 'smallholder';
    case MediumScale = 'medium_scale';
    case LargeScale = 'large_scale';
}
