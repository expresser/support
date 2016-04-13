<?php namespace Expresser\Support;

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

  public abstract function get();
}
