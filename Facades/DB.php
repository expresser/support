<?php

namespace Expresser\Support\Facades;

class DB extends \Themosis\Facades\Facade
{
    protected static function getFacadeAccessor()
    {
        return 'db';
    }
}
