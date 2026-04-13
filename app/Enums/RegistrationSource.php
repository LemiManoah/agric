<?php

namespace App\Enums;

enum RegistrationSource: string
{
    case SelfRegistered = 'self_registered';
    case FieldOfficer = 'field_officer';
    case Imported = 'imported';
}
