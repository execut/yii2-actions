<?php
/**
 * User: execut
 * Date: 07.07.15
 * Time: 10:05
 */

namespace execut\actions\action\adapter;


use execut\actions\action\adapter\gridView\Handler;
use execut\actions\action\Response;
use execut\TestCase;
use yii\base\Model;
use yii\data\ArrayDataProvider;

class GridViewTest extends TestCase
{
    public function testRun()
    {
        $adapter = $this->getAdapter();

        $response = $adapter->run();


        $formValue = [
            $adapter->model->formName() => [
                'attribute' => 'test',
            ],
        ];
        $adapter->setActionParams([
            'get' => $formValue,
        ]);

        $this->assertEquals([
            'filter' => $adapter->model,
            'dataProvider' => $adapter->model->dataProvider,
        ], $response->content);

        $adapter->actionParams->isAjax = true;

        $adapter->actionParams->post = $formValue;
        $adapter->attributes = [
            'id',
            'text' => 'test_text'
        ];
        $response = $adapter->run();
        $this->assertEquals([
            'results' => [
                [
                    'id' => 1,
                    'text' => 'test',
                ]
            ]
        ], $response->content);
    }

    public function testSetHandlers() {
        $adapter = $this->getAdapter();
        $handler = $this->getMockBuilder(Handler::className())->setMethods(['run'])->getMockForAbstractClass();
        $adapter->handlers = [
            'test' => [
                'class' => $handler->className(),
            ],
        ];

        $handlers = $adapter->handlers;
        $this->assertInstanceOf($handler->className(), $handlers['test']);
    }

    public function testRunWithDataProviderHandler() {
        $adapter = $this->getAdapter();
        $adapter->setActionParams([
            'get' => [
                'handle' => 'delete',
            ],
        ]);
        $handler = $this->getMockBuilder(Handler::className())->setMethods(['run'])->getMockForAbstractClass();
        $handler->expects($this->once())->method('run')->willReturn(new Response());
        $adapter->handlers = [
            'delete' => $handler,
        ];
        $adapter->run();
        $this->assertEquals($adapter->model->dataProvider, $handler->dataProvider);

        $adapter->setActionParams([
            'get' => [
                'handle' => null,
            ],
        ]);
        $adapter->run();
    }

    /**
     * @return mixed|object|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getAdapter()
    {
        $dataProvider = new ArrayDataProvider();
        $dataProvider->models = [
            [
                'id' => 1,
                'test_text' => 'test',
            ]
        ];

        $filter = new GridViewTestFilter;
        $filter->dataProvider = $dataProvider;


        $adapter = $this->getMockBuilder(GridView::className())->setMethods(['getDefaultViewRendererConfig'])->getMock();
        $adapter->method('getDefaultViewRendererConfig')->willReturn([]);

        $adapter->attributes = [];


        $adapter->model = $filter;
        return $adapter;
    }
}

class GridViewTestFilter extends Model {
    public $attribute;
    protected $dataProvider = null;
    public function rules() {
        return [
            [['attribute'], 'required']
        ];
    }

    public function setDataProvider($dp) {
        $this->dataProvider = $dp;
    }

    public function getDataProvider() {
        return $this->dataProvider;
    }
}