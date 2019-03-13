<?php
/**
 * User: execut
 * Date: 21.07.16
 * Time: 13:32
 */

namespace execut\actions\action\adapter\viewRenderer;


use execut\actions\action\adapter\helper\FormLoader;
use execut\actions\action\adapter\ViewRenderer;
use execut\actions\models\MassDelete;
use execut\actions\widgets\MassDeleteForm;

class MassHandler extends ViewRenderer
{
    public $model = null;
    public $filter = null;
    public $dataProvider = null;
    public $deletedCount = null;
    protected function _run() {
        return MassDeleteForm::widget([
            'model' => $this->model,
            'deletedCount' => $this->deletedCount,
        ]);
    }
}