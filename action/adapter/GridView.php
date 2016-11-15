<?php
/**
 * User: execut
 * Date: 07.07.15
 * Time: 11:26
 */

namespace execut\actions\action\adapter;


use execut\actions\action\Adapter;
use execut\actions\action\adapter\viewRenderer\DynaGrid;
use execut\yii\helpers\Html;
use yii\base\Model;
use yii\data\ArrayDataProvider;
use yii\web\Response;
use yii\widgets\ActiveForm;

/**
 * Class GridView
 * @package execut\actions\action
 * @property Model $filter
 */
class GridView extends \execut\actions\action\adapter\Form
{
    public $attributes = [
        'id',
        'text' => 'name',
    ];
    protected function _run() {
        parent::_run();
        $filter = $this->model;

        /**
         * @var ArrayDataProvider $dataProvider
         */
        $dataProvider = $filter->getDataProvider();
        $actionParams = $this->actionParams;
        $response = $this->getResponse();
        if ($actionParams->isAjax && !$actionParams->isPjax && !$this->isDisableAjax && $dataProvider) {
            $result = [];
            foreach ($dataProvider->models as $row) {
                $modelAttributes = array_values($this->attributes);
                if ($row instanceof Model) {
                    $row = $row->getAttributes($modelAttributes);
                }

                $res = [];
                foreach ($this->attributes as $targetKey => $attribute) {
                    if (is_int($targetKey)) {
                        $targetKey = $attribute;
                    }

                    $res[$targetKey] = $row[$attribute];
                }

                $result[] = $res;
            }

            $response->content = [
                'results' => $result
            ];

            $response->format = Response::FORMAT_JSON;
        } else {
            $response->content = [
                'filter' => $filter,
                'dataProvider' => $dataProvider,
            ];
        }

        return $response;
    }

    public function getDefaultViewRendererConfig()
    {
        return [
            'class' => DynaGrid::className(),
            'title' => $this->model->getModelLabel(2),
            'modelClass' => $this->model->className(),
        ];
    }
}