<?php

namespace App\Utils;

use Illuminate\Support\Str;

class AppStr
{
    public static function getErrorName($txt): string
    {
        $txt = str_replace('.', '_', $txt);
        $txt = trim($txt, '_');

        return Str::snake($txt);
    }

    public static function sanityText($txt): string
    {
        return strip_tags($txt);
    }

    public static function getErrorSwap($txt): string
    {
        $txt = str_replace('.', '_', strtolower($txt));
        $txt = str_replace('-', '_', strtolower($txt));
        $txt = str_replace(':', '', strtolower($txt));
        $txt = trim($txt, '_');

        return Str::snake($txt);
    }
}
