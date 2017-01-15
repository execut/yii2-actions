<?php
/**
 * User: execut
 * Date: 07.07.15
 * Time: 10:05
 */

namespace execut\actions\action\adapter;


use execut\actions\TestCase;
use execut\actions\action\Adapter;
use execut\actions\action\adapter\ViewRenderer;
use execut\actions\action\adapter\ViewRendererTest;

class StackTest extends TestCase
{
//    public function testSetAdapters()
//    {
//        $adapter = $this->getMockForAbstractClass(Adapter::className());
//        $adapter->expects($this->once())->method('_run')->will($this->returnValue(''));
//        $stack = new Stack();
//        $stack->adapters = [
//            $adapter
//        ];
//
//        $stack->setActionParams([
//            'post' => []
//        ]);
//        $this->assertEquals('', $stack->run());
//        $this->assertEquals([], $adapter->actionParams->post);
//    }
//
//    public function testGetDefaultAdapters() {
//        $stack = new Stack();
//        $stack->defaultAdapters = [
//            'default' => [
//                'class' => Stack::className(),
//            ]
//        ];
//
//        $this->assertInstanceOf(Stack::className(), $stack->getAdapter('default'));
//    }
}