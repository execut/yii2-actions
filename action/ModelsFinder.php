<?php
/**
 * Created by PhpStorm.
 * User: execut
 * Date: 8/11/17
 * Time: 1:30 PM
 */

namespace execut\actions\action;


use yii\base\BaseObject;
use yii\db\ActiveRecord;

class ModelsFinder extends BaseObject
{
    public $modelClass = null;
    protected $actionParams = null;
    protected $findAttributes = null;

    public function setFindAttributes($attributes) {
        $this->findAttributes = $attributes;
        return $this;
    }

    public function getFindAttributes() {
        if ($this->findAttributes === null) {
            $class = $this->modelClass;
            if (method_exists($class, 'primaryKey')) {
                $pk = $class::primaryKey();
                if (!is_array($pk)) {
                    $pk = [$pk];
                }

                $this->findAttributes = $pk;
            } else {
                $this->findAttributes = [];
            }
        }

        return $this->findAttributes;
    }

    public function getActionParams() {
        if (is_array($this->actionParams)) {
            if (empty($this->actionParams['class'])) {
                $this->actionParams['class'] = Params::class;
            }

            $this->actionParams = \yii::createObject($this->actionParams);
        }

        return $this->actionParams;
    }

    public function setActionParams($params) {
        $this->actionParams = $params;

        return $this;
    }

    public function find() {
        $class = $this->modelClass;
        if ($where = $this->getWhereParams()) {
            $model = new $class;
            if ($model instanceof ActiveRecord) {
                foreach ($where as $attribute => $value) {
                    $column = $model->getTableSchema()->getColumn($attribute);
                    if ($column) {
                        $value = $column->phpTypecast($value);
                        $where[$attribute] = $value;
                    }
                }
            }

            $model = $class::find()->andWhere($where)->one();
        } else {
            $model = new $class;
        }

        return $model;
    }

    protected function getWhereParams() {
        $attributes = $this->getFindAttributes();
        $actionParams = $this->getActionParams();
        $result = [];
        foreach ($attributes as $actionAttribute => $attribute) {
            if (is_int($actionAttribute)) {
                $actionAttribute = $attribute;
            }

            if (!empty($actionParams->get[$actionAttribute])) {
                $result[$attribute] = $actionParams->get[$actionAttribute];
            }

            if (!empty($actionParams->post[$actionAttribute])) {
                $result[$attribute] = $actionParams->post[$actionAttribute];
            }
        }

        if (count($attributes) == count($result)) {
            return $result;
        }
    }
}