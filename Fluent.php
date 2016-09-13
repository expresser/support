<?php namespace Expresser\Support;

use ArrayAccess;
use JsonSerializable;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

abstract class Fluent implements ArrayAccess, Arrayable, Jsonable, JsonSerializable {

  protected $attributes = [];

  protected $fieldPrefix = '';

  public function __construct(array $attributes = []) {

    $this->fill($attributes);
  }

  public function fill(array $attributes) {

    foreach ($attributes as $key => $value) {

      $this->setAttribute($key, $value);
    }

    return $this;
  }

  public function getAttribute($key) {

    return $this->getAttributeValue($this->transformKey($key));
  }

  public function getAttributeValue($key) {

    $value = $this->getAttributeFromArray($key);

    if (is_null($value)) {

      $method = camel_case($key);

      if (method_exists($this, $method)) {

        $value = $this->$method();
      }
    }

    if ($this->hasGetMutator($key)) {

      return $this->{'get' . studly_case($key) . 'Attribute'}($value);
    }

    return $value;
  }

  public function getAttributeFromArray($key) {

    if (array_key_exists($key, $this->attributes)) {

      return $this->attributes[$key];
    }

    if (array_key_exists($this->getFieldPrefix() . $key, $this->attributes)) {

      return $this->attributes[$this->getFieldPrefix() . $key];
    }
  }

  public function getFieldPrefix() {

    return $this->fieldPrefix;
  }

  public function hasGetMutator($key) {

    return method_exists($this, 'get' . studly_case($key) . 'Attribute');
  }

  public function hasSetMutator($key) {

    return method_exists($this, 'set' . studly_case($key) . 'Attribute');
  }

  public function setAttribute($key, $value) {

    $key = $this->transformKey($key);

    if ($this->hasSetMutator($key)) {

      $this->{'set' . studly_case($key) . 'Attribute'}($value);
    }
    else {

      $this->attributes[$key] = $value;
    }
  }

  public function transformKey($key) {

    return $key;
  }

  public function toArray() {

    return array_map(function ($value) {

      return $value instanceof Arrayable ? $value->toArray() : $value;

    }, $this->attributes);
  }

  public function toJson($options = 0) {

    return json_encode($this->toArray(), $options);
  }

  public function jsonSerialize() {

		return $this->toArray();
	}

  public function offsetExists($offset) {

		return isset($this->$offset);
	}

	public function offsetGet($offset) {

		return $this->$offset;
	}

	public function offsetSet($offset, $value) {

		$this->$offset = $value;
	}

	public function offsetUnset($offset) {

		unset($this->$offset);
	}

  public function __call($method, $parameters) {

    $this->attributes[$method] = count($parameters) > 0 ? $parameters[0] : true;

    return $this;
  }

  public function __get($key) {

    return $this->getAttribute($key);
  }

  public function __set($key, $value) {

    $this->setAttribute($key, $value);
  }

  public function __isset($key) {

    return !is_null($this->getAttribute($key));
  }

  public static function doRefreshRewrites() {

    $args = func_get_args();

    call_user_func_array('static::doRefreshRewriteTags', $args);
    call_user_func_array('static::doRefreshRewriteRules', $args);

    flush_rewrite_rules();
  }

  public static function doRefreshRewriteTags() {

    static::doAction('refreshRewriteTags', func_get_args());
  }

  public static function doRefreshRewriteRules() {

    static::doAction('refreshRewriteRules', func_get_args());
  }

  public static function make($attributes = []) {

    return new static($attributes);
  }

  public static function register() {

    $class = get_called_class();

    static::registerBaseHooks($class);
    static::registerHooks($class);
    static::registerClassHooks($class);
  }

  public static function registerHooks($class) {

    // Override to register actions and filters
  }

  public static function __callStatic($method, $parameters) {

    $instance = new static;

    return call_user_func_array(array($instance, $method), $parameters);
  }

  protected static function doAction($action, array $args = []) {

    static::doHook('do_action', $action, $args);
  }

  protected static function doFilter($filter, array $args = []) {

    return static::doHook('apply_filters', $filter, $args);
  }

  protected static function doHook($function, $hook, array $args = []) {

    if (function_exists($function) && has_filter($hook = implode('/', ['exp', $hook]))) {

      array_unshift($args, $hook);

      return call_user_func_array($function, $args);
    }

    return array_shift($args);
  }

  protected static function registerBaseHooks($class) {

    add_action('after_switch_theme', [__CLASS__, 'doRefreshRewrites'], PHP_INT_MAX, 0);
    add_action('delete_option_rewrite_rules', [__CLASS__, 'doRefreshRewrites'], PHP_INT_MAX, 0);
    add_action('init', [__CLASS__, 'doRefreshRewriteTags'], 10, 0);
    add_action('init', [__CLASS__, 'doRefreshRewriteRules'], 10, 0);
    add_action('switch_theme', 'flush_rewrite_rules', PHP_INT_MAX, 0);
  }

  protected static function registerClassHooks($class) {

    $method = explode('\\', $class);
    $method = end($method);
    $method = 'register' . $method . 'Hooks';

    if (method_exists($class, $method) && is_callable([$class, $method])) {

      forward_static_call([$class, $method], $class);
    }
  }
}
