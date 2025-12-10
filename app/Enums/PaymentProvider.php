<?php

namespace App\Enums;

enum PaymentProvider: string
{
    case LemonSqueezy = 'lemon_squeezy';
    case Paddle = 'paddle';
    case Creem = 'creem';
}
