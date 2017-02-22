<?php
/**
 * User: execut
 * Date: 06.07.15
 * Time: 17:51
 */

namespace execut;

use execut\actions\Action;
use execut\actions\action\Adapter;
use execut\actions\action\Params;
use yii\web\Controller;
use yii\base\Module;
use yii\web\Response;
use yii\web\Session;

class ActionTest extends \execut\actions\TestCase
{
    public $beforeRunTriggered = false;
    public $afterRunTriggered = false;
    public $beforeRenderTriggered = false;

    public function testGetParams() {
        $action = $this->getAction();
        $this->assertInstanceOf(Params::className(), $action->params);
    }

    public function testRun() {
        $action = $this->getAction();
        $adapter = $this->getMockForAbstractClass(Adapter::className());
        $action->controller->expects($this->once())->method('render')->with('test', ['test'])->will($this->returnValue('test'));

        $response = new \execut\actions\action\Response();
        $adapter->expects($this->exactly(2))->method('_run')->will($this->returnCallback(function () use ($adapter, $response) {
            $this->assertInstanceOf(Params::className(), $adapter->actionParams);
            $response->content = ['test'];
            return $response;
        }));

        $action->on('beforeRun', function () {
            $this->beforeRunTriggered = true;
        });
        $action->on('afterRun', function () {
            $this->afterRunTriggered = true;
        });
        $action->on('beforeRender', function () {
            $this->beforeRenderTriggered = true;
        });
        $action->adapter = $adapter;
        $action->view = 'test';

        $content = $action->run();
        $this->assertEquals('test', $content);
        $this->assertTrue($this->beforeRunTriggered);
        $this->assertTrue($this->afterRunTriggered);
        $this->assertTrue($this->beforeRenderTriggered);

        $response->format = Response::FORMAT_JSON;
        $content = $action->run();
        $this->assertEquals($response, $action->response);
        $this->assertEquals(Response::FORMAT_JSON, \yii::$app->response->format);
        $this->assertEquals(['test'], $content);
    }

    public function testGetAdapter() {
        $adapter = $this->getMockForAbstractClass(Adapter::className());
        $action = new Action('id', '');
        $action->adapter = [
            'class' => $adapter->className(),
        ];

        $this->assertInstanceOf($adapter->className(), $action->adapter);
    }

    public function testAddFlashes() {
        $action = $this->getAction();

        $response = new \execut\actions\action\Response([
            'flashes' => ['test'],
        ]);
        $adapter = $this->getMockForAbstractClass(Adapter::className());
        $adapter->method('_run')->will($this->returnCallback(function () use ($response) {
            return $response;
        }));
        $action->adapter = $adapter;

        $session = $this->getMockBuilder(Session::className())->setMethods([
            'setFlash',
        ])->getMock();
        $session->expects($this->once())->method('setFlash')->with(0, 'test');
        \yii::$app->set('session', $session);
        $action->run();
    }

    public function testRenderWithResponse() {
        $action = $this->getAction();

        $adapter = $this->getMockForAbstractClass(Adapter::className());
        $adapter->method('_run')->will($this->returnCallback(function () use ($adapter) {
            return new \execut\actions\action\Response([
                'content' => new Response()
            ]);
        }));
        $action->view = 'test';
        $action->adapter = $adapter;
        $this->assertInstanceOf(Response::className(), $action->run());
        $this->assertFalse(\yii::$app->layout);
    }

    /**
     * @return \execut\actions\Action
     */
    protected function getAction()
    {
        $controller = $this->getMockBuilder(Controller::className())->setMethods(['render'])->setConstructorArgs(['id', new Module('id')])->getMock();

        $action = new Action('id', $controller);
        return $action;
    }
}