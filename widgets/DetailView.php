<?php
/**
 * Created by PhpStorm.
 * User: execut
 * Date: 12/26/17
 * Time: 3:37 PM
 */

namespace execut\actions\widgets;


use execut\crudFields\fields\Field;
use execut\crudFields\fields\reloader\Reloader;
use execut\crudFields\fields\reloader\Target;
use execut\crudFields\fields\reloader\Periodically;
use execut\loadingOverlay\LoadingOverlay;
use execut\yii\jui\WidgetTrait;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\alert\Alert;
use yii\web\Response;
use yii\web\View;

class DetailView extends \kartik\detail\DetailView
{
    use WidgetTrait;
    const DEFAULT_BUTTONS_TEMPLATE = '{save}&nbsp;&nbsp;{apply}&nbsp;&nbsp;{cancel}';
    const All_BUTTONS_TEMPLATE = '{check}&nbsp;&nbsp;{save}&nbsp;&nbsp;{apply}&nbsp;&nbsp;{cancel}';
    public $uniqueId = null;
    public $action = null;

    /**
     * @var Model
     */
    public $model;
    public $buttonsTemplate = self::DEFAULT_BUTTONS_TEMPLATE;
    public $saveButton = self::BUTTON_SAVE;
    public $checkButton = self::BUTTON_CHECK;
    public $applyButton = self::BUTTON_APPLY;
    public $cancelButton = self::BUTTON_CANCEL;
    public $backUrl = null;
    public $alertBlockAddon = null;
    public $isFloatedButtons = true;
    public $reloadedAttributes = [];

