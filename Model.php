<?php namespace Expresser\Support;

abstract class Model extends Fluent {

  public $exists = false;

  protected $original = [];

  public function __construct(array $attributes = []) {

    parent::__construct($attributes);

    $this->syncOriginal();
  }

  public function getDirty() {

		$dirty = [];

		foreach ($this->attributes as $key => $value) {

			if (!array_key_exists($key, $this->original) || $value !== $this->original[$key]) {

				$dirty[$key] = $value;
			}
		}

		return $dirty;
	}

  public function newFromQuery($attributes = []) {

    return $this->newInstance($attributes, true);
  }

  public function newInstance($attributes = [], $exists = false) {

    $model = new static($attributes);

    $model->exists = $exists;

		return $model;
  }

  public function syncOriginal() {

		$this->original = $this->attributes;

		return $this;
	}

  public function __call($method, $parameters) {

    $query = $this->newQuery();

    return call_user_func_array([$query, $method], $parameters);
  }

  public static function query() {

    return (new static)->newQuery();
  }

  public abstract function newQuery();
}
