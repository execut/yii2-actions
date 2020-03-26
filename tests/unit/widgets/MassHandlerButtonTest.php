<?php


namespace execut\actions\widgets;

use execut\actions\TestCase;
use yii\db\ActiveRecord;

class MassHandlerButtonTest extends TestCase
{
    public function testRender() {
        $url = ['/testUrl'];
        $gridId = 'gridId';
        $model = new TestModel();
        $widget = new MassHandlerButton([
            'id' => 'widget',
            'url' => $url,
            'gridId' => $gridId,
            'model' => $model,
        ]);
        $result = $widget->run();
        $clientOptions = $widget->clientOptions;
        $this->assertArrayHasKey('gridSelector', $clientOptions);
        $this->assertEquals('#' . $gridId, $clientOptions['gridSelector']);
        $this->assertArrayHasKey('idAttribute', $clientOptions);
        $this->assertEquals('ModelFormName[id]', $clientOptions['idAttribute']);
        $this->assertEquals('<div id="widget" class="mass-handler-button"><a class="btn btn-default glyphicon glyphicon-ok" href="/testUrl?ModelFormName%5BtestAttribute%5D=testAttributeValue"></a></div>', $result);
    }

    public function testChangeButtonType() {
        $model = new TestModel();
        $buttonType = 'danger';
        $widget = new MassHandlerButton([
            'buttonType' => $buttonType,
            'model' => $model,
        ]);
        $this->assertStringContainsString(' btn-' . $buttonType, $widget->run());
    }

    public function testChangeButtonIcon() {
        $model = new TestModel();
        $buttonIcon = 'trash';
        $widget = new MassHandlerButton([
            'buttonIcon' => $buttonIcon,
            'model' => $model,
        ]);
        $this->assertStringContainsString(' glyphicon-' . $buttonIcon, $widget->run());
    }

    public function testChangeButtonTitle() {
        $model = new TestModel();
        $buttonTitle = 'test title';
        $widget = new MassHandlerButton([
            'buttonTitle' => $buttonTitle,
            'model' => $model,
        ]);
        $this->assertStringContainsString(' title="' . $buttonTitle . '"', $widget->run());
    }

    public function testGetIdAttribute() {
        $model = new TestModel();
        $widget = new MassHandlerButton([
            'model' => $model,
        ]);
        $idAttribute = $widget->getIdAttribute();
        $this->assertEquals('ModelFormName[id]', $idAttribute);
    }

    public function testGetIdAttributeForCompositePrimaryKey() {
        $model = new TestModelWithCompositePrimaryKey();
        $widget = new MassHandlerButton([
            'model' => $model,
        ]);
        $idAttribute = $widget->getIdAttribute();
        $this->assertEquals('ModelFormName[pk]', $idAttribute);
    }
}

class TestModel extends ActiveRecord {
    public $testAttribute = 'testAttributeValue';
    public static function primaryKey()
    {
        return ['id'];
    }

    public function formName()
    {
        return 'ModelFormName';
    }

    public function attributes()
    {
        return [
            'testAttribute',
        ];
    }
}

class TestModelWithCompositePrimaryKey extends ActiveRecord {
    public static function primaryKey()
    {
        return ['key1_id', 'key2_id'];
    }

    public function formName()
    {
        return 'ModelFormName';
    }
}