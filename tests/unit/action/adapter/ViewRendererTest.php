<?php
/**
 * User: execut
 * Date: 14.07.16
 * Time: 12:18
 */

namespace execut\action\adapter;


use execut\TestCase;
use yii\base\View;

class ViewRendererTest extends TestCase
{
    public function testRun() {
        $adapter = $this->getMockForAbstractClass(ViewRenderer::className());
        $adapter->expects($this->once())->method('_run')->will($this->returnValue([]));

        $adapter->view = new View();
        $this->assertEquals([], $adapter->run());
//        $this->assertEquals($adapter, $adapter->view->adapter);
    }
}