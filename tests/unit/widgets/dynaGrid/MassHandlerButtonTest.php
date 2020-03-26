<?php


namespace execut\actions\widgets\dynaGrid;


use execut\actions\TestCase;
use execut\actions\widgets\DynaGrid;
use execut\actions\widgets\MassHandlerButton as MassHandlerButtonWidget;
use yii\base\Model;

class MassHandlerButtonTest extends TestCase
{
    public function testRender() {
        $model = new Model();
        $gridId = 'testId';
        $url = ['testUrl'];
        /**
         * @var MassHandlerButtonWidget $widget
         */
        $widget = $this->getMockBuilder(MassHandlerButtonWidget::class)->getMock();
        $widget->expects($this->once())
            ->method('run')
            ->willReturn('test');
        $button = new MassHandlerButton([
            'url' => $url
        ], $widget);

        $dynaGrid = new DynaGrid();
        $dynaGrid->id = $gridId;
        $dynaGrid->filter = $model;
        $this->assertEquals('test', $button->render($dynaGrid));
        $this->assertEquals($gridId . '-grid', $widget->gridId);
        $this->assertEquals($model, $widget->model);
        $this->assertEquals($url, $widget->url);
    }

    public function testGetWidgetByDefault() {
        $button = new MassHandlerButton([]);
        $this->assertInstanceOf(MassHandlerButtonWidget::class, $button->getWidget());
    }

    public function testGetWidgetFromConstructor() {
        $widget = new MassHandlerButtonWidget();
        $button = new MassHandlerButton([], $widget);
        $this->assertEquals(spl_object_hash($widget), spl_object_hash($button->getWidget()));
    }
}