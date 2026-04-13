<?php

namespace App\Enums;

enum InternetAccessLevel: string
{
    case None = 'none';
    case TwoG = '2g';
    case ThreeG = '3g';
    case FourG = '4g';
}
