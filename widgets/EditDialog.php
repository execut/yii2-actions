<?php
/**
 * Created by PhpStorm.
 * User: execut
 * Date: 12/26/17
 * Time: 4:15 PM
 */

namespace execut\actions\widgets;


use execut\yii\jui\Widget;
use yii\bootstrap\Modal;
use yii\helpers\ArrayHelper;

class EditDialog extends Widget
{
    public $model;
    public $uniqueId;
    public $alertId;
    public $toggleButtonOptions = false;
    public $modalOptions = [];
    public function run()
    {
        $this->clientOptions['alertId'] = $this->alertId;
        $this->registerWidget();

        echo $this->_beginContainer();
        Modal::begin(ArrayHelper::merge([
            'id' => $this->id . '-modal',
            'header' => $this->getHeader(),
            'options' => [
                'class' => 'pull-left'
            ],
            'toggleButton' => $this->getToggleButton(),
        ], $this->modalOptions));
        echo \execut\actions\widgets\DetailView::widget([
            'id' => $this->id . '-detail-view',
            'mode' => 'edit',
            'buttonsTemplate' => '{save}',
            'uniqueId' => $this->uniqueId,
            'model' => $this->model,
            'formOptions' => [
                'enableAjaxValidation' => true,
                'validateOnSubmit' => true,
            ],
        ]);

        Modal::end();
        echo $this->_endContainer();
    }

    public $header = null;
    public function getHeader() {
        return $this->header;
    }

    public function getToggleButton() {
        if ($this->toggleButtonOptions === false) {
            return false;
        }

        return ArrayHelper::merge([
            'label' => $this->getHeader(),
            'class' => 'btn btn-default',
        ], $this->toggleButtonOptions);
    }
}