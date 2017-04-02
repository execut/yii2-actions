<?php
/**
 */

namespace execut\actions;


use yii\base\BootstrapInterface;

class Bootstrap implements BootstrapInterface
{
    public function bootstrap($app)
    {
        $app = \yii::$app;
        if (!$app->hasModule('dynagrid')) {
            \yii::$app->setModule('dynagrid', [
                'class' => '\kartik\dynagrid\Module',
                'defaultPageSize' => 100,
                'maxPageSize' => 500,
                'dbSettings' => [
                    'tableName' => 'dynagrid_grids',
                ],
                'dbSettingsDtl' => [
                    'tableName' => 'dynagrid_settings',
                    'dynaGridIdAttr' => 'dynagrid_grid_id',
                ],
            ]);
        }

        if (!$app->hasModule('gridview')) {
            \yii::$app->setModule('gridview', [
                'class' => '\kartik\grid\Module',
            ]);
        }
    }
}