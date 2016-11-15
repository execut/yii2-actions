<?php
/**
 * User: execut
 * Date: 07.07.15
 * Time: 11:26
 */

namespace execut\actions\action\adapter;


use execut\actions\action\Adapter;
use execut\actions\action\adapter\helper\FormLoader;
use yii\base\Model;
use yii\web\Response;
use yii\widgets\ActiveForm;

/**
 * Class GridView
 * @package execut\actions\action
 * @property Model $filter
 */
class Form extends Adapter
{
    const POST = 'post';
    const GET = 'get';
    /**
     * @var Model
     */
    protected $_model = null;
    public $requestType = 'get';
    public $isDisableAjax = false;
    public $filesAttributes = [];
    public $isValidate = true;
    public $attributes = [
        'id',
        'text' => 'name',
    ];
    protected function _run() {
        $response = $this->getResponse();
        if ($this->actionParams->isAjax && !$this->actionParams->isPjax && !$this->isDisableAjax) {
            $response->format = Response::FORMAT_JSON;
        }

        $result = $this->loadAndValidateForm();
        $response->content = $result;

        if ($result === true) {
            $this->trigger('afterValidate');
        }

        if (!($this->actionParams->isAjax && !$this->isDisableAjax) || $result === null) {
            $response->content = [
                'model' => $this->model,
            ];
        }

        return $response;
    }

    public function setModel($model) {
        if (is_string($model)) {
            $model = [
                'class' => $model,
            ];
        }

        if (is_array($model)) {
            $model = \Yii::createObject($model);
        }

        $this->_model = $model;
    }

    /**
     * @return Model
     */
    public function getModel() {
        return $this->_model;
    }

    /**
     * @return Model
     */
    protected function loadAndValidateForm()
    {
        $model = $this->getModel();
        $helper = new FormLoader();
        $helper->isValidate = $this->isValidate;
        $helper->data = $this->getData();
        $helper->model = $model;
        $helper->filesAttributes = $this->filesAttributes;
        $result = $helper->run();
        if ($this->isValidate && $result === false && $this->actionParams->isAjax && !$this->actionParams->isPjax && !$this->isDisableAjax) {
            return ActiveForm::validate($model);
        }

        return $result;
    }

    public function getData() {
        if (!$this->actionParams) {
            return;
        }

        if ($this->requestType === self::GET) {
            return $this->actionParams->get;
        } else {
            return $this->actionParams->post;
        }
    }
}