<?php
/**
 * User: execut
 * Date: 14.07.16
 * Time: 12:18
 */

namespace execut\actions\action\adapter;


use execut\actions\TestCase;
use yii\base\View;

class ViewRendererTest extends TestCase
{
    public function testRun() {
        $adapter = $this->getMockForAbstractClass(ViewRenderer::class);
        $adapter->expects($this->once())->method('_run')->will($this->returnValue([]));

        $adapter->view = new View();
        $this->assertEquals([], $adapter->run());
//        $this->assertEquals($adapter, $adapter->view->adapter);
    }
}