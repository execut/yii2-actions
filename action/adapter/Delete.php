<?php
/**
 * User: execut
 * Date: 25.07.16
 * Time: 9:59
 */

namespace execut\actions\action\adapter;


use execut\actions\action\Adapter;
use execut\actions\action\Response;
use yii\db\IntegrityException;

class Delete extends Adapter
{
    public $modelClass = null;
    public $isRedirect = true;
    protected function _run()
    {
        $model = $this->getModel();
        if (!$model) {
            $flashes = [
                'kv-detail-warning' => $this->translate('You are trying to delete an already deleted entry.'),
            ];
        } else {
            $isFailedDelete = false;
            try {
                $model->delete();
            } catch (IntegrityException $e) {
                $isFailedDelete = true;
                $flashes = ['kv-detail-warning' => $this->translate('Record') . ' #' . $model->primaryKey . ' ' . $this->translate('cannot be deleted because it has associated entries that cannot be deleted') . '. ' . $this->translate('Error content') . ': "' . $e->getMessage() . '".'];
            }

            if (!$isFailedDelete) {
                $flashes = ['kv-detail-success' => $this->translate('Record') . ' #' . $model->primaryKey . ' ' . $this->translate('is successfully') . ' ' . $this->translate('deleted')];
            }
        }

        if ($this->isRedirect) {
            $response = \yii::$app->response->redirect(\Yii::$app->request->referrer);
        } else {
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