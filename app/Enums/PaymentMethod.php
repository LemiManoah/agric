<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Momo = 'momo';
    case Airtel = 'airtel';
    case PalBank = 'pal_bank';
    case Escrow = 'escrow';
    case WireTransfer = 'wire_transfer';
}
