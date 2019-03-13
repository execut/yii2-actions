<?php
/**
 * Created by PhpStorm.
 * User: execut
 * Date: 3/13/19
 * Time: 4:15 PM
 */

namespace execut\actions\widgets;


use execut\yii\helpers\Html;
use execut\yii\jui\Widget;

class MassHandlerButton extends Widget
{
    public $url = null;
    public $model = null;
    public $gridId = null;
    public function run()
    {
        parent::run();
        $this->clientOptions['gridSelector'] = '#' . $this->gridId;
        $this->clientOptions['idAttribute'] = $this->model->formName() . '[' . current($this->model->primaryKey()) . ']';
        $this->registerWidget();
        return $this->_renderContainer(Html::a('', $this->url, [
            'class' => 'btn btn-danger glyphicon glyphicon-trash'
        ]));
    }
}