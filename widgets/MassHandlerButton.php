<?php
/**
 * Created by PhpStorm.
 * User: execut
 * Date: 3/13/19
 * Time: 4:15 PM
 */

namespace execut\actions\widgets;


use yii\helpers\Html;
use execut\yii\jui\Widget;

class MassHandlerButton extends Widget
{
    public $url = null;
    public $model = null;
    public $gridId = null;
    public $buttonType = 'default';
    public $buttonIcon = 'ok';
    public $buttonTitle = null;
    public function run()
    {
        parent::run();
        $this->clientOptions['gridSelector'] = '#' . $this->gridId;
        $this->clientOptions['idAttribute'] = $this->getIdAttribute();
        $this->registerWidget();
        $url = $this->url;
        if ($url !== null) {
            $url[$this->model->formName()] = $this->model->attributes;
        }

        return $this->_renderContainer(Html::a('', $url, [
            'class' => 'btn btn-' . $this->buttonType . ' glyphicon glyphicon-' . $this->buttonIcon,
            'title' => $this->buttonTitle,
        ]));
    }

    public function getIdAttribute() {
        $primaryKey = $this->model->primaryKey();
        if (count($primaryKey) == 1) {
            $formKey = '[' . current($primaryKey) . ']';
        } else {
            $formKey = '[pk]';
        }

        $idAttribute = $this->model->formName() . $formKey;

        return $idAttribute;
    }
}