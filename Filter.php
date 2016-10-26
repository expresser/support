<?php

namespace Expresser\Support;

class Filter
{
    public static function isEmail($value)
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL);
    }

    public static function isUrl($value)
    {
        return filter_var($value, FILTER_VALIDATE_URL);
    }
}
