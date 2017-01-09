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
    public $actionParams = null;
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
            foreach ($response->content as $key => $value) {
                $view->$key = $value;
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
            $view = ArrayHelper::merge($view, $this->getDefaultViewRendererConfig());
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
}