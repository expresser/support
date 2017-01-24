<?php

namespace Expresser\Support;

use InvalidArgumentException;
use Expresser\Contracts\Support\Queryable;

abstract class Query implements Queryable
{
    protected $query;

    public function __construct($query)
    {
        $this->setQuery($query);
    }

    public function getQuery()
    {
        return $this->query;
    }

    public function setQuery($query)
    {
        if (!property_exists($query, 'query_vars')) {
            throw new InvalidArgumentException('$query not a valid type of WordPress Query');
        }

        $this->query = $query;

        if (!is_array($this->query->query_vars)) {
            $this->query->query_vars = [];
        }

        $this->initQueryVars();

        return $this;
    }

    public function getQueryVar($name)
    {
      if (isset($this->query->query_vars[$name])) {
          return $this->query->query_vars[$name];
      }
    }

    public function setQueryVar($name, $value)
    {
        $this->query->query_vars[$name] = $value;

        return $this;
    }

    public function hasQueryVar($name)
    {
        $queryVar = $this->getQueryVar($name);

        return !(is_null($queryVar) && empty($queryVar));
    }

    protected function initQueryVars()
    {
    }

    abstract public function execute();
}
