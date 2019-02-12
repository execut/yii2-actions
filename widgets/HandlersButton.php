<?php
/**
 * Created by PhpStorm.
 * User: execut
 * Date: 2/7/19
 * Time: 11:46 AM
 */

namespace execut\actions\widgets;

use execut\yii\jui\Widget;
use yii\helpers\Html;
use yii\helpers\Url;

class HandlersButton extends Widget
{
    public $confirmMessage = null;
    public $totalCount = null;
    public $icon = null;
    public $type = null;
    public $label = null;
    public $url = null;
    public $gridId = null;
    public $idAttribute = null;
    public function run()
    {
        parent::run();
        $this->clientOptions = [
            'totalCount' => $this->totalCount,
            'confirmMessage' => $this->confirmMessage,
            'url' => Url::to($this->url),
            'gridSelector' => '#' . $this->gridId,
            'idAttribute' => $this->idAttribute,
        ];
        $this->registerWidget();
        return Html::tag('i', '', [
            'id' => $this->id,
            'class' => 'btn btn-' . $this->type . ' glyphicon glyphicon-' . $this->icon,
            'type' => 'button',
            'data-pjax' => 0,
            'title' => $this->label,
        ]);
    }
}