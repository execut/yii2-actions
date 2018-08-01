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
    public $buttonsTemplate = \execut\actions\widgets\DetailView::DEFAULT_BUTTONS_TEMPLATE;
    public $saveButton = '<input type="submit" name="save" value="Сохранить" class="btn btn-primary" href="" title="Сохранить и вернуться">';
    public $checkButton = '<input type="submit" name="check" value="Проверить" class="btn btn-primary" href="" title="Проверить">';
    public $applyButton = '<input type="submit" name="apply" value="Применить" class="btn btn-primary" href="" title="Применить изменения">';
    public $cancelButton = '<a class="btn btn-default" href="{backUrl}">Отмена</a>';
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
            'checkButton' => $this->checkButton,
            'applyButton' => $this->applyButton,
            'cancelButton' => $this->cancelButton,
        ];
    }
}