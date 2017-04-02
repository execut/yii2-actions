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
    public $model = null;
    protected function _run()
    {
        $model = $this->initModel();
        $model->delete();
        $response = $this->getResponse([
            'flashes' => ['kv-detail-success' => 'Record #' . $model->id . ' is successfully deleted'],
            'content' => \yii::$app->response->redirect(\Yii::$app->request->referrer),
        ]);

        return $response;
    }

    /**
     * @return mixed
     */
    protected function initModel()
    {
        if ($this->model !== null) {
            return $this->model;
        }

        $id = $this->actionParams->get['id'];
        $modelClass = $this->modelClass;
        $model = $modelClass::find()->andWhere([
            'id' => $id
        ])->one();

        return $this->model = $model;
    }
}