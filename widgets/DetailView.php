<?php
/**
 * Created by PhpStorm.
 * User: execut
 * Date: 12/26/17
 * Time: 3:37 PM
 */

namespace execut\actions\widgets;


use execut\widgets\LoadingOverlay;
use execut\yii\jui\WidgetTrait;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\alert\Alert;

class DetailView extends \kartik\detail\DetailView
{
    use WidgetTrait;
    public $uniqueId = null;
    public $action = null;
    public $buttonsTemplate = '{save}&nbsp;&nbsp;{apply}&nbsp;&nbsp;{cancel}';
    public $saveButton = '<input type="submit" name="save" value="Сохранить" class="btn btn-success" href="" title="Сохранить и вернуться">';
    public $applyButton = '<input type="submit" name="apply" value="Применить" class="btn btn-success" href="" title="Сохранить изменения">';
    public $cancelButton = '<a class="btn btn-default" href="{backUrl}">Вернуться к списку</a>';
    public $backUrl = null;

    public function __construct($config = [])
    {
        $config = ArrayHelper::merge([
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
            'buttonContainer' => [
                'class' => 'pull-right',
            ],
            'mainTemplate' => $this->renderAlertBlock() . '{detail}<div style="height:28px;width:0px;display: inline-block"></div>{buttons}',
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
                'enableAjaxValidation' => false,
                'validateOnChange' => false,
                'validateOnSubmit' => false,
                'validateOnBlur' => false,
                'options' => [
                    'enctype'=>'multipart/form-data',
                ],
            ],
        ], $config);

        parent::__construct($config);
    }

    public function run() {
        echo LoadingOverlay::widget();

        $this->_registerBundle();
        parent::registerPlugin('DetailView');
        echo $this->_beginContainer();
        $r = parent::run();
        echo $this->_endContainer();

        return $r;
    }

    public function init()
    {
        $this->buttons2 = $this->renderSubmitButtons();
        $this->attributes = $this->model->getFormFields();
        $this->formOptions['action'] = $this->getAction();
        $this->deleteOptions = [
            'url' => Url::to([
                $this->uniqueId . '/delete',
                'id' => $this->model->primaryKey,
            ]),
            'kvdelete' => true
        ];
        $this->formOptions['validationUrl'] = $this->getAction();

        parent::init();
    }

    protected function getAction() {
        if ($this->action !== null) {
            return $this->action;
        }

        return Url::to([
            $this->uniqueId . '/update',
            'id' => $this->model->primaryKey,
        ]);
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
    public function renderSubmitButtons()
    {
        $cancelButton = $this->cancelButton;
        $backUrl = $this->backUrl;
        if ($backUrl === null) {
            $backUrlParts = explode('/', $this->uniqueId);
            unset($backUrlParts[count($backUrlParts) - 1]);
            $backUrl = [implode('/', $backUrlParts)];
        }
        if (is_array($backUrl)) {
            $backUrl = Url::to($backUrl);
        }

        $cancelButton = strtr($cancelButton, [
            '{backUrl}' => $backUrl,
        ]);

        return strtr($this->buttonsTemplate, [
            '{save}' => $this->saveButton,
            '{apply}' => $this->applyButton,
            '{cancel}' => $cancelButton,
        ]);
    }
}