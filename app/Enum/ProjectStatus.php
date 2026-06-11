<?php

namespace App\Enum;

enum ProjectStatus: string
{
    case DRAFT = 'draft';
    case DISCOVERY = 'discovery';
    case EXECUTION = 'execution';
    case DELIVERED = 'delivered';
}
