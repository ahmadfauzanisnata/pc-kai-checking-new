<?php

namespace App\Enums;

enum DeviceType: string
{
    case PC = 'PC';
    case LAPTOP = 'LAPTOP';

    public function label(): string
    {
        return match ($this) {
            self::PC => 'PC Desktop',
            self::LAPTOP => 'Laptop',
        };
    }
}
