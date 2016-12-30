<?php
/**
 * Created by PhpStorm.
 * User: execut
 * Date: 12/29/16
 * Time: 2:09 PM
 */

namespace execut\actions\action\adapter\gridView;


use execut\helpers\StringHelper;
use execut\yii\helpers\ArrayHelper;
use kartik\daterange\DateRangePicker;
use kartik\detail\DetailView;
use kartik\grid\ActionColumn;
use kartik\grid\BooleanColumn;
use kartik\grid\GridView;
use yii\data\ActiveDataProvider;
use yii\helpers\Inflector;
use yii\helpers\Url;
use yii\web\JsExpression;

trait ModelHelper
{
    public function getStandardFields($columns = []) {
        $standardColumns = [
            'id' => [
                'displayOnly' => true,
                'attribute' => 'id',
            ],
            'visible' => [
                'type' => DetailView::INPUT_CHECKBOX,
                'attribute' => 'visible',
            ],
            'name' => [
                'attribute' => 'name'
            ],
            'created' => [
                'displayOnly' => true,
                'attribute' => 'created',
            ],
            'updated' => [
                'displayOnly' => true,
                'attribute' => 'updated',
            ],
        ];

        foreach ($standardColumns as $key => $column) {
            if (isset($column['attribute']) && !$this->hasAttribute($column['attribute'])) {
                unset($columns[$key]);
            }
        }

        $columns = ArrayHelper::merge($standardColumns, $columns);

        return $columns;
    }

    public function getStandardColumns($columns = []) {
        $standardColumns = [
            'id' => [
                'attribute' => 'id',
            ],
            'visible' => [
                'class' => BooleanColumn::class,
                'attribute' => 'visible',
            ],
            'name' => [
                'attribute' => 'name'
            ],
            'created' => [
                'attribute' => 'created',
                'filter' => DateRangePicker::widget([
                    'attribute' => 'created',
                    'model' => $this,
                    'convertFormat'=>true,
                    'pluginOptions'=>[
                        'locale'=>['format'=>'Y-m-d']
                    ]
                ]),
            ],
            'updated' => [
                'attribute' => 'updated',
                'filter' => DateRangePicker::widget([
                    'attribute' => 'updated',
                    'model' => $this,
                    'convertFormat'=>true,
                    'pluginOptions'=>[
                        'locale'=>['format'=>'Y-m-d']
                    ]
                ]),
            ],
            'actions' => [
                'class' => ActionColumn::className(),
            ],
        ];

        foreach ($standardColumns as $key => $column) {
            if (isset($column['attribute']) && !$this->hasAttribute($column['attribute'])) {
                unset($columns[$key]);
            }
        }

        $columns = ArrayHelper::merge($standardColumns, $columns);

        return $columns;
    }

    public function getRelationColumn($relationName, $url) {
        $attribute = Inflector::camel2id($relationName, '_') . '_id';
        $modelClass = $this->getRelation($relationName)->modelClass;
        $sourceInitText = [];
        if (!empty($this->$attribute)) {
            $sourceIds = [];
            if (is_array($this->$attribute)) {
                $sourceIds = $this->$attribute;
            } else {
                $sourceIds[] = $this->$attribute;
            }

            $sourceInitText = $modelClass::find()->byId($sourceIds)->forSelect('name', false);
        }

//        $sourcesNameAttribute = $modelClass::getFormAttributeName('name');

        return [
            'attribute' => $attribute,
            'value' => $relationName . '.name',
//                'value' => function () {
//                    return 'asdasd';
//                },
            'filter' => $sourceInitText,
            'filterType' => GridView::FILTER_SELECT2,
            'filterWidgetOptions' => [
                'initValueText' => $sourceInitText,
                'options' => [
                    'multiple' => true,
                ],
                'pluginOptions' => [

                    'allowClear' => true,
                    'ajax' => [
                        'url' => Url::to([$url]),
                        'dataType' => 'json',
                        'data' => new JsExpression(<<<JS
function (params) {
  return {
    "name": params.term
  };
}
JS
                        )

                    ],
                ],
            ],
        ];
    }

    public function getRelationField($relationName, $url) {
        $attribute = Inflector::camel2id($relationName, '_') . '_id';
        $modelClass = $this->getRelation($relationName)->modelClass;
        $sourceInitText = '';
        if (!empty($this->$attribute)) {
            $sourceInitText = $this->$relationName->name;
        }

        $sourcesNameAttribute = $modelClass::getFormAttributeName('name');

        return [
            'type' => DetailView::INPUT_SELECT2,
            'attribute' => $attribute,
//                'data' => DetailsBrands::find()->forSelect(),
            'value' => $sourceInitText,
            'widgetOptions' => [
                'initValueText' => $sourceInitText,
                'pluginOptions' => [
                    'allowClear' => true,
                    'ajax' => [
                        'url' => Url::to([$url]),
                        'dataType' => 'json',
                        'data' => new JsExpression(<<<JS
function(params) {
    return {
        "name": params.term
    };
}
JS
                        )
                    ],
                ],
            ],
        ];
    }

    public function getDataProvider() {
        $q = $this->getSearchQuery();

        return new ActiveDataProvider([
            'query' => $q,
        ]);
    }

    public function search($data) {
        $this->load($data);
        $result = $this->getDataProvider();

        return $result;
    }

    public function formName()
    {
        return '';
    }

    /**
     * @return mixed
     */
    protected function getSearchQuery()
    {
        $q = self::find();

        if ($this->term) {
            $q->andWhere([
                'ILIKE',
                self::tableName() . '.name',
                $this->term
            ]);
        }

        $q->andFilterWhere($this->attributes);

        return $q;
    }
}