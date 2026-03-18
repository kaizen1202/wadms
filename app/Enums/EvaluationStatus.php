<?php

namespace App\Enums;

enum EvaluationStatus: string
{
    case PENDING = 'pending';
    case SUBMITTED = 'submitted';
    case UPDATED = 'updated';
    case FINALIZED = 'finalized';
}
