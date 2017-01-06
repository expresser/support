<?php

namespace Expresser\Support;

abstract class Model extends Fluent
{
    public $exists = false;

    protected $original = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->syncOriginal();
    }

    public function syncOriginal()
    {
        $this->original = $this->attributes;

        return $this;
    }

    public static function hydrate(array $items)
	{
		$instance = (new static);

		return $instance->newCollection(array_map(function ($item) use ($instance) {
			return $instance->newFromBuilder($item);
		}, $items));
	}

    public function newFromBuilder($attributes = [])
    {
        $model = $this->newInstance($attributes, true);

        return $model;
    }

    public function newInstance($attributes = [], $exists = false)
    {
        $model = new static($attributes);

        $model->exists = $exists;

        return $model;
    }

    public function getDirty()
    {
        $dirty = [];

        foreach ($this->attributes as $key => $value) {
            if (!array_key_exists($key, $this->original) || $value !== $this->original[$key]) {
                $dirty[$key] = $value;
            }
        }

        return $dirty;
    }

    public static function query()
    {
        return (new static())->newQuery();
    }

    abstract public function newQuery();
}
