<?php

namespace App\Utils;

use Brick\Math\BigDecimal;

class AppNumber
{
    public static function limitDecimal($value, $length): bool
    {
        if (is_null($value)) {
            return  false;
        }

        $value = BigDecimal::of($value);
        $limit = '1' . str_repeat('0', $length);

        return $value->isGreaterThanOrEqualTo($limit);
    }
}
