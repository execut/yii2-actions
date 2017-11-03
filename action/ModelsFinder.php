<?php
/**
 * Created by PhpStorm.
 * User: execut
 * Date: 8/11/17
 * Time: 1:30 PM
 */

namespace execut\actions\action;


use yii\base\BaseObject;

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
            $pk = $class::primaryKey();
            if (!is_array($pk)) {
                $pk = [$pk];
            }

            $this->findAttributes = $pk;
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