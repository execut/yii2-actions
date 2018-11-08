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
use \execut\actions\widgets\DetailView as DetailViewWidget;

class DetailView extends Widget
{
    public $uniqueId = null;
    public $model = null;
    public $mode = null;
    public $heading = 'Редактирование';
    public $action = null;
    public $buttonsTemplate = \execut\actions\widgets\DetailView::DEFAULT_BUTTONS_TEMPLATE;
    public $saveButton = DetailViewWidget::BUTTON_SAVE;
    public $checkButton = DetailViewWidget::BUTTON_CHECK;
    public $applyButton = DetailViewWidget::BUTTON_APPLY;
    public $cancelButton = DetailViewWidget::BUTTON_CANCEL;
    public function getDefaultWidgetOptions()
    {
        return [
            'class' => DetailViewWidget::class,
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