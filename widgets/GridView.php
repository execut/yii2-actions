<?php
/**
 * Created by PhpStorm.
 * User: execut
 * Date: 2/21/18
 * Time: 2:44 PM
 */

namespace execut\actions\widgets;


use kartik\alert\Alert;
use yii\helpers\Html;
use yii\helpers\Inflector;
use yii\helpers\Json;
use yii\helpers\Url;
use kartik\base\Config;

class GridView extends \kartik\grid\GridView
{
    public $addButtonUrl = null;
    public $title = null;
    public $isAjaxCrud = false;
    public $formModel = null;
    public $uniqueId = null;
    public function init()
    {
        $this->toolbar = $this->getToolbarConfig();
        $this->rowOptions = function ($row) {
            return [
                'attributes' => Json::encode($row->attributes),
            ];
        };

        return parent::init();
    }

    public function run()
    {
        $this->initToggleData();
        $this->initExport();
        if ($this->export !== false && isset($this->exportConfig[self::PDF])) {
            Config::checkDependency(
                'mpdf\Pdf',
                'yii2-mpdf',
                'for PDF export functionality. To include PDF export, follow the install steps below. If you do not ' .
                "need PDF export functionality, do not include 'PDF' as a format in the 'export' property. You can " .
                "otherwise set 'export' to 'false' to disable all export functionality"
            );
        }
        $this->initHeader();
        $this->initBootstrapStyle();
        $this->containerOptions['id'] = $this->options['id'] . '-container';
        Html::addCssClass($this->containerOptions, 'kv-grid-container');
        $this->registerAssets();
        $this->renderPanel();
        $this->beginPjax();
        $this->initLayout();
        parent::run();
        $this->endPjax();
    }

    /**
     * @param $refreshUrlParams
     * @return array
     */
    public function getToolbarConfig(): array
    {
//        $refreshUrlParams = [
//            $this->adapter->uniqueId,
//        ];
//
//        foreach ($this->refreshAttributes as $key) {
//            if (!empty($this->adapter->actionParams->get[$key])) {
//                $refreshUrlParams[$key] = $this->adapter->actionParams->get[$key];
//            }
//        }

        return [
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
        ];
    }

    public function beginPjax() {
        parent::beginPjax();
        if ($this->isAjaxCrud) {
            $model = $this->formModel;
            if (is_callable($model)) {
                $model = $model();
            }

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
                    'attributesElement' => 'tr:first',
                    'editButtons' => '.btn.update',
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
            'style' => 'padding-left:10px;padding-right:10px;'
        ];

        return Alert::widget($alertWidgetOptions);
    }

    /**
     * @return string
     */
    protected function renderAddButton()
    {
        $title = \yii::t('execut.actions', 'Add') . ' ' . $this->title;
        return Html::a($title, Url::to($this->addButtonUrl), [
                'id' => $this->id . '-edit-add-button',
                'type' => 'button',
                'data-pjax' => 0,
                'title' => $title,
                'class' => 'btn btn-success'
            ]);
    }
}