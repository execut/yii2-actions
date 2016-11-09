<?php
/**
 * User: execut
 * Date: 07.07.15
 * Time: 10:05
 */

namespace execut\action\adapter;


use execut\TestCase;
use yii\base\Event;
use yii\base\Model;
use yii\data\ArrayDataProvider;
use yii\web\Response;

class FormTest extends TestCase
{
    public function testSetModel() {
        $adapter = new Form();
        $adapter->model = [
            'class' => GridViewTestFilter::className(),
        ];
        $this->assertInstanceOf(GridViewTestFilter::className(), $adapter->model);

        $adapter->model = GridViewTestFilter::className();
        $this->assertInstanceOf(GridViewTestFilter::className(), $adapter->model);
    }

    public function testGetData() {
        $adapter = new Form();
        $this->assertNull($adapter->getData());
        $adapter->setActionParams([
            'get' => 'get',
            'post' => 'post',
        ]);
        $this->assertEquals('get', $adapter->getData());
        $adapter->requestType = 'post';
        $this->assertEquals('post', $adapter->getData());
    }

    public function testOutputVars() {
        $filter = new GridViewTestFilter;

        $formValue = [
            $filter->formName() => [
                'attribute' => 'test',
            ],
        ];

        $adapter = new Form();
        $adapter->attributes = [];
        $adapter->setActionParams([
            'get' => $formValue,
        ]);
        $adapter->model = $filter;
        $response = $adapter->run();
        $this->assertEquals([
            'model' => $filter,
        ], $response->content);
    }

    public function testAjaxValidate() {
        $model = new GridViewTestFilter;
        $adapter = new Form;
        $adapter->model = $model;
        $adapter->setActionParams([
            'isAjax' => true,
            'get' => [
                $model->formName() => [
                    'attribute' => '',
                ],
            ],
        ]);

        $response = $adapter->run();
        $this->assertEquals([
            'gridviewtestfilter-attribute' => [
                'Attribute cannot be blank.'
            ]
        ], $response->content);
        $this->assertEquals(Response::FORMAT_JSON, $response->format);

        $adapter->actionParams->get = [
            $model->formName() => [
                'attribute' => 'value'
            ],
        ];

        $response = $adapter->run();
        $this->assertTrue($response->content);
    }

    protected $event = null;
    public function testAfterValidate() {
        $model = new FormTestFilter();
        $adapter = new Form;
        $adapter->model = $model;
        $adapter->setActionParams([
            'isAjax' => true,
            'get' => [
                $model->formName() => [
                    'attribute' => 'test',
                ],
            ],
        ]);
        $this->event = null;
        $adapter->on('afterValidate', function ($event) {
            $this->event = $event;
        });
        $adapter->run();
        $this->assertInstanceOf(Event::className(), $this->event);
    }
}

class FormTestFilter extends Model {
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