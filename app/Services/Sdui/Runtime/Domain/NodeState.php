<?php

namespace App\Services\Sdui\Runtime\Domain;

enum NodeState: string
{
    case ENABLED = 'enabled';
    case DISABLED = 'disabled';
    case LOADING = 'loading';
}
