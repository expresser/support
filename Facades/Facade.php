<?php namespace Expresser\Support\Facades;

class Facade extends \Themosis\Facades\Facade {

  public static function register() {

		static::$app->register(static::getIgniterClass());
	}

  // TODO: register igniters from config
}
