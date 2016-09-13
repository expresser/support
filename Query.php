<?php namespace Expresser\Support;

abstract class Query extends Builder {

  protected $query;

  protected $params = [];

  public function __construct($query) {

    $this->query = $query;
  }

  public function get() {

    $models = $this->query->query($this->params);

    return $this->getModels($models);
  }

  public function getParameter($name) {

    return $this->getParameterValue($name);
  }

  public function getParameterValue($name) {

    if (isset($this->params[$name])) {

      return $this->params[$name];
    }
  }

  public function setParameter($name, $value) {

    $this->params[$name] = $value;
  }

  public function __get($name) {

    return $this->getParameter($name);
  }

  public function __set($name, $value) {

    $this->setParameter($name, $value);
  }

  public function __isset($name) {

    return is_null($this->getParameter($name)) === false;
  }
}
