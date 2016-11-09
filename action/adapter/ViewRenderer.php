<?php
/**
 * User: execut
 * Date: 14.07.16
 * Time: 13:25
 */

namespace execut\action\adapter;


use execut\action\Adapter;
use yii\base\Component;

abstract class ViewRenderer extends Component
{
    /**
     * @var Adapter
     */
    public $adapter = null;
    public $view = null;
    public function run() {
        return $this->_run();
    }
    
    abstract protected function _run();
}