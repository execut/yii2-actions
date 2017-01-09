<?php
/**
 * User: execut
 * Date: 21.07.16
 * Time: 13:32
 */

namespace execut\actions\action\adapter\viewRenderer;


use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use kartik\export\ExportMenu;
use yii\bootstrap\Alert;
use yii\data\BaseDataProvider;
use yii\helpers\Url;
use yii\web\JsExpression;

class DynaGrid extends Widget
{
    public $title = null;
    public $modelClass = null;
    /**
     * @var BaseDataProvider
     */
    public $dataProvider = null;
    public $filter = null;
    public $uniqueId = null;
    public $urlAttributes = [];
    public $isAllowedAdding = true;
    public $isAllowedMassEdit = false;
    public $refreshAttributes = [];
    public $handleButtons = [];
    public $defaultHandleButtons = [
        'visible' => [
            'icon' => 'eye-open',
            'label' => 'Mark visible',
            'confirmMessage' => 'You sure want mark # records as visible?',
            'enable' => false,
        ],
        'unvisible' => [
            'icon' => 'eye-close',
            'label' => 'Mark unvisible',
            'confirmMessage' => 'You sure want mark # records as unvisible?',
            'enable' => false,
        ],
        'delete' => [
            'icon' => 'trash',
            'label' => 'Delete',
            'button' => 'danger',
            'confirmMessage' => 'You sure want delete # records?',
            'enable' => false,
        ],
    ];
    public function getDefaultWidgetOptions()
    {
        $ucfirstTitle = $title = $this->title;

        $refreshUrlParams = [
            $this->adapter->uniqueId,
        ];

        foreach ($this->refreshAttributes as $key) {
            if (!empty($this->adapter->actionParams->get[$key])) {
                $refreshUrlParams[$key] = $this->adapter->actionParams->get[$key];
            }
        }

        $columns = $this->filter->getGridColumns();
//        $flash = '<aasd';
        $alertBlock = $this->renderAlertBlock();
        $fullExportMenu = ExportMenu::widget([
            'dataProvider' => $this->dataProvider,
//            'dataProvider' => $dataProvider,
            'columns' => $columns,
            'target' => ExportMenu::TARGET_BLANK,
            'batchSize' => 1000,
            'fontAwesome' => true,
            'asDropdown' => false, // this is important for this case so we just need to get a HTML list
            'dropdownOptions' => [
                'label' => '<i class="glyphicon glyphicon-export"></i> Full'
            ],
        ]);

        return [
            'class' => \kartik\dynagrid\DynaGrid::className(),
            'storage' => \kartik\dynagrid\DynaGrid::TYPE_DB,
//            'pageSize' => 100000,
            'gridOptions' => [
                'export' => [
                    'fontAwesome' => true,
                    'itemsAfter'=> [
                        '<li role="presentation" class="divider"></li>',
                        '<li class="dropdown-header">Export All Data</li>',
                        $fullExportMenu
                    ]
                ],
                'toggleDataOptions' => [
                    'maxCount' => 100000,
//                    'all' => [
//                       'icon' => 'resize-full',
//                       'label' => 'All',
//                       'class' => 'btn btn-default',
//                       'title' => 'Show all data'
//                    ],
                ],
                'filterModel' => $this->filter,
                'afterHeader' => $alertBlock,
                'toolbar' => [
                    ['content' => $this->renderMassEditButton()],
                    ['content' => $this->renderVisibleButtons()],
                    ['content' => $this->renderAddButton() .
                        Html::a('<i class="glyphicon glyphicon-repeat"></i>', $refreshUrlParams, ['data-pjax' => 0, 'class' => 'btn btn-default', 'title' => 'Reset Grid'])
                    ],
                    ['content' => '{dynagridFilter}{dynagridSort}{dynagrid}'],
                    '{toggleData}',
                    '{export}',
                ],
                'panel' => [
                    'heading' => '<h3 class="panel-title"><i class="glyphicon glyphicon-cog"></i> ' . \yii::t('executimport', $ucfirstTitle . ' list') . '</h3>',
                ],
                'dataProvider' => $this->dataProvider,
                'options' => [
                    'id' => $this->getGridId(),
                ],
            ],
            'options' => [
                'id' => $this->getDynaGridId(),
            ],
            'columns' => $columns,
        ];
    }

    protected function getDynaGridId() {
        $modelClass = $this->modelClass;
        $userId = '';
        if (\yii::$app->user) {
            $userId .= \yii::$app->user->id;
        }

        return 'dynagrid-' . $modelClass::getModelId() . $userId;
    }

    protected function getGridId() {
        $modelClass = $this->modelClass;
        $userId = '';
        if (\yii::$app->user) {
            $userId .= \yii::$app->user->id;
        }

        return 'grid-' . $modelClass::getModelId() . $userId;
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

    /**
     * @return array
     */
    protected function renderAddButton()
    {
        if ($this->isAllowedAdding) {
            $lcfirstTitle = $this->title;
            return Html::a('<i class="glyphicon glyphicon-plus"></i>', Url::to(array_merge([
                    '/' . $this->getUniqueId() . '/update',
                ], $this->urlAttributes)), [
                    'type' => 'button',
                    'data-pjax' => 0,
                    'title' => 'Add ' . $lcfirstTitle,
                    'class' => 'btn btn-success'
                ]) . ' ';
        }
    }

    /**
     * @return array
     */
    protected function renderMassEditButton()
    {
        if ($this->isAllowedMassEdit) {
            $lcfirstTitle = $this->title;

            return \mickgeek\actionbar\Widget::widget([
                'renderContainer' => false,
                'grid' => $this->getGridId(),
                'templates' => [
                    '{bulk-actions}' => [
//                        'class' => 'col-xs-4'
                    ],
//                    '{create}' => ['class' => 'col-xs-8 text-right'],
                ],
                'bulkActionsItems' => [
                    'General' => ['mass-update' => 'Mass edit',],
                ],
                'bulkActionsOptions' => [
                    'options' => [
                        'mass-update' => [
                            'url' => Url::toRoute(['mass-update']),
                            'method' => 'get',
                            'name' => 'id',
                        ],
                    ],
                    'class' => 'form-control',
                ],
            ]);
        }
    }

    /**
     * @return string
     */
    protected function renderVisibleButtons(): string
    {
        $buttons = '';
        $handleButtons = ArrayHelper::merge($this->defaultHandleButtons, $this->handleButtons);
        foreach ($handleButtons as $handle => $buttonOptions) {
            if (isset($buttonOptions['enable']) && $buttonOptions['enable'] === false) {
                continue;
            }

            $urlParams = \yii::$app->request->getQueryParams();
            $urlParams[0] = '';
            $urlParams['handle'] = $handle;
            $buttonClass = 'default';
            if (!empty($buttonOptions['button'])) {
                $buttonClass = $buttonOptions['button'];
            }

            $icon = $buttonOptions['icon'];
            $confirmMessage = strtr($buttonOptions['confirmMessage'], ['#' => (string) $this->dataProvider->getTotalCount()]);
            $buttons .= Html::a('<i class="glyphicon glyphicon-' . $icon . '"></i>', Url::to($urlParams), [
                'type' => 'button',
                'onclick' => new JsExpression(<<<JS
return confirm('$confirmMessage');
JS
),
                'data-pjax' => 0,
                'title' => $buttonOptions['label'],
                'class' => 'btn btn-' . $buttonClass
            ]);

        }

        return $buttons;
    }
}