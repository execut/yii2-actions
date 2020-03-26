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
        $adapter = $this->getMockForAbstractClass(Adapter::class);
        $response = new \execut\actions\action\Response();
        $adapter->expects($this->once())->method('_run')->will($this->returnValue($response));

        $this->assertEquals($response, $adapter->run());
        $this->assertEquals($response, $adapter->response);
    }

    public function testWithView() {
        $adapter = $this->getMockForAbstractClass(Adapter::class);
        $response = new \execut\actions\action\Response([
            'content' => [
                'testProperty' => 'testValue',
            ],
        ]);

        $adapter->expects($this->once())->method('_run')->will($this->returnValue($response));
        $view = new AdapterTestView();
        $adapter->view = $view;
        $result = $adapter->run();
        $this->assertInstanceOf(\execut\actions\action\Response::class, $result);
        $this->assertEquals('run result', $result->content);
        $this->assertEquals('testValue', $view->testProperty);
    }

    public function testSetViewFromConfig() {
        $adapter = $this->getMockForAbstractClass(Adapter::class);
        $adapter->view = [
            'class' => AdapterTestView::class,
        ];
        $this->assertInstanceOf(AdapterTestView::class, $adapter->view);
    }

    public function testGetDefaultViewConfig() {
        $adapter = $this->getMockBuilder(Adapter::class)->setMethods(['getDefaultViewRendererConfig', '_run'])->getMock();
        $adapter->method('getDefaultViewRendererConfig')
            ->willReturn([
                'class' => AdapterTestView::class,
            ]);
        $this->assertInstanceOf(AdapterTestView::class, $adapter->view);
    }

    public function testGetData() {
        $adapter = $this->getMockForAbstractClass(Adapter::class);
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
        $adapter = $this->getMockForAbstractClass(Adapter::class);
        $adapter->setActionParams([]);
        $adapterNew = $adapter->createFromAdapter($adapter);
        $this->assertInstanceOf(Adapter::class, $adapterNew);
    }

    public function testSetActionParams() {
        $adapter = $this->getMockForAbstractClass(Adapter::class);
        $adapter->setActionParams([
            'get' => 'test'
        ]);
        $this->assertEquals('test', $adapter->actionParams->get);
    }

    public function testGetUniqueId() {
        $params = $this->getMockBuilder(Params::class)->setMethods(['getUniqueId'])->getMock();
        $params->method('getUniqueId')->willReturn('test');
        $adapter = $this->getMockForAbstractClass(Adapter::class);
        $adapter->actionParams = $params;
        $this->assertEquals('test', $adapter->uniqueId);
    }

    public function testRenderWithResponse() {
        $adapter = $this->getMockForAbstractClass(Adapter::class);
        $adapter->method('_run')->will($this->returnCallback(function () use ($adapter) {
            return new \execut\actions\action\Response([
                'content' => new Response([
                    'charset' => 'utf8'
                ]),
            ]);
        }));
        $adapter->setView($this->getMockForAbstractClass(ViewRenderer::class));

        $this->assertInstanceOf(Response::class, $adapter->run()->content);
    }

//    public function testRenderWithViewString() {
//        $adapter = $this->getMockForAbstractClass(Adapter::class);
//        $adapter->method('_run')->will($this->returnCallback(function () use ($adapter) {
//            return new \execut\actions\action\Response([
//                'content' => [
//                    'test' => 'test',
//                ],
//            ]);
//        }));
//        $adapter->setView('test');
//
//        $this->assertInstanceOf(Response::class, $adapter->run()->content);
//    }
}

class AdapterTestView extends ViewRenderer {
    public $testProperty = null;
    protected function _run() {
        return 'run result';
    }
}