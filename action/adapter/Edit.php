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
use yii\helpers\Url;
use yii\web\Application;
use yii\web\NotFoundHttpException;

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
    const EVENT_BEFORE_SAVE = 'beforeSave';
    protected function _run() {
        $request = \yii::$app->request;
        $actionParams = $this->actionParams;
        if ($request->referrer !== null && empty($actionParams->get['redirect'])) {
            $url = $request->pathInfo;
            $refererUrl = trim(str_replace([$request->hostInfo, $request->baseUrl], '', explode('?', $request->referrer)[0]), '/');
            if ($refererUrl !== $url) {
                $this->saveRedirectUrl($request->referrer);
            }
        }

//        if ($this->actionParams && (!empty($actionParams->get['id']) || !empty($actionParams->post['id']))) {
            $mode = $this->mode;
//        } else {
//            $mode = 'edit';
//        }

        if (!empty($this->getData()['view'])) {
            $mode = 'view';
        }

        $model = $this->getModel();
        if (!$model) {
            throw new NotFoundHttpException('Record not founded');
        }

        $this->trigger(self::EVENT_AFTER_FIND);
        if ($this->scenario !== null) {
            $model->setScenario($this->scenario);
        }

        $isNewRecord = $model->isNewRecord;
        if (!$this->isCanceled()) {
            $result = parent::loadAndValidateForm();
            if (is_array($result)) {
                $response = $this->getResponse([
                    'content' => $result
                ]);
                if ($this->actionParams->isAjax && !$this->actionParams->isPjax) {
                    $response->format = \yii\web\Response::FORMAT_JSON;
                }

                return $response;
            }
        }

        if ($this->isCanceled()) {
            $result = $this->redirectAfterSave();
            if ($result === false) {
                $result = [
                    'mode' => $mode,
                    'model' => $model
                ];
            }
        } else if ($result === true && $this->isSave() && $this->isSubmitted()) {
            $this->trigger(self::EVENT_BEFORE_SAVE);
            $model->save();
            $this->trigger('afterSave');
            if ($isNewRecord) {
                $this->trigger('afterCreate');
            } else {
                $this->trigger('afterUpdate');
            }

            $successMessage = $this->getSuccessMessage($isNewRecord);
            if ($successMessage) {
                $this->setFlash($successMessage);
            }

            if ($this->actionParams->isAjax && !$this->actionParams->isPjax) {
                return $this->getResponse([
                    'format' => \yii\web\Response::FORMAT_JSON,
                    'content' => [
                        'message' => $this->getSuccessMessage($isNewRecord),
                    ],
                ]);
            }

            $result = $this->redirectAfterSave();
            if ($result === false) {
                $result = [
                    'mode' => $mode,
                    'model' => $model
                ];
            }
        } else {
            if ($this->actionParams->isAjax && !$this->actionParams->isPjax) {
                return $this->getResponse([
                    'format' => \yii\web\Response::FORMAT_JSON,
                    'content' => [
                        'success' => true,
                    ],
                ]);
            }

            if (!empty($model->errors)) {
                $flash = Html::errorSummary($model, [
                    'encode' => false,
                ]);

                $this->setFlash($flash, 'danger');
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

    protected function saveRedirectUrl($url) {
        \yii::$app->cache->set($this->getCacheKey(), $url);
    }

    protected function loadRedirectUrl() {
        return \yii::$app->cache->get($this->getCacheKey());
    }

    protected function getSession() {
        if ($this->session === null) {
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

        return (!empty($get) && $this->isTrySaveFromGet || (!empty($this->actionParams->post) && empty($this->actionParams->post['check'])));
    }

    protected function isCanceled() {
        $data = $this->actionParams->getData();
        return !empty($data['cancel']);
    }

    protected function isSubmitted() {
        $data = $this->actionParams->getData();
        return !empty($data['apply']) || !empty($data['save']);
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
        $afterSave = $this->urlParamsForRedirectAfterSave;
        if ($afterSave === false) {
            return false;
        }

        $data = $this->actionParams->getData();
        $params = $this->getUrlParams();
        if (is_callable($afterSave)) {
            $afterSave = $afterSave($params, $this);
            if ($afterSave === false) {
                return false;
            }
        }

        $params = ArrayHelper::merge($params, $afterSave);
        if (!empty($params[1])) {
            unset($params[0]);
            $params = [$params[1]];
        }

        if ((!empty($data['save']) || !empty($data['cancel'])) && empty($afterSave[0])) {
            $params = $this->getDefaultRedirectParams();
        }

        $result = \yii::$app->response->redirect($params);

        return $result;
    }

    protected function getDefaultRedirectParams() {
        if ($params = $this->loadRedirectUrl()) {
            $model = $this->getModel();
            $params = $params . (strpos($params, '?') === false ? '?' : '&') . 'redirect=1';

            $hash = $this->getUrlHashFromModel($model);
            $params = Url::to($params) . '#' . $hash;

            return $params;
        }

        $params = [
            str_replace('/update', '/index', $this->getUniqueId()),
        ];

        return $params;
    }

    public function getData() {
        $data = parent::getData();
        $model = $this->getModel();
        if (!$model) {
            return $data;
        }

        if (empty($data[$model->formName()])) {
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
        ];

        $pk = $model->primaryKey;
        if (is_array($pk)) {
            if (!$model->isNewRecord) {
                $params = array_merge($params, $pk);
            }
        } else {
            $params['id'] = $pk;
        }

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

    /**
     * @param $model
     * @return string
     */
    protected function getSuccessMessage($isNewRecord)
    {
        $model = $this->getModel();
        if ($this->templateSuccessMessage !== false) {
            if ($isNewRecord) {
                $operation = 'created';
            } else {
                $operation = 'updated';
            }

            $operation = $this->translate($operation);

            $parts = [
                '{id}' => $model->primaryKey,
                '{operation}' => $operation,
            ];

            $template = $this->getTemplateSuccessMessage();

            $successMessage = strtr($template, $parts);

            return $successMessage;
        }
    }

    /**
     * @return string
     */
    protected function getCacheKey(): string
    {
        return $this->getUniqueId() . '-' . $this->modelClass . '-' . \yii::$app->session->id;
    }

    /**
     * @param $model
     * @return string
     */
    protected function getUrlHashFromModel($model): string
    {
        $primaryKey = $model->primaryKey;
        if (is_array($primaryKey)) {
            $primaryKey = implode('-', $primaryKey);
        }

        $hash = $model->formName() . '-' . $primaryKey;
        return $hash;
    }
}