    const BUTTON_CANCEL = '<input type="submit" name="cancel" value="Отмена" class="btn btn-default" title="Отменить" onclick="$(this).parents(\'form\').data(\'yiiActiveForm\').validated = true">';
    const BUTTON_APPLY = '<input type="submit" name="apply" value="Применить" class="btn btn-primary" href="" title="Сохранить изменения">';
    const BUTTON_CHECK = '<input type="submit" name="check" value="Проверить" class="btn btn-info" href="" title="Проверить">';
    const BUTTON_SAVE = '<input type="submit" name="save" value="Отправить" class="btn btn-primary" href="" title="Сохранить и вернуться">';

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
                'class' => 'buttons-container',
            ],
            'bordered' => true,
            'striped' => true,
            'condensed' => true,
            'responsive' => false,
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

    protected function checkReloadedAttributes() {
        $request = \yii::$app->request;
        if (!$request->isAjax || !$request->getQueryParam('refreshedAttributes')) {
            return;
        }

        $response = \Yii::$app->getResponse();
        $response->clearOutputBuffers();
        $response->setStatusCode(200);
        $response->format = Response::FORMAT_JSON;
        $attributes = $request->getQueryParam('refreshedAttributes');
        $reloadedAttributes = [];
        foreach ($attributes as $attribute) {
            $reloadedAttributes[$attribute] = $this->attributes[$attribute];
        }

        $rows = [];
        $view = $this->getView();
        foreach ($attributes as $attribute) {
            $view->js = [];
            $view->jsFiles = [];
            $renderedRow = [
                'html' => $this->renderAttributeRow($reloadedAttributes[$attribute])
            ];
            ob_start();
            $view->endBody();
            ob_end_clean();

            if (!empty($view->js[View::POS_READY])) {
                $renderedRow['js'] = implode('', $view->js[View::POS_READY]);
                $renderedRow['html'] .= implode('', $view->jsFiles[View::POS_END]);
            }

            $rows[$attribute] = $renderedRow;

            $data = [
                'rows' => $rows
            ];
        }

        $response->data = $data;

        \Yii::$app->end();
    }

    public function run() {
        $this->checkReloadedAttributes();
        $this->mainTemplate = $this->renderAlertBlock() . ((!$this->isFloatedButtons && !empty($this->attributes) && count($this->attributes) > 8) ? '{buttons}' : '') . '{detail}{buttons}';

        $this->_registerBundle();
        $this->pluginOptions['isFloatedButtons'] = $this->isFloatedButtons;
        $this->pluginOptions['reloadedAttributes'] = $this->reloadedAttributes;

        $this->pluginOptions['refreshedRows'] = $this->getRefreshedRows();
        $this->pluginOptions['fields'] = $this->getFieldsJsParams();

        parent::registerPlugin('DetailView');
        echo $this->_beginContainer();
        $r = parent::run();
        echo $this->_endContainer();

        return $r;
    }

    protected function getFieldsJsParams() {
        $params = [];
        foreach ($this->getFields() as $field) {
            $name = $field->getName();
            $param = [
                'name' => $name,
                'rowSelector' => '.' . $this->getRowClass($name),
            ];
            $attribute = $field->getAttribute();
            if ($attribute) {
                $param['fieldSelector'] = '#' . Html::getInputId($this->model, $attribute);
            }
            $params[$name] = $param;
        }
        return $params;
    }

    protected function getRefreshedRows() {
        $result = [];
        /**
         * @var Field $field
         */
        foreach ($this->getFields() as $key => $field) {
            $reloaders = $field->getReloaders();
            foreach ($reloaders as $reloader) {
                $name = $field->getName();
                if (empty($name)) {
                    throw new Exception('Name for field with key ' . $key . ' is required');
                }
                $reloaderOptions = [
                    'refreshType' => $reloader->getKey(),
                    'name' => $name,
                ];

                $targets = $reloader->getTargets();
                $targetsOptions = [];
                /**
                 * @var Target $target
                 */
                foreach ($targets as $target) {
                    $targetOptions = [
                        'name' => $target->getField()->getName(),
                    ];

                    $values = $target->getValues();
                    if (!empty($values)) {
                        $targetOptions['values'] = $values;
                    }

                    $whenIsEmpty = $target->getWhenIsEmpty();
                    if ($whenIsEmpty !== null) {
                        $targetOptions['whenIsEmpty'] = $whenIsEmpty;
                    }

                    $targetsOptions[] = $targetOptions;
                }

                $reloaderOptions['watchedInputs'] = $targetsOptions;
                $result[] = $reloaderOptions;
            }
        }

        return $result;
    }

    protected function getRowClass($attribute) {
        return 'row-' . $attribute;
    }

    public function init()
    {
        $this->attributes = $this->model->getFormFields();
        foreach ($this->attributes as $attribute => &$options) {
            if (empty($options['rowOptions'])) {
                $options['rowOptions'] = [];
            }

            Html::addCssClass($options['rowOptions'], $this->getRowClass($attribute));
        }

        if (!array_key_exists('action', $this->formOptions)) {
            $this->formOptions['action'] = $this->getAction();
        }

        $urlParams = [
            $this->uniqueId . '/delete',
        ];

        if ($this->model instanceof ActiveRecord) {
            $urlParams['id'] = $this->model->primaryKey;
        }

        $this->deleteOptions = [
            'url' => Url::to($urlParams),
            'kvdelete' => true
        ];
        $this->formOptions['validationUrl'] = $this->getAction();

        parent::init();
    }

    protected function getAction() {
        if ($this->action !== null) {
            return $this->action;
        }

        $urlParams = [
            $this->uniqueId . '/update',
        ];

        if ($this->model instanceof ActiveRecord) {
            $urlParams['id'] = $this->model->primaryKey;
        }

        return Url::to($urlParams);
    }

    public function runWidget()
    {
        if (!$this->isFloatedButtons) {
            $this->buttons1 = $this->renderSubmitButtons();
        }

        $this->buttons2 = $this->renderSubmitButtons();
        parent::runWidget(); // TODO: Change the autogenerated stub
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

        return $this->alertBlockAddon . $out;
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


        $buttonsTemplate = $this->buttonsTemplate;
        if (is_callable($buttonsTemplate)) {
            $buttonsTemplate = call_user_func($buttonsTemplate, $this->model);
        }

        return strtr($buttonsTemplate, [
            '{check}' => $this->checkButton,
            '{save}' => $this->saveButton,
            '{apply}' => $this->applyButton,
            '{cancel}' => $cancelButton,
        ]);
    }

    /**
     * @return mixed
     */
    protected function getFields()
    {
        return $this->model->getFields();
    }
}