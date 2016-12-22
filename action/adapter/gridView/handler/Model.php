<?php
/**
 * Created by PhpStorm.
 * User: execut
 * Date: 12/22/16
 * Time: 9:42 AM
 */

namespace execut\actions\action\adapter\gridView\handler;


use execut\actions\action\adapter\gridView\Handler;

class Model extends Handler
{
    public $modelClass = null;
    public $attributes = null;
    public $method = 'updateAll';
    public $successMessage = '# records has been updated';
    public function run() {
        $dataProvider = $this->dataProvider;
        $class = $this->modelClass;
        $ids = $dataProvider->query->select('id')->queryAttribute('id');
        $method = $this->method;
        $arguments = [];
        if ($this->attributes !== null) {
            $arguments[] = $this->attributes;
        }

        $arguments[] = ['id' => $ids];
        $count = $class::$method(...$arguments);

        $response = new \execut\actions\action\Response();
        $flashes = [
            'kv-detail-success' => strtr($this->successMessage, '#', $count),
        ];
        $response->content = \yii::$app->response->redirect($this->getReferer());
        $response->flashes = $flashes;

        return $response;
    }

    protected function getReferer() {
        return \yii::$app->request->referrer;
    }
}