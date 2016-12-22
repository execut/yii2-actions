<?php
/**
 * User: execut
 * Date: 14.07.16
 * Time: 16:15
 */

namespace execut\actions\action\adapter;
use execut\TestCase;
use yii\db\ActiveRecord;
use yii\helpers\Url;
use yii\web\Response;
use yii\web\UploadedFile;

class EditTest extends TestCase
{
    public function testRunWithNewRecord() {
        $action = $this->getMockBuilder(Edit::className())->setMethods(['getDefaultViewRendererConfig'])->getMock();
        $action->method('getDefaultViewRendererConfig')->willReturn([]);
        $action->modelClass = TestModel::className();
        $response = $action->run();
        $vars = $response->content;
        $this->assertArrayHasKey('model', $vars);
        $this->assertArrayHasKey('mode', $vars);
        $this->assertInstanceOf(TestModel::className(), $vars['model']);
        $this->assertEquals('edit', $vars['mode']);
    }

    public function testCreateNewRecord() {
        $action = $this->getMockBuilder(Edit::className())->setMethods(['getDefaultViewRendererConfig'])->getMock();
        $action->method('getDefaultViewRendererConfig')->willReturn([]);
        $action->modelClass = TestModel::className();
        $action->setActionParams([
            'controller' => 'test',
            'post' => [
                'savedAttribute' => 'testName',
            ]
        ]);
        $response = $action->run();
        $this->assertInstanceOf(Response::className(), $response->content);

        $model = $action->model;
        $this->assertTrue($model->saveIsCalled, 'Check what save is called');

        $this->assertEquals([
            'kv-detail-success' => 'Record ' . $model . ' updated',
        ], $response->flashes);

        $this->assertEquals(Url::to([
            $action->actionParams->uniqueId,
            'id' => $model->id,
        ], true), $response->content->getHeaders()->get('Location'));
    }

    public function testEdit() {
        $action = $this->getMockBuilder(Edit::className())->setMethods(['getDefaultViewRendererConfig'])->getMock();
        $action->method('getDefaultViewRendererConfig')->willReturn([]);
        $action->modelClass = TestModel::className();
        $action->setActionParams([
            'get' => [
                'id' => 1,
            ]
        ]);
//        $action->filesAttributes = [
//            'content' => 'contentFile',
//        ];
        $action->run();
        $model = $action->model;
        $this->assertTrue(TestModel::$findIsCalled);
//        $this->assertEquals(['savedAttribute', 'addinalAttribute'], $model->selectAttributes);
    }

    public function testWithAdditionalAttributes() {
        $action = $this->getMockBuilder(Edit::className())->setMethods(['getDefaultViewRendererConfig'])->getMock();
        $action->method('getDefaultViewRendererConfig')->willReturn([]);
        $action->modelClass = TestModel::className();
        $action->additionalAttributes = [
            'addinalAttribute',
        ];

        $action->setActionParams([
            'post' => [
                'savedAttribute' => 'testName',
                'addinalAttribute' => 'testValue',
            ]
        ]);
        $response = $action->run();
        $response = $response->content;
        $model = $action->model;
        $this->assertEquals('testValue', $model->addinalAttribute);
        $this->assertEquals(Url::to([
            $action->actionParams->uniqueId,
            'id' => $model->id,
            'addinalAttribute' => 'testValue',
        ], true), $response->getHeaders()->get('Location'));
    }

//    public function testRelations() {
//
//    }
}

class TestFileModel extends TestModel {
    public $testFile = null;
    public $testContentFile = null;
    public function rules() {
        return [
            [['testContentFile'], 'required'],
            [['testFile'], 'file', 'skipOnEmpty' => false, 'extensions' => 'txt'],
        ];
    }
}

class TestModel extends ActiveRecord {
    public $id = 1;
    public $savedAttribute = null;
    public $addinalAttribute = null;
    public $content = null;
    public $isNewRecord = false;
    public function rules() {
        return [
            [['savedAttribute'], 'required'],
            [['content', 'addinalAttribute'], 'safe'],
        ];
    }

    public function attributes()
    {
        return [
            'savedAttribute',
            'content',
            'addinalAttribute',
        ];
    }

    public $selectAttributes = null;
    public function select($attributes) {
        $this->selectAttributes = $attributes;
        return $this;
    }

    public function formName()
    {
        return '';
    }

    public static $findIsCalled = false;

    public static function findByPk() {
        self::$findIsCalled = true;
        return new self;
    }

    public $where = null;
    public function andWhere($where) {
        $where = $this->where;
        return $this;
    }

    public $saveIsCalled = false;
    public function save($runValidation = true, $attributeNames = NULL) {
        $this->saveIsCalled = true;
        return true;
    }

    public function __toString() {
        return '__toString model name';
    }

//    public function findOne() {
//        return $this;
//    }
}