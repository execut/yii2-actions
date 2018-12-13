<?php
/**
 * Created by PhpStorm.
 * User: execut
 * Date: 2/21/18
 * Time: 2:44 PM
 */

namespace execut\actions\widgets;


use execut\yii\jui\WidgetTrait;
use kartik\alert\Alert;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Inflector;
use yii\helpers\Json;
use yii\helpers\Url;
use kartik\base\Config;

class GridView extends \kartik\grid\GridView
{
    use WidgetTrait;
    public $addButtonUrl = null;
    public $updateUrl = null;
    public $title = null;
    public $isAjaxCrud = false;
    public $formModel = null;
    public $uniqueId = null;
    public $hover = true;
    public $responsiveWrap = false;
    public function init()
    {
        $this->toolbar = $this->getToolbarConfig();
        $rowOptions = function ($row) {
            if ($this->isAjaxCrud) {
                $attributes = Json::encode($row->attributes);
            } else {
                $attributes = [];
            }

            return [
                'class' => 'link-row',
                'id' => $row->formName() . '-' . $row->primaryKey,
                'data-id' => $row->primaryKey,
                'attributes' => $attributes,
            ];
        };

        if (is_callable($this->rowOptions)) {
            $oldRowOptions = $this->rowOptions;
            $this->rowOptions = function ($row) use ($oldRowOptions, $rowOptions) {
                $oldData = $oldRowOptions($row);
                if ($oldData === null) {
                    $oldData = [];
                }

                return ArrayHelper::merge($rowOptions($row), $oldData);
            };
        } else if (is_array($this->rowOptions)) {
            $oldRowOptions = $this->rowOptions;
            $this->rowOptions = function ($row) use ($oldRowOptions, $rowOptions) {
                return ArrayHelper::merge($rowOptions($row), $oldRowOptions);
            };
        } else {
            $this->rowOptions = $rowOptions;
        }

        return parent::init();
    }

    protected function getUpdateUrl() {
        if ($this->updateUrl !== null) {
            return $this->updateUrl;
        }

        return $this->addButtonUrl;
    }

    /**
     * Registers a specific jQuery UI widget options
     * @param string $name the name of the jQuery UI widget
     * @param string $id the ID of the widget
     */
    protected function registerWidget($name = null, $id = null)
    {
        if ($name === null) {
            $name = $this->getDefaultJsWidgetName();
        }

        $this->_registerBundle();

        if (!$this->isAjaxCrud && $this->getUpdateUrl()) {
            if ($id === null) {
                $id = $this->options['id'];
            }

            $options = empty($this->clientOptions) ? '' : Json::htmlEncode([
                'updateUrl' => Url::to($this->getUpdateUrl())
            ]);
            $js = "jQuery('#$id').$name($options);";
            $this->getView()->registerJs($js);
        }
    }

    public function run()
    {
        $this->registerWidget();
        parent::run();
    }

    /**
     * @param $refreshUrlParams
     * @return array
     */
    public function getToolbarConfig(): array
    {
        if ($this->toolbar === false) {
            return [];
        }
//        $refreshUrlParams = [
//            $this->adapter->uniqueId,
//        ];
//
//        foreach ($this->refreshAttributes as $key) {
//            if (!empty($this->adapter->actionParams->get[$key])) {
//                $refreshUrlParams[$key] = $this->adapter->actionParams->get[$key];
//            }
//        }
        if (!is_array($this->toolbar)) {
            if (empty($this->toolbar)) {
                $this->toolbar = [];
            } else {
                $this->toolbar = [$this->toolbar];
            }
        }

        return ArrayHelper::merge([
//            'massEdit' => ['content' => $this->renderMassEditButton()],
//            'massVisible' => ['content' => $this->renderVisibleButtons()],
            'add' => ['content' => $this->renderAddButton()],
            'alert' => ['content' => $this->renderAlertBlock()],
//            'refresh' => [
//                'content' => Html::a('<i class="glyphicon glyphicon-repeat"></i>', $refreshUrlParams, ['data-pjax' => 0, 'class' => 'btn btn-default', 'title' => 'Reset Grid']),
//            ],
//            'dynaParams' => ['content' => '{dynagridFilter}{dynagridSort}{dynagrid}'],
//            'toggleData' => '{toggleData}',
//            'export' => '{export}',
        ], $this->toolbar);
    }

    public function beginPjax() {
        parent::beginPjax();
        if ($this->isAjaxCrud) {
            $model = $this->getFormModel();

            if ($this->pjax) {
                $gridId = $this->id . '-pjax';
            } else {
                $gridId = null;
            }

            echo EditDialog::widget([
                'id' => $this->id . '-edit',
                'model' => $model,
                'uniqueId' => $this->uniqueId,
                'alertId' => $this->id . '-alert',
                'clientOptions' => [
                    'inputsPrefix' => str_replace('-', '', Inflector::camel2id($model->formName())),
                    'editButtons' => 'tr[data-key]',
                    'gridId' => $gridId,
                ],
                'toggleButtonOptions' => false,
            ]);
        }
    }

    public function renderAlertBlock() {
        $alertWidgetOptions = [
            'id' => $this->id . '-alert',
        ];

        $alertWidgetOptions['body'] = '<span></span>';
        $alertWidgetOptions['options'] = [
            'class' => ['alert', 'alert-success'],
            'style' => 'padding-left:10px;padding-right:10px;display:none;'
        ];

        return Alert::widget($alertWidgetOptions);
    }

    /**
     * @return string
     */
    protected function renderAddButton()
    {
        $title = \yii::t('execut.actions', 'Add') . ' ' . $this->title;
        $url = $this->addButtonUrl;
        if (!is_array($url)) {
            $url = [$url];
        }


        return Html::a($title, Url::to($url), [
                'id' => $this->id . '-edit-add-button',
                'type' => 'button',
                'data-pjax' => 0,
                'title' => $title,
                'class' => 'btn btn-success'
            ]);
    }

    /**
     * @return null
     */
    protected function getFormModel()
    {
        $model = $this->formModel;
        if (is_callable($model)) {
            $model = $model();
        }
        return $model;
    }
}