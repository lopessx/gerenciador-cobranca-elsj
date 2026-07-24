<?php

namespace App\Enum;

enum Role: string
{
    case Admin = 'admin';
    case Operator = 'operator';
}