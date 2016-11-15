<?php
/**
 * Created by PhpStorm.
 * User: execut
 * Date: 19.08.16
 * Time: 14:03
 */

namespace execut\actions\action;


use yii\base\Component;

class Response extends Component
{
    public $format = \yii\web\Response::FORMAT_HTML;
    public $content = null;
    public $flashes = [];
}