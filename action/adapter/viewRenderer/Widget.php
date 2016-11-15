<?php
/**
 * User: execut
 * Date: 14.07.16
 * Time: 13:29
 */

namespace execut\actions\action\adapter\viewRenderer;

use execut\actions\action\adapter\ViewRenderer;
use execut\yii\helpers\ArrayHelper;

class Widget extends ViewRenderer
{
    public $widget = [];
    public $actionVars = [];
    protected function _run() {
        $widgetOptions = $this->getWidgetOptions();
        $widgetClass = $widgetOptions['class'];

        return $widgetClass::widget($widgetOptions);
    }

    public function getDefaultWidgetOptions() {
        return [];
    }

    public function getWidgetOptions() {
        $options = ArrayHelper::merge($this->getDefaultWidgetOptions(), $this->widget);
        $this->replaceActionVarsInOptions($options);

        return $options;
    }

    protected function replaceActionVarsInOptions(&$options) {
        foreach ($options as $key => &$option) {
            if (is_string($option) && strpos($option, '{') === 0) {
                $varName = str_replace(['{', '}'], '', $option);
                if (isset($this->actionVars[$varName])) {
                    $options[$key] = $this->actionVars[$varName];
                }
            } else if (is_array($option)) {
                $this->replaceActionVarsInOptions($option);
            }
        }
    }

    public function __set($name, $value)
    {
        $this->actionVars[$name] = $value;
    }

    public function __get($name)
    {
        if (isset($this->actionVars[$name])) {
            return $this->actionVars[$name];
        }
    }
}