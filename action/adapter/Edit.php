<?php
/**
 * User: execut
 * Date: 14.07.16
 * Time: 16:00
 */

namespace execut\actions\action\adapter;


use execut\actions\action\Adapter;
use execut\actions\action\adapter\viewRenderer\DetailView;
use execut\actions\action\ModelsFinder;
use execut\actions\action\Response;
use yii\base\Event;
use yii\bootstrap\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\UploadedFile;

class Edit extends Form
{
    public $modelClass = null;
    public $relations = [];
    public $additionalAttributes = [];
    public $requestType = self::POST;
    public $scenario = null;
    public $createFormLabel = 'Create';
    public $editFormLabel = 'Edit';
    public $urlParamsForRedirectAfterSave = [];

    public $isTrySaveFromGet = false;
    public $mode = 'view';
    protected function _run() {
        $actionParams = $this->actionParams;
        if ($this->actionParams && (!empty($actionParams->get['id']) || !empty($actionParams->post['id']))) {
            $mode = $this->mode;
        } else {
            $mode = 'edit';
        }

        $model = $this->getModel();
        if ($this->scenario !== null) {
            $model->setScenario($this->scenario);
        }

        $result = parent::loadAndValidateForm();
        if (is_array($result)) {
            return $this->getResponse([
                'content' => $result
            ]);
        }

        $flashes = [];
        if ($result === true) {
            if ($model->isNewRecord) {
                $operation = 'created';
            } else {
                $operation = 'updated';
            }

            $operation = $this->translate($operation);

            $model->save();
            $flashes['kv-detail-success'] = $this->translate('Record') . ' #' . $model->id . ' ' . $this->translate('is successfully') . ' ' .  $operation;

            $result = $this->redirectAfterSave();
            if ($result === false) {
                if ($this->actionParams->isAjax) {
                    return $this->getResponse([
                        'format' => \yii\web\Response::FORMAT_JSON,
                        'content' => [],
                    ]);
                }
            }
        } else {
            if (!empty($model->errors)) {
                $flashes['error'] = Html::errorSummary($model);
            }

            $result = [
                'mode' => $mode,
                'model' => $model
            ];
        }

        if (\yii::$app->has('db') && $t = \yii::$app->db->transaction) {
            while ($t->getIsActive()) {
                $t->commit();
            }
        }

        $response = $this->getResponse([
            'flashes' => $flashes,
            'content' => $result,
        ]);

        return $response;
    }

    protected function getHeading() {
        if ($this->model->isNewRecord) {
            return $this->getCreateFormLabel();
        } else {
            return $this->getEditFormLabel($this->model);
        }
    }

    protected function getCreateFormLabel() {
        $m  = $this->createFormLabel;
        $t = $this->translate($m);

        return $t;
    }

    protected function getEditFormLabel() {
        $editFormLabel = $this->editFormLabel;
        if (is_callable($editFormLabel)) {
            return $editFormLabel($this->model);
        }

        return \yii::t('execut.actions', $editFormLabel);
    }

    public function getDefaultViewRendererConfig() {
        return [
            'class' => DetailView::className(),
            'uniqueId' => $this->uniqueId,
            'heading' => $this->getHeading(),
            'action' => $this->getFormAction(),
        ];
    }

    protected function getFormAction() {
        $params = $this->getUrlParams();

        return $params;
    }

    /**
     * @param $model
     */
    protected function redirectAfterSave()
    {
        if ($this->urlParamsForRedirectAfterSave === false) {
            return false;
        }

        $params = $this->getUrlParams();

        if (is_callable($this->urlParamsForRedirectAfterSave)) {
            $urlParamsForRedirectAfterSave = $this->urlParamsForRedirectAfterSave;
            $params = $urlParamsForRedirectAfterSave($params);
        } else {
            $params = ArrayHelper::merge($this->urlParamsForRedirectAfterSave, $params);
            if (!empty($params[1])) {
                unset($params[1]);
            }
        }

        $result = \yii::$app->response->redirect($params);

        return $result;
    }

    /**
     * @param $model
     * @return array
     */
    protected function getUrlParams(): array
    {
        $model = $this->model;
        $params = [
            $this->actionParams->uniqueId,
            'id' => $model->id
        ];

        foreach ($this->additionalAttributes as $attribute) {
            $params[$attribute] = $model->$attribute;
        }
        return $params;
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