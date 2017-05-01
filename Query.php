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

        return $this;
    }

    public function getQueryVar($name)
    {
        $value = $this->getQueryVarValue($name);

        if ($this->hasGetMutator($name)) {
            $value = $this->mutateQueryVar($name, $value);
        }

        return $value;
    }

    public function getQueryVarValue($name)
    {
      if (isset($this->query->query_vars[$name])) {
          return $this->query->query_vars[$name];
      }
    }

    public function hasGetMutator($name)
    {
        return method_exists($this, 'get'.studly_case($name).'QueryVar');
    }

    public function mutateQueryVar($name, $value)
	{
        $value = $this->{'get'.studly_case($name).'QueryVar'}($value);

		return $value;
	}

    public function setQueryVar($name, $value)
    {
        if ($this->hasSetMutator($name)) {
            $this->{'set'.studly_case($name).'QueryVar'}($value);
        } else {
            $this->query->query_vars[$name] = $value;
        }

        return $this;
    }

    public function hasSetMutator($name)
    {
        return method_exists($this, 'set'.studly_case($name).'QueryVar');
    }

    public function hasQueryVar($name)
    {
        $queryVar = $this->getQueryVar($name);

        return !(is_null($queryVar) || empty($queryVar));
    }

    public function removeQueryVar($name)
    {
        if (isset($this->query->query_vars[$name])) {
            unset($this->query->query_vars[$name]);
        }

        return $this;
    }

    abstract public function execute();
}
