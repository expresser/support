<?php namespace Expresser\Support;

use Illuminate\Support\Collection;

abstract class Builder {

  protected $model;

  public function getModels(array $models = []) {

    foreach ($models as &$model) {

      $model = $this->model->newFromQuery($model);
    }

    return Collection::make($models);
  }

  public function getModel() {

		return $this->model;
	}

  public function setModel(Model $model) {

		$this->model = $model;

		return $this;
	}

  public function __call($method, array $parameters) {

    if (strpos($method, 'where') === 0) {

      $method = lcfirst(str_replace('where', '', $method));

      return call_user_func_array(array($this, $method), $parameters);
    }
  }

  public abstract function get();
}
