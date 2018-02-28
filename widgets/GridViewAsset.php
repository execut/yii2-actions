<?php
/**
 * Created by PhpStorm.
 * User: execut
 * Date: 11/15/17
 * Time: 12:24 PM
 */

namespace execut\actions\widgets;


use execut\yii\web\AssetBundle;
use yii\jui\JuiAsset;

class GridViewAsset extends AssetBundle
{
    public $depends = [
        JuiAsset::class,
    ];
}