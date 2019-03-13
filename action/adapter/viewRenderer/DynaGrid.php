<?php
/**
 * User: execut
 * Date: 21.07.16
 * Time: 13:32
 */

namespace execut\actions\action\adapter\viewRenderer;


use execut\actions\widgets\HandlersButton;
use yii\base\Model;
use yii\db\ActiveRecord;
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

    /**
     * @var ActiveRecord
     */
    public $filter = null;
    public $uniqueId = null;
    public $urlAttributes = null;
    public $isAllowedAdding = true;
    public $isAllowedMassEdit = false;
    public $refreshAttributes = [];
    public $handleButtons = [];
    public $isRenderFlashes = true;
    public $isRenderMassDelete = false;
    public $urlAttributesExcluded = [];
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
        return [
            'class' => \execut\actions\widgets\DynaGrid::class,
            'filter' => $this->filter,
            'dataProvider' => $this->dataProvider,
            'gridOptions' => [
                'id' => $this->getGridId(),
                'updateUrl' => $this->getUpdateUrlParams(),
                'addButtonUrl' => $this->getAddUrlParams(),
                'layout' => '{alertBlock}<div class="dyna-grid-footer">{summary}{pager}<div class="dyna-grid-toolbar">{toolbar}</div></div>{items}',
                'toolbar' => $this->getToolbarConfig(),
//                'panel' => [
//                    'heading' => '<h3 class="panel-title"><i class="glyphicon glyphicon-cog"></i> ' . \yii::t('execut.actions', 'List') . ' ' . $this->lcfirst($this->title) . '</h3>',
//                    'before' => $alertBlock,
//                ],
                'options' => [
                    'id' => $this->getGridId(),
                ],
            ],
            'options' => [
                'id' => $this->getDynaGridId(),
            ],
        ];
    }

    public function getWidgetOptions()
    {
        $options = parent::getWidgetOptions();
        if (!empty($options['gridOptions']['layout'])) {
            $options['gridOptions']['layout'] = strtr($options['gridOptions']['layout'], [
                '{alertBlock}' => $this->renderAlertBlock(),
            ]);
        }

        return $options;
    }

    protected function getDynaGridId() {
        $m = $this->modelClass;
        $tableName = str_replace('\\', '_', $m) . '1';
        $userId = '';
        if (\yii::$app->user) {
            $userId .= \yii::$app->user->id;
        }

        return 'dynagrid-' . $tableName . '-' . $userId;
    }

    protected function getGridId() {
        return 'grid-' . $this->getDynaGridId();
    }

    protected function renderAlertBlock()
    {
        if (!$this->isRenderFlashes) {
            return '';
        }

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
     * @param $refreshUrlParams
     * @return array
     */
    public function getToolbarConfig(): array
    {
        $refreshUrlParams = [
            $this->adapter->uniqueId,
        ];

        foreach ($this->refreshAttributes as $key) {
            if (!empty($this->adapter->actionParams->get[$key])) {
                $refreshUrlParams[$key] = $this->adapter->actionParams->get[$key];
            }
        }

        return [
            'massEdit' => ['content' => $this->renderMassEditButton()],
            'massVisible' => ['content' => $this->renderVisibleButtons()],
            'add' => ['content' => $this->renderAddButton()],
            'refresh' => [
                'content' => Html::a('<i class="glyphicon glyphicon-repeat"></i>', $refreshUrlParams, ['data-pjax' => 0, 'class' => 'btn btn-default', 'title' => 'Reset Grid']),
            ],
            'dynaParams' => ['content' => '{dynagridFilter}{dynagridSort}{dynagrid}'],
            'toggleData' => '{toggleData}',
            'export' => '{export}',
        ];
    }

    protected function lcfirst($string, $encoding = "UTF-8")
    {
        $first = mb_convert_case(mb_substr($string, 0, 1, $encoding), MB_CASE_LOWER, $encoding);

        return $first . mb_substr($string, 1, null, $encoding);
    }

    public function getUniqueId() {
        if ($this->uniqueId) {
            return $this->uniqueId;
        } else {
            return $this->adapter->actionParams->getUniqueId(['module', 'controller']);
        }
    }

    protected function getUrlAttributes() {
        if ($this->urlAttributes === null) {
            $filterAttributes = $this->filter->attributes;
            foreach ($this->filter->getRelatedRecords() as $relation => $records) {
                if (empty($records)) {
                    continue;
                }

                if (!is_array($records)) {
                    $filterAttributes[$relation] = $records;
                    continue;
                }

                $relationAttributes = [];
                foreach ($records as $key => $record) {
                    $recordAttributes = array_filter($record->attributes);
                    if (!empty($recordAttributes)) {
                        $relationAttributes[$key] = $recordAttributes;
                    }
                }

                if (!empty($relationAttributes)) {
                    $filterAttributes[$relation] = $relationAttributes;
                }
            }

            $formName = $this->filter->formName();
            $result = [$formName => []];
            foreach ($filterAttributes as $attribute => $value) {
                if (in_array($attribute, $this->urlAttributesExcluded)) {
                    continue;
                }
                if (!empty($value)) {
                    if (is_array($value) && $this->filter->hasAttribute($attribute)) {
                        $value = current($value);
                   }

                    $result[$formName][$attribute] = $value;
                }
            }

            return $result;
        }

        return $this->urlAttributes;
    }

    /**
     * @return string
     */
    protected function renderAddButton()
    {
        if ($this->isAllowedAdding) {
            $lcfirstTitle = $this->title;
//            var_dump($lcfirstTitle);
//            exit;
            return Html::a(\yii::t('execut.actions', 'Add') . ' ' . $lcfirstTitle, Url::to($this->getAddUrlParams()), [
                    'type' => 'button',
                    'data-pjax' => 0,
                    'title' => \yii::t('execut.actions', 'Add') . ' ' . $lcfirstTitle,
                    'class' => 'btn btn-success'
                ]) . ' ';
        }
    }

    /**
     * @return string
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
            $idAttribute = null;
            /**
             * @var Model $model
             */
            $model = new $this->modelClass;
            $idAttribute = $model->formName() . '[id]';
//            $confirmMessage = strtr($buttonOptions['confirmMessage'], ['#' => (string) $this->dataProvider->getTotalCount()]);
            $buttons .= HandlersButton::widget([
                'gridId' => $this->getGridId(),
                'confirmMessage' => $buttonOptions['confirmMessage'],
                'icon' => $icon,
                'idAttribute' => $idAttribute,
                'label' => $buttonOptions['label'],
                'url' => $urlParams,
                'type' => $buttonClass,
                'totalCount' => $this->dataProvider->getTotalCount(),
            ]);
//            $buttons .= Html::a('<i class="glyphicon glyphicon-' . $icon . '"></i>', Url::to($urlParams), [
//                'type' => 'button',
//                'onclick' => new JsExpression(<<<JS
//return confirm('$confirmMessage');
//JS
//),
//                'data-pjax' => 0,
//                'title' => $buttonOptions['label'],
//                'class' => 'btn btn-' . $buttonClass
//            ]);

        }

        if ($this->isRenderMassDelete) {
            $urlParams = \yii::$app->request->getQueryParams();
            $urlParams[0] = $this->getUniqueId() . '/mass-delete';

            $buttons .= Html::a('', $urlParams, [
                'class' => 'btn btn-danger glyphicon glyphicon-trash'
            ]);
        }

        return $buttons;
    }

    /**
     * @return array
     */
    protected function getUpdateUrlParams(): array
    {
        return [
            '/' . trim($this->getUniqueId(), '/') . '/update',
        ];
    }

    /**
     * @return array
     */
    protected function getAddUrlParams(): array
    {
        return array_merge($this->getUpdateUrlParams(), $this->getUrlAttributes());
    }
}