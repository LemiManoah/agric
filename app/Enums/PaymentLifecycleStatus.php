<?php

namespace App\Enums;

enum PaymentLifecycleStatus: string
{
    case Pending = 'pending';
    case Successful = 'successful';
    case Failed = 'failed';
    case Refunded = 'refunded';
    case Partial = 'partial';
}
