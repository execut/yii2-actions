<?php
/**
 */

namespace execut\actions;

use iutbay\yii2kcfinder\KCFinder;
use yii\web\Application;

class Bootstrap extends \execut\yii\Bootstrap
{
    public $allowedRole = '@';
    public $kCFinderOptions = [];
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
        if ($app instanceof Application) {
            $this->registerKCFinderSessionSettings($app);
        }

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

    protected function registerKCFinderSessionSettings($app) {
        $app->on(Application::EVENT_BEFORE_REQUEST, function () use ($app) {
            ;
            $kcfOptions = array_merge(KCFinder::$kcfDefaultOptions, [
                'uploadURL' => '@web/upload',
                'access' => [
                    'files' => [
                        'upload' => true,
                        'delete' => false,
                        'copy' => false,
                        'move' => false,
                        'rename' => false,
                    ],
                    'dirs' => [
                        'create' => true,
                        'delete' => false,
                        'rename' => false,
                    ],
                ],
            ], $this->kCFinderOptions);
            if ($this->allowedRole !== false) {
                if (!(!$app->user->isGuest && $this->allowedRole === '@') && !$app->user->can($this->allowedRole)) {
                    $kcfOptions['disabled'] = true;
                }
            }

            $kcfOptions['uploadURL'] = \yii::getAlias($kcfOptions['uploadURL']);

            $app->session->set('KCFINDER', $kcfOptions);
        });
    }
}