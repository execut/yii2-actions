<?php
/**
 * User: execut
 * Date: 14.07.16
 * Time: 13:25
 */

namespace execut\actions\action\adapter;


use execut\actions\action\Adapter;
use yii\base\Component;

abstract class ViewRenderer extends Component
{
    /**
     * @var Adapter
     */
    public $adapter = null;
    public $view = null;
    public function run() {
        \yii::beginProfile('Render action', 'execut.yii2-actions');
        $result = $this->_run();
        \yii::endProfile('Render action', 'execut.yii2-actions');

        return $result;
    }
    
    abstract protected function _run();
}