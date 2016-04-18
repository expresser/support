<?php namespace Expresser\Support;

abstract class Query extends Builder {

  protected $query;

  public function __construct($query) {

    $this->query = $query;
  }

  public function getParameter($name) {

    return $this->getParameterValue($name);
  }

  public function getParameterValue($name) {

    $value = $this->query->get($name);

    if (!empty($value)) return $value;
  }

  public function setParameter($name, $value) {

    $this->query->set($name, $value);
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
