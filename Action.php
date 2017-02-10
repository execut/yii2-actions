<?php
/**
 * User: execut
 * Date: 06.07.15
 * Time: 17:51
 */

namespace execut\actions;


use execut\actions\action\Adapter;
use execut\actions\action\Params;
use yii\web\Response;

/**
 * Class Action
 *
 * @property Adapter $adapter
 * @package execut\actions
 */
class Action extends \yii\base\Action
{
    /**
     * @var Adapter
     */
    protected $_adapter;
    protected $_view;
    public $flashes = [];
    public $response = null;
    public function setView($view) {
        if (is_array($view)) {
            $view = \Yii::createObject($view);
        }

        $this->_view = $view;
    }

    public function getView() {
        return $this->_view;
    }

    public function setAdapter($adapter) {
        if (is_array($adapter)) {
            $adapter = \Yii::createObject($adapter);
        }

        $adapter->action = $this;

        $this->_adapter = $adapter;
    }

    /**
     * @return Adapter
     */
    public function getAdapter() {

        return $this->_adapter;
    }

    public function run() {
        $this->trigger('beforeRun');
        if ($adapter = $this->adapter) {
            $adapter->actionParams = $this->getParams();

            $response = $adapter->run();
            $this->response = $response;
            if ($response->flashes) {
                foreach ($response->flashes as $key => $flash) {
                    $this->addFlash($key, $flash);
                }
            }

            if ($response->content instanceof Response) {
                \yii::$app->layout = false;
                return $response->content;
            }
        } else {
            $response = [];
        }

        $this->trigger('beforeRender');
        if ($adapter) {
            if ($response->format === Response::FORMAT_HTML) {
                if ($view = $this->view) {
                    $result = $this->controller->render($view, $response->content);
                } else {
                    $result = $this->controller->renderContent($response->content);
                }
            } else {
                \yii::$app->response->format = $response->format;
                $result = $response->content;
            }
        } else {
            $result = $response;
        }

        $this->trigger('afterRun');

        return $result;
    }

    protected function addFlash($key, $flash) {
        \yii::$app->session->setFlash($key, $flash);
    }

    public function getParams() {
        return Params::createFromAction($this);
    }
}