<?php

namespace Expresser\Support;

use Expresser\Contracts\Support\Queryable;

abstract class Query implements Queryable
{
    protected $query;

    protected $params = [];

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function execute()
    {
        $results = $this->query->query($this->params);

        return $results;
    }

    public function getParameter($name)
    {
        return $this->getParameterValue($name);
    }

    public function getParameterValue($name)
    {
        if (isset($this->params[$name])) {
            return $this->params[$name];
        }
    }

    public function setParameter($name, $value)
    {
        $this->params[$name] = $value;
    }

    public function __get($name)
    {
        return $this->getParameter($name);
    }

    public function __set($name, $value)
    {
        $this->setParameter($name, $value);
    }

    public function __isset($name)
    {
        return is_null($this->getParameter($name)) === false;
    }
}
