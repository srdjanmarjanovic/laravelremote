<?php

namespace App\Enums;

enum PaymentType: string
{
    case Initial = 'initial';
    case Upgrade = 'upgrade';
}
