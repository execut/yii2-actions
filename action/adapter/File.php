<?php
/**
 * User: execut
 * Date: 07.07.15
 * Time: 11:26
 */

namespace execut\action\adapter;


use execut\action\Adapter;
use yii\base\Model;
use yii\web\Response;

/**
 * Class GridView
 * @package execut\action
 * @property Model $filter
 */
class File extends Adapter
{
    public $modelClass = null;
    public $model = null;
    protected function _run() {
        $attributes = $this->actionParams->get;
        $class = $this->modelClass;
        unset($attributes['r']);
        $result = $class::find()->byAttributes($attributes)->one();
        $this->model = $result;

        $response = \Yii::$app->getResponse();
        $response->setDownloadHeaders($result->name, $result->mime_type);

        $response = $this->getResponse([
            'format' => Response::FORMAT_RAW,
            'content' => stream_get_contents($result->content),
        ]);

        return $response;
    }
}