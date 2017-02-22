<?php
/**
 * User: execut
 * Date: 21.07.16
 * Time: 14:13
 */

namespace execut\actions\action\adapter;

use execut\actions\action\Adapter;
use execut\actions\action\adapter\viewRenderer\DetailView;
use execut\actions\action\adapter\viewRenderer\DynaGrid;
use execut\yii\db\query\ActiveQuery;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;
use yii\web\Response;

class EditWithRelations extends Adapter
{
    public $relations = [];
    public $modelClass = null;
    public $editAdapterConfig = [];

    public $relationAdapterConfig = [];


    public function getDefaultEditAdapterConfig() {
        return [
            'class' => Edit::className(),
            'requestType' => 'post',
            'modelClass' => $this->modelClass,
            //                    'findAttribute' => 'id',
            'view' => [
                'class' => DetailView::className(),
                'uniqueId' => $this->uniqueId,
            ],
        ];
    }

    public function getEditAdapterConfig() {
        return ArrayHelper::merge($this->getDefaultEditAdapterConfig(), $this->editAdapterConfig);
    }

    public function getDefaultRelationAdapterConfig() {
        $config = [
            'class' => \execut\actions\action\adapter\GridView::className(),
            'view' => [
                'class' => DynaGrid::className(),
                'title' => '',
            ],
        ];

        return $config;
    }

    public function getRelationAdapterConfig($relationFilter, $relation) {
        if (isset($this->relationAdapterConfig[$relation])) {
            $relationConfig = $this->relationAdapterConfig[$relation];
        } else {
            $relationConfig = $this->relationAdapterConfig;
        }

        $actionParams = $this->actionParams;
        $config = [
            'model' => $relationFilter,
            'view' => [
                'urlAttributes' => $relationFilter->attributes,
                'modelClass' => $relationFilter->className(),
                'uniqueId' => '/' . $actionParams->module . '/' . Inflector::camel2id($relationFilter->formName()),
            ],
        ];

        $config = ArrayHelper::merge($config, $this->getDefaultRelationAdapterConfig());
        $config = ArrayHelper::merge($config, $relationConfig);

        return $config;
    }


    protected function _run()
    {
        $editConfig = $this->getEditAdapterConfig();
        $editAdapterClass = $editConfig['class'];
        unset($editConfig['class']);
        $edit = $editAdapterClass::createFromAdapter($this, $editConfig);
        $result = $edit->run();
        $result->flashes = ArrayHelper::merge($this->flashes, ArrayHelper::merge($edit->flashes, $result->flashes));
        if ($result->content instanceof Response) {
            return $result;
        }

        $model = $edit->model;
        foreach ($this->relations as $relation => $relationParams) {
            if (is_int($relation)) {
                $relation = $relationParams;
                $relationParams = [];
            }

            /**
             * @var ActiveQuery $relationQuery
             */
            $relationQuery = $model->getRelation($relation);

            if (!$model->isNewRecord) {
                if (!is_array($relationQuery)) {
                    $relationModelClass = $relationQuery->modelClass;
                    $relationFilter = new $relationModelClass;
                    $id = key($relationQuery->link);
                    $relationFilter->$id = $model->id;
                } else {
                    $relationFilter = $relationQuery;
                }

                $relationConfig = $this->getRelationAdapterConfig($relationFilter, $relation);
                $relationGridClass = $relationConfig['class'];
                unset($relationConfig['class']);
                $relationGrid = $relationGridClass::createFromAdapter($this, $relationConfig);
                $relationResponse = $relationGrid->run();
                $result->content .= $relationResponse->content;
            }
        }

        return $result;
    }
}