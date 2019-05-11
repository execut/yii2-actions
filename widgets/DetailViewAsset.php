<?php
/**
 * Created by PhpStorm.
 * User: execut
 * Date: 12/27/17
 * Time: 1:39 PM
 */

namespace execut\actions\widgets;


use execut\loadingOverlay\LoadingOverlayAsset;
use execut\yii\web\AssetBundle;
use yii\jui\JuiAsset;

class DetailViewAsset extends AssetBundle
{
    public $depends = [
        JuiAsset::class,
        LoadingOverlayAsset::class,
    ];
}