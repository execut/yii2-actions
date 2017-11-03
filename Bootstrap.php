<?php
/**
 */

namespace execut\actions;

class Bootstrap extends \execut\yii\Bootstrap
{
    public function getDefaultDepends()
    {
        return [
            'modules' => [
                'dynagrid' => [
                    'class' => '\kartik\dynagrid\Module',
                    'defaultPageSize' => 100,
                    'maxPageSize' => 500,
                    'dbSettings' => [
                        'tableName' => 'dynagrid',
                    ],
                    'dbSettingsDtl' => [
                        'tableName' => 'dynagrid_dtl',
                    ],

                ],
                'gridview' => [
                    'class' => '\kartik\grid\Module',
                ],
                'actions' => [
                    'class' => Module::class,
                ],
            ],
        ];
    }

    public function bootstrap($app)
    {
        parent::bootstrap($app);

        self::initI18N();
    }



    public static function initI18N() {
        \yii::setAlias('@execut', '@vendor/execut');
        if (\Yii::$app->i18n) {
            \Yii::$app->i18n->translations['execut.actions'] = [
                'class' => 'yii\i18n\PhpMessageSource',
                'sourceLanguage' => 'en-US',
                'basePath' => '@execut/yii2-actions/messages',
                'fileMap' => [
                    'execut.actions' => 'actions.php',
                ],
            ];
        }
    }
}