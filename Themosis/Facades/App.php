<?php

namespace Expresser\Support\Themosis\Facades;

use Themosis\Facades\Facade;

class App extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'app';
    }
}
