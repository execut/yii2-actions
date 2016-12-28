<?php
/**
 * Created by PhpStorm.
 * User: execut
 * Date: 12/21/16
 * Time: 4:59 PM
 */

namespace execut\actions\action\adapter\gridView;


use yii\base\Component;
use yii\data\ActiveDataProvider;

abstract class Handler extends Component
{
    /**
     * @var ActiveDataProvider
     */
    public $dataProvider = null;
    abstract public function run();
}