<?php
/**
 * Created by PhpStorm.
 * User: execut
 * Date: 11/15/17
 * Time: 12:14 PM
 */

namespace execut\actions\widgets;

use execut\actions\action\adapter\viewRenderer\DynaGridRow;
use execut\yii\jui\WidgetTrait;
use kartik\export\ExportMenu;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use \kartik\dynagrid\DynaGrid as KartikDynaGrid;

class DynaGrid extends KartikDynaGrid
{
    use WidgetTrait;
    public $dataProvider = null;
    public $storage = KartikDynaGrid::TYPE_DB;
    public $filter = null;

    public function init()
    {
        $columns = $this->filter->getGridColumns();
//        foreach ($exportColumns as &$column) {
//            unset($column['visible']);
//        }

        $fullExportMenu = ExportMenu::widget([
            'dataProvider' => $this->dataProvider,
            'columns' => $columns,
            'showColumnSelector' => true,
            'target' => ExportMenu::TARGET_BLANK,
            'batchSize' => 1000,
            'fontAwesome' => true,
            'asDropdown' => false,
            'dynagrid' => true,
            'dynagridOptions' => [
                'options' => ['id' => $this->options['id']],
//                'columns' => $columns,
            ],
            'dropdownOptions' => [
                'label' => '<i class="glyphicon glyphicon-export"></i> Full'
            ],
        ]);

        $this->columns = $columns;

        $this->gridOptions = ArrayHelper::merge([
            'class' => GridView::class,
            'responsive' => false,
            'responsiveWrap' => false,
            'rowOptions' => function ($row) {
                if ($row instanceof DynaGridRow) {
                    return $row->getRowOptions();
                }
            },
            'panel' => false,
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
            ],
            'filterModel' => $this->filter,
//            'toolbar' => $this->getToolbarConfig(),
            'dataProvider' => $this->dataProvider,
        ], $this->gridOptions);

        parent::init(); // TODO: Change the autogenerated stub
    }

    public function getGridId() {
        return $this->id . '-grid';
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        if (method_exists($this, 'initWidget')) {
            $this->initWidget();
        }

        $this->gridOptions['options']['id'] = $this->id;

        Html::addCssClass($this->options, $this->getDefaultCssClass());
        $this->_registerBundle();

        echo Html::tag('div', GridView::widget($this->gridOptions), $this->options);
    }
}