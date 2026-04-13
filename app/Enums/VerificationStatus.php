<?php

namespace App\Enums;

enum VerificationStatus: string
{
    case Submitted = 'submitted';
    case PendingReview = 'pending_review';
    case Verified = 'verified';
    case Suspended = 'suspended';
    case Rejected = 'rejected';
}
