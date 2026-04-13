<?php

namespace App\Enums;

enum ListingStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case OutOfStock = 'out_of_stock';
    case Archived = 'archived';
}
