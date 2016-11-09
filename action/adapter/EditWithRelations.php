<?php
/**
 * User: execut
 * Date: 21.07.16
 * Time: 14:13
 */

namespace execut\action\adapter;

use execut\action\Adapter;
use execut\action\adapter\viewRenderer\DetailView;
use execut\action\adapter\viewRenderer\DynaGrid;
use execut\yii\db\query\ActiveQuery;
use execut\yii\helpers\ArrayHelper;
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
            'class' => \execut\action\adapter\GridView::className(),
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

        $config =  ArrayHelper::merge($this->getDefaultRelationAdapterConfig(), $relationConfig);
        $config['model'] = $relationFilter;
        $config['view']['urlAttributes'] = $relationFilter->attributes;
        $config['view']['modelClass'] = $relationFilter->className();
        $actionParams = $this->actionParams;
        $config['view']['uniqueId'] = '/' . $actionParams->module . '/' . Inflector::camel2id($relationFilter->formName());

        return $config;
    }


    protected function _run()
    {
        $editConfig = $this->getEditAdapterConfig();
        $editAdapterClass = $editConfig['class'];
        unset($editConfig['class']);
        $edit = $editAdapterClass::createFromAdapter($this, $editConfig);
        $result = $edit->run();
        $result->flashes = $this->flashes = $edit->flashes;
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