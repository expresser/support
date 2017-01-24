<?php

namespace Expresser\Support;

use Expresser\Contracts\Support\Queryable;

abstract class Builder
{
    protected $query;

    protected $model;

    public function __construct(Queryable $query)
    {
        $this->setQuery($query);
    }

    public function get()
    {
        $models = $this->getModels();

        return $this->model->newCollection($models);
    }

    public function getModels()
    {
        $results = $this->query->execute();

        return $this->model->hydrate($results)->all();
    }

    public function getQuery()
    {
        return $this->query;
    }

    public function setQuery(Queryable $query)
    {
        $this->query = $query;

        return $this;
    }

    public function getModel()
    {
        return $this->model;
    }

    public function setModel(Model $model)
    {
        $this->model = $model;

        return $this;
    }

    public function __call($method, $parameters)
    {
        call_user_func_array([$this->query, $method], $parameters);

        return $this;
    }
}
