<?php namespace Expresser\Support\Facades;

class Facade extends \Themosis\Facades\Facade {

  public static function register() {

		static::$app->register(static::getIgniterClass());
	}

  protected static function getIgniterClass() {

    throw new \RuntimeException('Facade does not implement getIgniterClass method.');
  }
}
