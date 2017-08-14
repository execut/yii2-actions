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
    public $isRedirect = true;
    protected function _run()
    {
        $model = $this->getModel();
        $model->delete();
        if ($this->isRedirect) {
            $response = \yii::$app->response->redirect(\Yii::$app->request->referrer);
            $flashes = ['kv-detail-success' => $this->translate('Record') . ' #' . $model->id . ' ' . $this->translate('is successfully') . ' ' . $this->translate('deleted')];
        } else {
            $flashes = [];
            $response = '';
        }

        $response = $this->getResponse([
            'flashes' => $flashes,
            'content' => $response,
        ]);

        return $response;
    }

    /**
     * @param $m
     * @return string
     */
    protected function translate($m): string
    {
        if (YII_ENV !== 'test') {
            $m = \yii::t('execut.actions', $m);
        }

        return $m;
    }
}