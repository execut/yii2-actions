<?php
$params = [];

defined('YII_DEBUG') or define('YII_DEBUG', true);

defined('YII_ENV') or define('YII_ENV', 'test');

return [
    'id' => 'app-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log', \execut\actions\Bootstrap::class],
    'modules' => [],
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
//        'cache' => [
//            'class' => 'yii\caching\FileCache',
//        ],
        'i18n' => [
            'class' => \yii\i18n\I18N::class,
//            'translations' => [
//                'app'=>[
//                    'class' => 'yii\i18n\PhpMessageSource',
//                    'basePath' => "@app/messages",
//                    'sourceLanguage' => 'en_US',
//                    'fileMap' => [
//                        'app'=>'app.php',
//                    ]
//                ],
//                'common.modules.catalog.models' => [
//                    'class' => 'yii\i18n\PhpMessageSource',
//                    'basePath' => '@app/modules/catalog/messages',
//                    'sourceLanguage' => 'en',
//                    'fileMap' => [
//                        'common.modules.catalog.models' => 'models.php',
//                    ],
//                ],
//            ],
        ],
    ],
    'params' => $params,
];
