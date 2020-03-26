<?php


namespace execut\actions\widgets\dynaGrid;


use execut\actions\widgets\DynaGrid;
use \execut\actions\widgets\MassHandlerButton as MassHandlerButtonWidget;

class MassHandlerButton extends ToolbarButton
{
    protected ?MassHandlerButtonWidget $widget = null;
    protected $widgetOptions = null;
    public function __construct(array $widgetOptions, MassHandlerButtonWidget $widget = null)
    {
        $this->widgetOptions = $widgetOptions;
        $this->widget = $widget;
        if ($this->widget === null) {
            $this->widget = new MassHandlerButtonWidget();
        }
    }

    public function getWidget() {
        return $this->widget;
    }

    public function render(DynaGrid $dynaGrid)
    {
        $widget = $this->widget;
        $widget->gridId = $dynaGrid->getGridId();
        $widget->model = $dynaGrid->filter;
        foreach ($this->widgetOptions as $key => $value) {
            $widget->$key = $value;
        }

        return $widget->run();
    }
}