<?php
/**
 * User: execut
 * Date: 07.07.15
 * Time: 11:26
 */

namespace execut\actions\action\adapter;


use execut\actions\action\Adapter;
use execut\actions\action\adapter\helper\FormLoader;
use execut\yii\helpers\ArrayHelper;
use execut\yii\helpers\Html;
use yii\base\Model;
use yii\data\ArrayDataProvider;
use yii\web\Response;
use yii\widgets\ActiveForm;

/**
 * Class GridView
 * @package execut\actions\action
 * @property Model $filter
 */
class Stack extends Adapter
{
    public $adapters = [];
    protected $initedAdapters = null;
    public $defaultAdapters;

    public function getAdapters() {
        $this->initAdapters();

        return $this->initedAdapters;
    }

    public function getAdapter($key) {
        $adapters = $this->getAdapters();

        return $adapters[$key];
    }

    protected function _run()
    {
        $result = '';
        foreach ($this->getAdapters() as $adapter) {
            $adapter->actionParams = $this->actionParams;
            $result .= $adapter->run();
        }

        return $result;
    }

    public function getDefaultAdapters() {
        return $this->defaultAdapters;
    }

    /**
     * @return array
     */
    protected function initAdapters()
    {
        if ($this->initedAdapters === null) {
            $adapters = ArrayHelper::merge($this->getDefaultAdapters(), $this->adapters);
            foreach ($adapters as $key => $adapter) {
                if (is_array($adapter)) {
                    $adapter = \yii::createObject($adapter);
                }

                $adapters[$key] = $adapter;
            }

            $this->initedAdapters = $adapters;
        }
    }
}