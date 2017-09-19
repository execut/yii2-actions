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
    public $editAdapterConfig = [];
    public $relationAdapterConfig = [];
    public function getDefaultEditAdapterConfig() {
        return [
            'class' => Edit::className(),
            'requestType' => 'post',
//            'modelClass' => $this->modelClass,
//            'scenario' => $this->scenario,
//            'editFormLabel' => $this->editFormLabel,
//            'createFormLabel' => $this->createFormLabel,
//            'mode' => $this->mode,
            //                    'findAttribute' => 'id',
//            'view' => [
//                'class' => DetailView::className(),
//                'uniqueId' => $this->uniqueId,
//            ],
        ];
    }

    public function getEditAdapterConfig() {
        $config = ArrayHelper::merge($this->getDefaultEditAdapterConfig(), $this->editAdapterConfig);

        return $config;
    }

    public function getDefaultRelationAdapterConfig() {
        $config = [
            'class' => \execut\actions\action\adapter\GridView::className(),
            'view' => [
                'class' => DynaGrid::className(),
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

    protected $_model = null;
    public function getModel() {
        if ($this->_model === null) {
            $edit = $this->getEditAdapter();
            $this->_model = $edit->getModel();
        }

        return $this->_model;
    }

    protected $_editAdapter = null;
    public function getEditAdapter() {
        if ($this->_editAdapter !== null) {
            return $this->_editAdapter;
        }

        $editConfig = $this->getEditAdapterConfig();
        $editAdapterClass = $editConfig['class'];
        unset($editConfig['class']);
        $this->_editAdapter = $editAdapterClass::createFromAdapter($this, $editConfig);

        return $this->_editAdapter;
    }

    public function run() {
        $edit = $this->getEditAdapter();
        $result = $edit->run();
        $result->flashes = ArrayHelper::merge($this->flashes, ArrayHelper::merge($edit->flashes, $result->flashes));
        if ($result->content instanceof Response) {
            return $result;
        }

        $model = $this->getModel();
        foreach ($this->relations as $relation => $relationParams) {
            if ($relationParams === null) {
                continue;
            }

            if (is_int($relation) || is_string($relationParams)) {
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
                    if (is_array($relationQuery->via) | is_null($relationQuery->via)) {
                        foreach ($relationQuery->link as $key => $relatedKey) {
                            if (!empty($relationQuery->via) && !empty($relationQuery->via[1])) {
                                $idKey = current($relationQuery->via[1]->link);
                                $relationFilter->$key = $model->$idKey;
                            } else {
                                $relationFilter->$key = $model->$relatedKey;
                            }
                        }
                    } else {
                        $key = key($relationQuery->via->link);
                        $relationFilter->$key = $model->id;
                    }
                } else {
                    $relationFilter = $relationQuery;
                }

                $relationConfig = ArrayHelper::merge($this->getRelationAdapterConfig($relationFilter, $relation), $relationParams);
                $relationGridClass = $relationConfig['class'];
                unset($relationConfig['class']);
                $relationGrid = $relationGridClass::createFromAdapter($this, $relationConfig);
                $relationResponse = $relationGrid->run();
                $result->content .= $relationResponse->content;
            }
        }

        return $result;
    }

    protected function _run()
    {
    }
}