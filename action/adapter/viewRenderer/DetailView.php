<?php
/**
 * User: execut
 * Date: 21.07.16
 * Time: 13:47
 */

namespace execut\actions\action\adapter\viewRenderer;


use execut\yii\helpers\ArrayHelper;
use execut\yii\helpers\Html;
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
    public $cancelButton = '<a class="btn btn-default" href="./">Вернуться к списку</a>';

    public function getDefaultWidgetOptions()
    {
        return [
            'class' => \kartik\detail\DetailView::class,
            'panel'=> false,
//            [
//                'heading'=> '',
//                'footer' => '&nbsp;{buttons}',
////                'type'=>\kartik\detail\DetailView::TYPE_PRIMARY,
//                'headingOptions' => [
//                    'template' => ''
//                ],
//                'footerOptions' => [
//                    'style' => 'height: 31px',
//                ],
//            ],
            'buttons1' => '',
            'buttons2' => $this->renderButtons(),
            'deleteOptions' => [
                'url' => Url::to([
                    $this->uniqueId . '/delete',
                    'id' => '{id}',
                ]),
                'kvdelete' => true
            ],
            'mainTemplate' => $this->renderAlertBlock() . '{detail}{buttons}',
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
     * Initializes and renders alert container block
     */
    protected function renderAlertBlock()
    {
        $session = \Yii::$app->session;
        $flashes = $session->getAllFlashes();
        $alertContainerOptions = [
            'style' => 'max-width:400px'
        ];
        if (count($flashes) === 0) {
            Html::addCssStyle($alertContainerOptions, 'display:none;');
        }
        $out = Html::beginTag('div', $alertContainerOptions);
        foreach ($flashes as $type => $message) {
            if (is_array($message)) {
                $message = implode('<br>', $message);
            }

            $alertWidgetOptions = [];
            $alertWidgetOptions['body'] = $message;
            $alertWidgetOptions['options'] = [
                'class' => ['alert', 'alert-success'],
                'style' => 'padding-left:10px;padding-right:10px;'
            ];
            $out .= "\n" . Alert::widget($alertWidgetOptions);
            $session->removeFlash($type);
        }

        $out .= "\n</div>";

        return $out;
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