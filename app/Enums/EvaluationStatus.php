<?php

namespace App\Enums;

enum EvaluationStatus: string
{
    case DRAFT = 'draft';
    case SUBMITTED = 'submitted';
    case UPDATED = 'updated';
    case FINALIZED = 'finalized';
}
