<?php
/**
 * User: execut
 * Date: 21.07.16
 * Time: 13:32
 */

namespace execut\actions\action\adapter\viewRenderer;


use execut\yii\helpers\Html;
use yii\bootstrap\Alert;
use yii\helpers\Url;

class DynaGrid extends Widget
{
    public $title = null;
    public $modelClass = null;
    public $dataProvider = null;
    public $filter = null;
    public $uniqueId = null;
    public $urlAttributes = [];
    public $isAllowedAdding = true;
    public $refreshAttributes = [];
    public function getDefaultWidgetOptions()
    {
        $ucfirstTitle = $lcfirstTitle = $title = $this->title;
        $modelClass = $this->modelClass;
        if ($this->isAllowedAdding) {
            $addButton = Html::a('<i class="glyphicon glyphicon-plus"></i>', Url::to(array_merge([
                '/' . $this->getUniqueId() . '/update',
            ], $this->urlAttributes)), [
                'type' => 'button',
                'data-pjax' => 0,
                'title' => 'Add ' . $lcfirstTitle,
                'class' => 'btn btn-success'
            ]) . ' ';
        } else {
            $addButton = '';
        }

        $refreshUrlParams = [
            $this->adapter->uniqueId,
        ];

        foreach ($this->refreshAttributes as $key) {
            if (!empty($this->adapter->actionParams->get[$key])) {
                $refreshUrlParams[$key] = $this->adapter->actionParams->get[$key];
            }
        }

//        $flash = '<aasd';
        $alertBlock = $this->renderAlertBlock();

        return [
            'class' => \kartik\dynagrid\DynaGrid::className(),
            'storage' => \kartik\dynagrid\DynaGrid::TYPE_SESSION,
            'gridOptions' => [
                'filterModel' => $this->filter,
                'afterHeader' => $alertBlock,
                'toolbar' => [
                    ['content' => $addButton .
                        Html::a('<i class="glyphicon glyphicon-repeat"></i>', $refreshUrlParams, ['data-pjax' => 0, 'class' => 'btn btn-default', 'title' => 'Reset Grid'])
                    ],
                    ['content' => '{dynagridFilter}{dynagridSort}{dynagrid}'],
                    '{export}',
                ],
                'panel' => [
                    'heading' => '<h3 class="panel-title"><i class="glyphicon glyphicon-cog"></i> ' . \yii::t('executimport', $ucfirstTitle . ' list') . '</h3>',
                ],
                'dataProvider' => $this->dataProvider,
            ],
            'options' => [
                'id' => $modelClass::getModelId(),
            ],
            'columns' => $this->filter->getGridColumns(),
        ];
    }

    protected function renderAlertBlock()
    {
        $session = \Yii::$app->session;
        $flashes = $session->getAllFlashes();
        $alertContainerOptions = [];
        if (count($flashes) === 0) {
            Html::addCssStyle($alertContainerOptions, 'display:none;');
        }
        $out = Html::beginTag('div', $alertContainerOptions);
        foreach ($flashes as $type => $message) {
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

    public function getUniqueId() {
        if ($this->uniqueId) {
            return $this->uniqueId;
        } else {
            return $this->adapter->actionParams->getUniqueId(['module', 'controller']);
        }
    }
}