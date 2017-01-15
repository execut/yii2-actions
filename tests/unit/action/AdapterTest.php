<?php
/**
 * User: execut
 * Date: 07.07.15
 * Time: 10:05
 */

namespace execut\actions\action;


use execut\actions\action\adapter\ViewRenderer;
use execut\actions\action\adapter\ViewRendererTest;
use execut\actions\TestCase;
use yii\web\Response;

class AdapterTest extends TestCase
{
    public function testRender() {
        $adapter = $this->getMockForAbstractClass(Adapter::className());
        $response = new \execut\actions\action\Response();
        $adapter->expects($this->once())->method('_run')->will($this->returnValue($response));

        $this->assertEquals($response, $adapter->run());
        $this->assertEquals($response, $adapter->response);
    }

    public function testWithView() {
        $adapter = $this->getMockForAbstractClass(Adapter::className());
        $response = new \execut\actions\action\Response([
            'content' => [
                'testProperty' => 'testValue',
            ],
        ]);

        $adapter->expects($this->once())->method('_run')->will($this->returnValue($response));
        $view = new AdapterTestView();
        $adapter->view = $view;
        $result = $adapter->run();
        $this->assertInstanceOf(\execut\actions\action\Response::className(), $result);
        $this->assertEquals('run result', $result->content);
        $this->assertEquals('testValue', $view->testProperty);
    }

    public function testSetViewFromConfig() {
        $adapter = $this->getMockForAbstractClass(Adapter::className());
        $adapter->view = [
            'class' => AdapterTestView::className(),
        ];
        $this->assertInstanceOf(AdapterTestView::className(), $adapter->view);
    }

    public function testGetDefaultViewConfig() {
        $adapter = $this->getMockBuilder(Adapter::className())->setMethods(['getDefaultViewRendererConfig', '_run'])->getMock();
        $adapter->method('getDefaultViewRendererConfig')
            ->willReturn([
                'class' => AdapterTestView::className(),
            ]);
        $this->assertInstanceOf(AdapterTestView::className(), $adapter->view);
    }

    public function testGetData() {
        $adapter = $this->getMockForAbstractClass(Adapter::className());
        $data = [
            'test' => 'test'
        ];
        $adapter->setActionParams([
            'post' => $data
        ]);
        $this->assertEquals($data, $adapter->data);
        $adapter->setActionParams([
            'get' => $data
        ]);
        $this->assertEquals($data, $adapter->data);
    }

    public function testCreateFromAdapter() {
        $adapter = $this->getMockForAbstractClass(Adapter::className());
        $adapter->setActionParams([]);
        $adapterNew = $adapter->createFromAdapter($adapter);
        $this->assertInstanceOf(Adapter::className(), $adapterNew);
    }

    public function testSetActionParams() {
        $adapter = $this->getMockForAbstractClass(Adapter::className());
        $adapter->setActionParams([
            'get' => 'test'
        ]);
        $this->assertEquals('test', $adapter->actionParams->get);
    }

    public function testGetUniqueId() {
        $params = $this->getMockBuilder(Params::className())->setMethods(['getUniqueId'])->getMock();
        $params->method('getUniqueId')->willReturn('test');
        $adapter = $this->getMockForAbstractClass(Adapter::className());
        $adapter->actionParams = $params;
        $this->assertEquals('test', $adapter->uniqueId);
    }

    public function testRenderWithResponse() {
        $adapter = $this->getMockForAbstractClass(Adapter::className());
        $adapter->method('_run')->will($this->returnCallback(function () use ($adapter) {
            return new \execut\actions\action\Response([
                'content' => new Response(),
            ]);
        }));
        $adapter->setView($this->getMockForAbstractClass(ViewRenderer::className()));

        $this->assertInstanceOf(Response::className(), $adapter->run()->content);
    }

//    public function testRenderWithViewString() {
//        $adapter = $this->getMockForAbstractClass(Adapter::className());
//        $adapter->method('_run')->will($this->returnCallback(function () use ($adapter) {
//            return new \execut\actions\action\Response([
//                'content' => [
//                    'test' => 'test',
//                ],
//            ]);
//        }));
//        $adapter->setView('test');
//
//        $this->assertInstanceOf(Response::className(), $adapter->run()->content);
//    }
}

class AdapterTestView extends ViewRenderer {
    public $testProperty = null;
    protected function _run() {
        return 'run result';
    }
}