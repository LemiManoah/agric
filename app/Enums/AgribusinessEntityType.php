<?php

namespace App\Enums;

enum AgribusinessEntityType: string
{
    case Cooperative = 'cooperative';
    case TractorAssociation = 'tractor_association';
    case InputDealer = 'input_dealer';
    case GrainMiller = 'grain_miller';
    case ColdChainOperator = 'cold_chain_operator';
    case ExportCompany = 'export_company';
    case AgroDealer = 'agro_dealer';
}
