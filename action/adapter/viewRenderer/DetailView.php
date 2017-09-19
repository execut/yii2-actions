<?php
/**
 * User: execut
 * Date: 21.07.16
 * Time: 13:47
 */

namespace execut\actions\action\adapter\viewRenderer;


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
    public $cancelButton = '<a class="btn btn-default" href="./">Вернуться к списку</a>';

    public function getDefaultWidgetOptions()
    {
        return [
            'class' => \kartik\detail\DetailView::className(),
            'panel'=>[
                'heading'=> $this->heading,
                'footer' => '&nbsp;{buttons}',
                'type'=>\kartik\detail\DetailView::TYPE_PRIMARY,
                'headingOptions' => [
                    'template' => '{title}'
                ],
                'footerOptions' => [
                    'style' => 'height: 31px',
                ],
            ],
            'buttons1' => '',
            'buttons2' => $this->renderButtons(),
            'deleteOptions' => [
                'url' => Url::to([
                    $this->uniqueId . '/delete',
                    'id' => '{id}',
                ]),
            ],
            'mainTemplate' => '{detail}',
            'model' => $this->model,
            'mode' => $this->mode,
            'bordered' => true,
            'striped' => true,
            'condensed' => true,
            'responsive' => true,
            'hideIfEmpty' => true,
            'hover' => true,
//                        'hAlign'=> true,
//                        'vAlign'=> true,
//                        'fadeDelay'=> 2000,
            'deleteOptions' => [ // your ajax delete parameters
                'params' => ['id' => 1000, 'kvdelete'=>true],
            ],
//                        'container' => ['id'=>'kv-demo'],
            'formOptions' => [
                'action' => $this->action,
                'options' => [
                    'enctype'=>'multipart/form-data',
                ],
            ],
            'attributes' => $this->model->getFormFields(),
        ];
    }

    /**
     * @param $cancelUrl
     * @return string
     */
    public function renderButtons()
    {
        return strtr($this->buttonsTemplate, [
            '{save}' => $this->saveButton,
            '{apply}' => $this->applyButton,
            '{cancel}' => $this->cancelButton,
        ]);
    }
}