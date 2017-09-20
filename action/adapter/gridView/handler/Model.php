<?php
/**
 * Created by PhpStorm.
 * User: execut
 * Date: 12/22/16
 * Time: 9:42 AM
 */

namespace execut\actions\action\adapter\gridView\handler;


use execut\actions\action\adapter\gridView\Handler;
use yii\db\ActiveQuery;

class Model extends Handler
{
    public $modelClass = null;
    public $attributes = null;
    public $method = 'updateAll';
    public $successMessage = '# records has been updated';
    public $asArray = true;
    public function run() {
        $dataProvider = $this->dataProvider;
        $class = $this->modelClass;
        $method = $this->method;
        $arguments = [];
        if ($this->attributes !== null) {
            $arguments[] = $this->attributes;
        }

        /**
         * @var ActiveQuery
         */
        $q = $dataProvider->query->select($class::tableName() . '.id');
        if ($this->asArray) {
            $ids = $q->limit(65535)->createCommand()->queryColumn();
        } else {
            $ids = $q;
        }
        $arguments[] = ['id' => $ids];
        $count = $class::$method(...$arguments);

        $response = new \execut\actions\action\Response();
        $flashes = [
            'kv-detail-success' => strtr($this->successMessage, ['#' => $count]),
        ];
        $response->content = \yii::$app->response->redirect($this->getReferer());
        $response->flashes = $flashes;

        return $response;
    }

    protected function getReferer() {
        return \yii::$app->request->referrer;
    }
}