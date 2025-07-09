<?php

declare(strict_types=1);

namespace App\Helper;

class DateFormatter
{
    public const string DATETIME_FORMAT      = 'd/m/Y H:i';
    public const string DATETIME_FORMAT_LONG = 'jS F Y H:i';

    public function format(\DateTimeInterface $date, bool $long = false): string
    {
        return $date->format($long ? self::DATETIME_FORMAT_LONG : self::DATETIME_FORMAT);
    }
}
