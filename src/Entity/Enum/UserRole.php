<?php

namespace App\Entity\Enum;

enum UserRole: string
{
    case Admin = 'ROLE_ADMIN';
    case Operator = 'ROLE_OPERATOR';
    case Reader = 'ROLE_READER';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrador',
            self::Operator => 'Operador',
            self::Reader => 'Leitor',
        };
    }
}