<?php
/**
 * User: execut
 * Date: 07.07.15
 * Time: 10:38
 */

namespace execut\actions\action;


use execut\actions\action\adapter\ViewRenderer;
use yii\base\Component;
use yii\helpers\ArrayHelper;
use yii\web\Response;

abstract class Adapter extends Component
{
    public $flashes = [];
    public $action = null;
    /**
     * @var Params
     */
    public $actionParams = null;
    /**
     * @var \execut\actions\action\Response
     */
    protected $response = null;

    /**
     * @var ViewRenderer $view
     */
    protected $view = [];
    public function run() {
        if ($this->actionParams === null) {
            $this->actionParams = new Params;
        }

        $response = $this->_run();
        if ($response->format === 'json') {
            return $response;
        }

        if ($response->content instanceof Response) {
            return $response;
        }

        if ($view = $this->getView()) {
            if (is_array($response->content)) {
                foreach ($response->content as $key => $value) {
                    $view->$key = $value;
                }
            }

            $response->content = $view->run();
        }

        return $response;
    }

    public function getResponse($params = null) {
        if ($this->response === null) {
            $this->response = new \execut\actions\action\Response($params);
        }

        return $this->response;
    }

    public function getData() {
        if ($this->actionParams) {
            return $this->actionParams->getData();
        }
    }

    abstract protected function _run();

    public function getDefaultViewRendererConfig() {
        return [];
    }

    public function setView($view) {
        $this->view = $view;
    }

    public function getView() {
        $view = $this->view;
        if (is_array($view)) {
            $view = ArrayHelper::merge($this->getDefaultViewRendererConfig(), $view);
            if (empty($view)) {
                return null;
            }

            $view = \yii::createObject($view);

            $view->adapter = $this;
            $this->view = $view;
        }

        return $view;
    }

    public static function createFromAdapter($adapter, $config = []) {
        $newAdapter = new static($config);
        $newAdapter->actionParams = $adapter->actionParams;

        return $newAdapter;
    }

    public function setActionParams($params) {
        if (is_array($params)) {
            if (!isset($params['class'])) {
                $params['class'] = Params::className();
            }

            $params = \yii::createObject($params);
        }

        $this->actionParams = $params;
    }

    public function getUniqueId() {
        return $this->actionParams->getUniqueId();
    }

    /**
     * @TODO Block for extraction
     */
    public function setModelsFinder($finder) {
        $this->_finder = $finder;

        return $this;
    }

    protected $_finder = null;

    /**
     * @return ModelsFinder
     */
    public function getModelsFinder() {
        $finder = $this->_finder;
        if ($finder === null) {
            $finder = [];
        }

        if (is_array($finder)) {
            $finder = ArrayHelper::merge([
                'class' => ModelsFinder::class,
                'modelClass' => $this->modelClass,
                'actionParams' => $this->actionParams,
            ], $finder);

            $finder = \yii::createObject($finder);
            $this->_finder = $finder;
        }

        return $this->_finder;
    }

    protected $_model = null;
    public function setModel($model) {
        if (is_string($model)) {
            $model = ['class' => $model];
        }

        if (is_array($model)) {
            $model = \yii::createObject($model);
        }

        $this->_model = $model;
    }

    public function getModel() {
        if ($this->_model !== null) {
            return $this->_model;
        }

        $model = $this->modelsFinder->find();

        return $this->_model = $model;
    }


    /**
     * end bock for extraction
     */
}