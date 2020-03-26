<?php


namespace execut\actions\widgets;

use execut\actions\widgets\dynaGrid\ToolbarButton;
use execut\actions\TestCase;

class DynaGridTest extends TestCase
{
    public function testSetToolbarButtons() {
        $widget = new DynaGrid([
            'toolbarButtons' => [
                [
                ],
            ]
        ]);
        $buttons = $widget->toolbarButtons;
        $this->assertCount(1, $buttons);
        $button = $buttons[0];
        $this->assertInstanceOf(ToolbarButton::class, $button);
    }

    public function testRenderToolbarButtons() {
        $button = $this->getMockBuilder(ToolbarButton::class)->getMock();
        $button->method('render')
            ->willReturn('test');
        $widget = new DynaGrid([
            'toolbarButtons' => [
                'test' => $button,
            ]
        ]);
        $toolbarConfig = $widget->getToolbarConfig();
        $this->assertIsArray($toolbarConfig);
        $this->assertArrayHasKey('test', $toolbarConfig);
        $this->assertIsArray($toolbarConfig['test']);
        $this->assertArrayHasKey('content', $toolbarConfig['test']);
        $this->assertEquals('test', $toolbarConfig['test']['content']);
    }
}