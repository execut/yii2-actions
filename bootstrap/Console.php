<?php
/**
 */

namespace execut\actions\bootstrap;

use yii\base\BootstrapInterface;
use yii\console\controllers\MigrateController;
use yii\helpers\ArrayHelper;

class Console implements BootstrapInterface
{
    public function bootstrap($app)
    {
        if (empty($app->controllerMap['migrate'])) {
            $app->controllerMap['migrate'] = [];
        }

        $app->controllerMap['migrate'] = ArrayHelper::merge([
            'class' => MigrateController::class,
            'migrationNamespaces' => ['kartik' => 'kartik\dynagrid\migrations'],
        ], $app->controllerMap['migrate']);
    }
}