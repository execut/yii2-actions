<?php
/**
 * User: execut
 * Date: 07.07.15
 * Time: 10:05
 */

namespace execut\action\adapter;


use execut\TestCase;
use yii\base\Model;
use yii\data\ArrayDataProvider;

class GridViewTest extends TestCase
{
    public function testRun()
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

        $formValue = [
            $filter->formName() => [
                'attribute' => 'test',
            ],
        ];

        $adapter = $this->getMockBuilder(GridView::className())->setMethods(['getDefaultViewRendererConfig'])->getMock();
        $adapter->method('getDefaultViewRendererConfig')->willReturn([]);

        $adapter->attributes = [];
        $adapter->setActionParams([
            'get' => $formValue,
        ]);
        $adapter->model = $filter;
        $response = $adapter->run();
        $this->assertEquals([
            'filter' => $filter,
            'dataProvider' => $dataProvider,
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