<?php
/**
 * Created by PhpStorm.
 * User: execut
 * Date: 2/7/19
 * Time: 11:46 AM
 */

namespace execut\actions\widgets;

use execut\yii\jui\Widget;
use execut\yii\web\AssetBundle;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\jui\JuiAsset;
use yii\web\JsExpression;

class HandlersButtonAsset extends AssetBundle
{
    public $depends = [
        JuiAsset::class,
    ];
}