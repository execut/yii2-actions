<?php
/**
 */

namespace execut\actions\action;


use yii\base\Component;

class RequestHelper extends Component
{
    public function getPost() {
        return \yii::$app->request->post();
    }

    public function getGet() {
        return \yii::$app->request->get();
    }

    public function getFiles() {
        return $_FILES;
    }

    public function isAjax() {
        return \yii::$app->request->getIsAjax();
    }

    public function isPjax() {
        return \yii::$app->request->getIsPjax();
    }
}