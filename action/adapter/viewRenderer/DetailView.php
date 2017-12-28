<?php
/**
 * User: execut
 * Date: 21.07.16
 * Time: 13:47
 */

namespace execut\actions\action\adapter\viewRenderer;


use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use kartik\alert\Alert;
use yii\helpers\Url;

class DetailView extends Widget
{
    public $uniqueId = null;
    public $model = null;
    public $mode = null;
    public $heading = 'Редактирование';
    public $action = null;
    public $buttonsTemplate = '{save}&nbsp;&nbsp;{apply}&nbsp;&nbsp;{cancel}';
    public $saveButton = '<input type="submit" name="save" value="Сохранить" class="btn btn-success" href="" title="Сохранить и вернуться">';
    public $applyButton = '<input type="submit" name="apply" value="Применить" class="btn btn-success" href="" title="Сохранить изменения">';
    public $cancelButton = '<a class="btn btn-default" href="{backUrl}">Вернуться к списку</a>';
    public function getDefaultWidgetOptions()
    {
        return [
            'class' => \execut\actions\widgets\DetailView::class,
            'uniqueId' => $this->uniqueId,
            'model' => $this->model,
            'mode' => $this->mode,
            'action' => $this->action,
            'buttonsTemplate' => $this->buttonsTemplate,
            'saveButton' => $this->saveButton,
            'applyButton' => $this->applyButton,
            'cancelButton' => $this->cancelButton,
        ];
    }
}