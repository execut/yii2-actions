<?php
/**
 * User: execut
 * Date: 14.07.16
 * Time: 16:00
 */

namespace execut\actions\action\adapter;


use execut\actions\action\adapter\viewRenderer\DetailView;
use yii\bootstrap\Html;
use yii\helpers\ArrayHelper;
use yii\web\Application;

class Edit extends Form
{
    public $modelClass = null;
    public $relations = [];
    public $additionalAttributes = [];
    public $requestType = self::MIXED;
    public $scenario = null;
    public $createFormLabel = 'Create';
    public $editFormLabel = 'Edit';
    public $labelTemplate = '{label}{closeButton}';
    public $urlParamsForRedirectAfterSave = [];
    public $closeButton = '<a href="./" class="pull-right btn btn-xs btn-primary glyphicon glyphicon-remove"></a>';

    public $isTrySaveFromGet = false;
    public $templateSuccessMessage = null;
    public $mode = 'edit';
    public $session = null;

    const EVENT_AFTER_FIND = 'afterFind';
    protected function _run() {
        $actionParams = $this->actionParams;
        if ($this->actionParams && (!empty($actionParams->get['id']) || !empty($actionParams->post['id']))) {
            $mode = $this->mode;
        } else {
            $mode = 'edit';
        }

        $model = $this->getModel();
        $this->trigger(self::EVENT_AFTER_FIND);
        if ($this->scenario !== null) {
            $model->setScenario($this->scenario);
        }

        $result = parent::loadAndValidateForm();
        if (is_array($result)) {
            $response = $this->getResponse([
                'content' => $result
            ]);
            if ($this->actionParams->isAjax) {
                $response->format = \yii\web\Response::FORMAT_JSON;
            }

            return $response;
        }

        if ($result === true && $this->isSave()) {
            if ($model->isNewRecord) {
                $operation = 'created';
            } else {
                $operation = 'updated';
            }

            $operation = $this->translate($operation);

            $model->save();
            if ($this->templateSuccessMessage !== false) {
                $parts = [
                    '{id}' => $model->primaryKey,
                    '{operation}' => $operation,
                ];

                $template = $this->getTemplateSuccessMessage();

                $this->setFlash(strtr($template, $parts));
            }

            $result = $this->redirectAfterSave();
            if ($result === false) {
                if ($this->actionParams->isAjax) {
                    return $this->getResponse([
                        'format' => \yii\web\Response::FORMAT_JSON,
                        'content' => [],
                    ]);
                }

                $result = [
                    'mode' => $mode,
                    'model' => $model
                ];
            }
        } else {
            if (!empty($model->errors)) {
                $flash = Html::errorSummary($model, [
                    'encode' => false,
                ]);

                $this->setFlash($flash, 'danger');
//                $flashes['kv-detail-danger'] = Html::errorSummary($model);
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
            'content' => $result,
        ]);

        return $response;
    }

    protected function getSession() {
        if ($this->session === null && (YII_ENV !== 'test')) {
            return \yii::$app->session;
        }

        return $this->session;
    }

    public function getIsValidate()
    {
        return $this->isSave() && parent::getIsValidate(); // TODO: Change the autogenerated stub
    }

    protected function isSave() {
        $get = $this->actionParams->get;
        unset($get['id']);

        return (!empty($get) && $this->isTrySaveFromGet || !empty($this->actionParams->post));
    }

    protected function getHeading() {
        if ($this->model->isNewRecord) {
            $editFormLabel = $this->getCreateFormLabel();
        } else {
            $editFormLabel = $this->getEditFormLabel($this->model);
        }

        $editFormLabel  = strtr($this->labelTemplate, [
            '{label}' => $editFormLabel,
            '{closeButton}' => $this->closeButton,
        ]);

        return $editFormLabel;
    }

    protected function getCreateFormLabel() {
        $m  = $this->createFormLabel;

        $t = $this->translate($m);

        return $t;
    }

    protected function getEditFormLabel() {
        $editFormLabel = $this->editFormLabel;
        if (is_callable($editFormLabel)) {
            $editFormLabel = $editFormLabel($this->model);
        } else {
            $editFormLabel = \yii::t('execut.actions', $editFormLabel);
        }

        return $editFormLabel;
    }

    public function getDefaultViewRendererConfig() {
        return [
            'class' => DetailView::className(),
            'uniqueId' => $this->uniqueId,
            'heading' => $this->getHeading(),
            'action' => $this->getFormAction(),
        ];
    }

    protected $_formAction = null;

    public function setFormAction($action) {
        $this->_formAction = $action;

        return $this;
    }

    protected function getFormAction() {
        if ($this->_formAction === null) {
            return $this->getUrlParams();
        }

        return $this->_formAction;
    }

    /**
     * @param $model
     */
    protected function redirectAfterSave()
    {
        if ($this->urlParamsForRedirectAfterSave === false) {
            return false;
        }

        $data = $this->actionParams->getData();
        $params = $this->getUrlParams();
        if (is_callable($this->urlParamsForRedirectAfterSave)) {
            $urlParamsForRedirectAfterSave = $this->urlParamsForRedirectAfterSave;
            $params = $urlParamsForRedirectAfterSave($params);
        } else {
            $params = ArrayHelper::merge($this->urlParamsForRedirectAfterSave, $params);
            if (!empty($params[1])) {
                unset($params[1]);
            }

            if (!empty($data['save'])) {
                $params = [
                    str_replace('/update', '/index', $this->getUniqueId()),
                ];
            }
        }

        $result = \yii::$app->response->redirect($params);

        return $result;
    }

    public function getData() {
        $data = parent::getData();
        if (empty($data[$this->getModel()->formName()])) {
            $data[$this->getModel()->formName()] = [];
        }

        foreach ($this->additionalAttributes as $attribute) {
            if (!empty($data[$attribute])) {
                $data[$this->getModel()->formName()][$attribute] = $data[$attribute];
            }
        }

        return $data;
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
            'id' => $model->primaryKey,
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
        $m = \yii::t('execut.actions', $m);

        return $m;
    }

    /**
     * @return string
     */
    protected function getTemplateSuccessMessage(): string
    {
        if ($this->templateSuccessMessage !== null) {
            return $this->templateSuccessMessage;
        }

        $template = $this->translate('Record') . ' #{id} ' . $this->translate('is successfully') . ' {operation}';

        return $template;
    }

    /**
     * @param $flash
     */
    protected function setFlash($flash, $type = 'success'): void
    {
        if ($session = $this->getSession()) {
            $session->addFlash('kv-detail-' . $type, $flash);
        }
    }
}