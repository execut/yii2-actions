<?php
/**
 * User: execut
 * Date: 06.07.15
 * Time: 17:51
 */

namespace execut\action;


use execut\TestCase;
use execut\Action;
use execut\action\Adapter;
use execut\yii\web\Controller;
use yii\base\Module;
use yii\web\Response;
use yii\web\Session;

class ParamsTest extends TestCase
{
    public function testGetUniqueId() {
        $params = new Params();
        $params->module = 'module';
        $params->controller = 'controller';
        $params->action = 'action';
        $this->assertEquals('/module/controller/action', $params->getUniqueId());
    }

    public function testCreateFromAction() {
        $_POST = [
            'testPost' => 'testPost',
        ];
        $_GET = [
            'r' => 'blabla',
            'testGet' => 'testGet',
        ];

        $_FILES = [
            'testFile' => 'testFile',
        ];

        $action = $this->getAction();

        $controller = $action->controller;

        $controller->expects($this->any())->method('getPost')->will($this->returnValue($_POST));
        $controller->expects($this->any())->method('getGet')->will($this->returnValue($_GET));
        $controller->expects($this->any())->method('getFiles')->will($this->returnValue($_FILES));
        $controller->expects($this->any())->method('isAjax')->willReturn(true);
        $controller->expects($this->any())->method('isPjax')->willReturn(true);

        $params = Params::createFromAction($action);

        $this->assertEquals($_POST, $params->post);
        $this->assertEquals($_GET, $params->get);
        $this->assertEquals($_FILES, $params->files);
        $this->assertTrue($params->isPjax);
        $this->assertTrue($params->isAjax);
        $this->assertEquals('id', $params->action);
    }

    public function testGetData() {
        $params = new Params([
            'post' => 'test'
        ]);
        $this->assertEquals('test', $params->data);

        $params->post = null;
        $params->get = 'test';
        $this->assertEquals('test', $params->data);
    }

    public function testGetUniqueIdWithoutAction() {
        $params = new Params([
            'module' => 'module',
            'controller' => 'controller',
            'action' => 'action'
        ]);
        $this->assertEquals('/module/controller', $params->getUniqueIdWithoutAction());
    }

    /**
     * @return \execut\Action
     */
    protected function getAction()
    {
        $controller = $this->getMockBuilder(Controller::className())->setMethods(['render', 'getPost', 'getGet', 'isAjax', 'isPjax', 'getFiles'])->setConstructorArgs(['id', new Module('id')])->getMock();

        $action = new Action('id', $controller);
        return $action;
    }
}