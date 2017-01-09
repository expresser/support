<?php

namespace Expresser\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Fluent as BaseFluent;

abstract class Fluent extends BaseFluent
{
    protected $fieldPrefix = '';

    private $cacheableAccessors = [];

    private $accessorsCache = [];

    public function __construct(array $attributes = [])
	{
        $this->cacheableAccessors = $this->getCacheableAccessors();

		$this->fill($attributes);
	}

    public function getCacheableAccessors()
    {
        return [];
    }

    public function fill(array $attributes)
    {
        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }

        return $this;
    }

    public function get($key, $default = null)
    {
        $value = $this->getAttribute($key);

        return is_null($value) ? $default : $value;
    }

    public function getAttribute($key)
    {
        $key = $this->transformKey($key);

        return $this->getAttributeValue($key);
    }

    public function transformKey($key)
    {
        return $key;
    }

    public function getAttributeValue($key)
    {
        $value = $this->getAttributeFromArray($key);

        if ($this->hasGetMutator($key)) {
            $value = $this->mutateAttribute($key, $value);
        }

        return $value;
    }

    public function getAttributeFromArray($key)
    {
        if (array_key_exists($key, $this->attributes)) {
            return $this->attributes[$key];
        } elseif (array_key_exists($this->getFieldPrefix().$key, $this->attributes)) {
            return $this->attributes[$this->getFieldPrefix().$key];
        }
    }

    public function getFieldPrefix()
    {
        return $this->fieldPrefix;
    }

    public function hasGetMutator($key)
    {
        return method_exists($this, 'get'.studly_case($key).'Attribute');
    }

    public function mutateAttribute($key, $value)
	{
        if (isset($this->accessorsCache[$key])) {
            return $this->accessorsCache[$key];
        }
        else {
            $value = $this->{'get'.studly_case($key).'Attribute'}($value);

            if (in_array($key, $this->cacheableAccessors)) {
                $this->accessorsCache[$key] = $value;
            }
        }

		return $value;
	}

    public function setAttribute($key, $value)
    {
        $key = $this->transformKey($key);

        if ($this->hasSetMutator($key)) {
            $this->{'set'.studly_case($key).'Attribute'}($value);
        } else {
            $this->attributes[$key] = $value;
        }
    }

    public function hasSetMutator($key)
    {
        return method_exists($this, 'set'.studly_case($key).'Attribute');
    }

    public function newCollection($items = [])
	{
		return new Collection($items);
	}

    public function toArray()
    {
        return array_map(function ($value) {
            return $value instanceof Arrayable ? $value->toArray() : $value;
        }, $this->attributes);
    }

    public function __get($key)
    {
        return $this->getAttribute($key);
    }

    public function __set($key, $value)
    {
        $this->setAttribute($key, $value);
    }

    public function __isset($key)
    {
        return !is_null($this->getAttribute($key));
    }

    public static function make($attributes = [])
    {
        return new static($attributes);
    }

    public static function register()
    {
        $class = get_called_class();

        static::registerBaseHooks($class);
        static::registerHooks($class);
        static::registerClassHooks($class);
    }

    public static function registerBaseHooks($class)
    {
        add_action('after_switch_theme', [__CLASS__, 'doRefreshRewrites'], PHP_INT_MAX, 0);
        add_action('delete_option_rewrite_rules', [__CLASS__, 'doRefreshRewrites'], PHP_INT_MAX, 0);
        add_action('init', [__CLASS__, 'doRefreshRewriteTags'], 10, 0);
        add_action('init', [__CLASS__, 'doRefreshRewriteRules'], 10, 0);
        add_action('switch_theme', 'flush_rewrite_rules', PHP_INT_MAX, 0);
    }

    public static function registerHooks($class)
    {
        // Override to register actions and filters
    }

    public static function registerClassHooks($class)
    {
        $method = explode('\\', $class);
        $method = end($method);
        $method = 'register'.$method.'Hooks';

        if (method_exists($class, $method) && is_callable([$class, $method])) {
            forward_static_call([$class, $method], $class);
        }
    }

    public static function doRefreshRewrites()
    {
        $args = func_get_args();

        call_user_func_array('static::doRefreshRewriteTags', $args);
        call_user_func_array('static::doRefreshRewriteRules', $args);

        flush_rewrite_rules();
    }

    public static function doRefreshRewriteTags()
    {
        static::doAction('refreshRewriteTags', func_get_args());
    }

    public static function doRefreshRewriteRules()
    {
        static::doAction('refreshRewriteRules', func_get_args());
    }

    protected static function doAction($action, array $args = [])
    {
        static::doHook('do_action', $action, $args);
    }

    protected static function doFilter($filter, array $args = [])
    {
        return static::doHook('apply_filters', $filter, $args);
    }

    protected static function doHook($function, $hook, array $args = [])
    {
        if (function_exists($function) && has_filter($hook = implode('/', ['exp', $hook]))) {
            array_unshift($args, $hook);

            return call_user_func_array($function, $args);
        }

        return array_shift($args);
    }
}
