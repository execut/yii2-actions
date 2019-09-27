<?php
/**
 * User: execut
 * Date: 14.07.16
 * Time: 16:15
 */

namespace execut\actions\action\adapter;
use execut\actions\Bootstrap;
use execut\actions\TestCase;
use yii\db\ActiveRecord;
use yii\helpers\Url;
use yii\web\Response;
use yii\web\Session;
use yii\web\UploadedFile;

class EditTest extends TestCase
{
    public function testRunWithNewRecord() {
        $action = $this->getMockBuilder(Edit::class)->setMethods(['getDefaultViewRendererConfig'])->getMock();
        $action->method('getDefaultViewRendererConfig')->willReturn([]);
        $action->modelClass = TestModel::class;
        $response = $action->run();
        $vars = $response->content;
        $this->assertArrayHasKey('model', $vars);
        $this->assertArrayHasKey('mode', $vars);
        $this->assertInstanceOf(TestModel::class, $vars['model']);
        $this->assertEquals('edit', $vars['mode']);
    }

    public function testCreateNewRecord() {
        Bootstrap::initI18N();
        $action = $this->getMockBuilder(Edit::class)->setMethods(['getDefaultViewRendererConfig'])->getMock();
        $session = $this->getMockBuilder(Session::class)->setMethods(['addFlash'])->getMock();
        $session->expects($this->once())->method('addFlash')
            ->with('kv-detail-success', 'Record #1 is successfully updated');

        $action->session = $session;
        $action->method('getDefaultViewRendererConfig')->willReturn([]);
        $action->modelClass = TestModel::class;
        $action->setActionParams([
            'controller' => 'test',
            'post' => [
                'savedAttribute' => 'testName',
            ]
        ]);
        $response = $action->run();
        $this->assertInstanceOf(Response::class, $response->content);

        $model = $action->model;
        $this->assertTrue($model->saveIsCalled, 'Check what save is called');

        $this->assertEquals(Url::to([
            $action->actionParams->uniqueId,
            'id' => $model->id,
        ], true), $response->content->getHeaders()->get('Location'));
    }

    public function testEdit() {
        $action = $this->getMockBuilder(Edit::class)->setMethods(['getDefaultViewRendererConfig'])->getMock();
        $action->method('getDefaultViewRendererConfig')->willReturn([]);
        $action->modelClass = TestModel::class;
        $action->setActionParams([
            'get' => [
                'id' => '1',
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
        $action = $this->getMockBuilder(Edit::class)->setMethods(['getDefaultViewRendererConfig'])->getMock();
        $action->method('getDefaultViewRendererConfig')->willReturn([]);
        $action->modelClass = TestModel::class;
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
    public $primaryKey = 1;
    public function rules() {
        return [
            [['savedAttribute'], 'required'],
            [['content', 'addinalAttribute'], 'safe'],
        ];
    }

    public static function primaryKey()
    {
        return 'id';
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

    public static function find() {
        return new self;
    }

    public function one() {
        self::$findIsCalled = true;
        return $this;
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