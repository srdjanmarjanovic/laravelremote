<?php

namespace App\Enums;

enum ListingType: string
{
    case Regular = 'regular';
    case Featured = 'featured';
    case Top = 'top';

    public function isFeatured(): bool
    {
        return $this === self::Featured;
    }

    public function isTop(): bool
    {
        return $this === self::Top;
    }

    public function isSpecial(): bool
    {
        return $this === self::Featured || $this === self::Top;
    }
}
