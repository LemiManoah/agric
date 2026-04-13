<?php

namespace App\Enums;

enum AgentOnboardingStatus: string
{
    case Onboarding = 'onboarding';
    case Active = 'active';
    case Suspended = 'suspended';
}
