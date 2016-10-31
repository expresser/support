<?php

namespace Expresser\Support;

use Themosis\Facades\Config;

class ModelRegistrationServiceProvider extends \Themosis\Core\IgniterService {

    public function ignite()
    {
        $models = Config::get('registration');

        $this->register($models['options']);
        $this->register($models['roles']);
        $this->register($models['users']);
        $this->register($models['taxonomies']);
        $this->register($models['posttypes']);
    }

    private function register($models)
    {
        foreach ($models as $model) {
            $this->registerModel($model);
        }
    }

    private function registerModel($model)
    {
        call_user_func([$model, 'register']);
    }
}
