<?php namespace Expresser\Support;

use Arrayable;

abstract class Fluent {

  protected $attributes = [];

  public function __construct($attributes = []) {

    if (!is_array($attributes)) $attributes = (array)$attributes;

    $this->fill($attributes);
  }

  public function fill(array $attributes) {

    foreach ($attributes as $key => $value) {

      $this->setAttribute($key, $value);
    }

    return $this;
  }

  public function getAttribute($key) {

    return $this->getAttributeValue($key);
  }

  public function getAttributeFromArray($key) {

    if (array_key_exists($key, $this->attributes)) return $this->attributes[$key];
  }

  public function getAttributeValue($key) {

    $value = $this->getAttributeFromArray($key);

    if (is_null($value)) {

      $method = camel_case($key);

      if (method_exists($this, $method)) $value = $this->$method();
    }

    if ($this->hasGetMutator($key)) return $this->{'get' . studly_case($key) . 'Attribute'}($value);

    return $value;
  }

  public function hasGetMutator($key) {

    return method_exists($this, 'get' . studly_case($key) . 'Attribute');
  }

  public function hasSetMutator($key) {

    return method_exists($this, 'set' . studly_case($key) . 'Attribute');
  }

  public function setAttribute($key, $value) {

    if ($this->hasSetMutator($key)) {

      $this->{'set' . studly_case($key) . 'Attribute'}($value);
    }
    else {

      $this->attributes[$key] = $value;
    }
  }

  public function toArray() {

    return array_map(function ($value) {

      return $value instanceof Arrayable ? $value->toArray() : $value;

    }, $this->attributes);
  }

  public function toJson($options = 0) {

    return json_encode($this->toArray(), $options);
  }

  public function transformKey($key) {

    return $key;
  }

  public function __call($method, $parameters) {

    $this->attributes[$method] = count($parameters) > 0 ? $parameters[0] : true;

    return $this;
  }

  public function __get($key) {

    return $this->getAttribute($this->transformKey($key));
  }

  public function __set($key, $value) {

    $this->setAttribute($this->transformKey($key), $value);
  }

  public function __isset($key) {

    return !is_null($this->getAttribute($this->transformKey($key)));
  }

  public static function make($attributes = []) {

    return new static($attributes);
  }

  public static function register() {

    $class = get_called_class();

    static::registerClassHooks($class);
  }

  public static function __callStatic($method, $parameters) {

    $instance = new static;

    return call_user_func_array(array($instance, $method), $parameters);
  }

  protected static function registerHooks($class) {

    // Override to register actions and filters
  }

  private static function registerClassHooks($class) {

    static::registerHooks($class);

    $method = explode('\\', $class);
    $method = end($method);
    $method = 'register' . $method . 'Hooks';

    if (method_exists($class, $method) && is_callable([$class, $method])) {

      forward_static_call([$class, $method], [$class]);
    }
  }
}
