<?php

namespace App\Services\Sdui\Runtime\Domain;

enum NodeVisibility: string
{
    case VISIBLE = 'visible';
    case HIDDEN = 'hidden';
    case GONE = 'gone';
}
