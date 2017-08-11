<?php
/**
 * Created by PhpStorm.
 * User: execut
 * Date: 8/11/17
 * Time: 1:30 PM
 */

namespace execut\actions\action;


use yii\base\Object;

class ModelsFinder extends Object
{
    public $modelClass = null;
    public $actionParams = null;
    public $findAttributes = ['id'];
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
        $attributes = $this->findAttributes;
        $actionParams = $this->actionParams;
        $isHasAttributes = false;
        $result = [];
        foreach ($attributes as $attribute) {
            if (!empty($actionParams->get[$attribute])) {
                $result[$attribute] = $actionParams->get[$attribute];
            }

            if (!empty($actionParams->post[$attribute])) {
                $result[$attribute] = $actionParams->post[$attribute];
            }
        }

        if (count($attributes) == count($result)) {
            return $result;
        }
    }
}