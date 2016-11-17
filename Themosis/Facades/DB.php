<?php

namespace Expresser\Support\Themosis\Facades;

use Themosis\Facades\Facade;

class DB extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'db';
    }
}
