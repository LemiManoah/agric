<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case MTN_Momo = 'mtn_momo';
    case AIRTEL_MONEY = 'airtel_money';
    case PAL_BANK = 'pal_bank';
    case ESCROW = 'escrow';
    case WIRE_TRANSFER = 'wire_transfer';
}
