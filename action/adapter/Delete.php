<?php
/**
 * User: execut
 * Date: 25.07.16
 * Time: 9:59
 */

namespace execut\actions\action\adapter;


use execut\actions\action\Adapter;
use execut\actions\action\Response;

class Delete extends Adapter
{
    public $modelClass = null;
    public $isRedirect = false;
    protected function _run()
    {
        $model = $this->getModel();
        $model->delete();
        if ($this->isRedirect) {
            $response = \yii::$app->response->redirect(\Yii::$app->request->referrer);
        } else {
            $response = '';
        }

        $response = $this->getResponse([
            'flashes' => ['kv-detail-success' => 'Record #' . $model->id . ' is successfully deleted'],
            'content' => $response,
        ]);

        return $response;
    }
}