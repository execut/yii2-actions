<?php
/**
 * Created by PhpStorm.
 * User: execut
 * Date: 12/22/16
 * Time: 9:42 AM
 */

namespace execut\actions\action\adapter\gridView\handler;
use execut\actions\action\adapter\Response;
use execut\actions\TestCase;
use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;
use yii\db\Command;

class ModelTest extends TestCase
{
    public function setUp()
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $_SERVER['HTTP_REFERER'] = 'test';
    }

    public function tearDown()
    {
        parent::tearDown(); // TODO: Change the autogenerated stub
        $_SERVER['HTTP_REFERER'] = null;
    }

    public function testRun() {
        $query = $this->getMockBuilder(ActiveRecord::class)->setMethods(['select', 'queryAttribute', 'limit', 'createCommand'])->getMock();
        $query->method('select')->with('{{%test_model}}.id')->willReturn($query);

        $command = $this->getMockBuilder(Command::class)->setMethods(['queryColumn'])->getMock();
        $command->method('queryColumn')->willReturn([1, 2]);
        $query->method('createCommand')->willReturn($command);
        $query->expects($this->once())->method('limit')->with(65535)->willReturn($query);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $handler = new Model([
            'attributes' => ['attribute' => 'value'],
            'modelClass' => TestModel::class,
            'dataProvider' => $dataProvider,
            'successMessage' => '# records succesfull updated',
            'method' => 'updateAll',
        ]);
        $response = $handler->run();
        $this->assertEquals(['attribute' => 'value'], TestModel::$updatedAttributes);
        $this->assertEquals(['id' => [1,2]], TestModel::$conditions);

        $this->assertInstanceOf(\execut\actions\action\Response::class, $response);

        $this->assertInstanceOf(\yii\base\Response::class, $response->content);
        $this->assertEquals('test', $response->content->getHeaders()->get('Location'));

        $this->assertEquals([
            'kv-detail-success' => '22 records succesfull updated',
        ], $response->flashes);
    }
}

class TestModel extends ActiveRecord {
    public static $updatedAttributes = [];
    public static $conditions = [];
    public static function updateAll($attributes, $condition = '', $params = [])
    {
        self::$updatedAttributes = $attributes;
        self::$conditions = $condition;
        return 22; // TODO: Change the autogenerated stub
    }
}