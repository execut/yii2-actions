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
use yii\db\ActiveRecord;
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

    public $scenario = ActiveRecord::SCENARIO_DEFAULT;

    protected $handlers = [];

    public function setHandlers($handlers) {
        foreach ($handlers as $key => $handler) {
            if (is_array($handler)) {
                $handlers[$key] = \yii::createObject($handler);
            }
        }

        $this->handlers = $handlers;
    }

    public function getHandlers() {
        return $this->handlers;
    }

    protected function _run() {
        $filter = $this->model;
        if ($this->scenario !== ActiveRecord::SCENARIO_DEFAULT) {
            $filter->scenario = $this->scenario;
        }

        parent::_run();

        /**
         * @var ArrayDataProvider $dataProvider
         */
        $dataProvider = $filter->search();
        if (!empty($this->actionParams->get['handle'])) {
            $handlerKey = $this->actionParams->get['handle'];
            $handlers = $this->handlers;
            if (!empty($handlers[$handlerKey])) {
                $handler = $handlers[$handlerKey];
                $handler->dataProvider = $dataProvider;
                if (($result = $handler->run()) !== null) {
                    return $result;
                }
            }
        }

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

            if (isset($this->actionParams->get['depdrop_parents'])) {
                $key = 'output';
            } else {
                $key = 'results';
            }

            $pagination = $dataProvider->pagination;
            $response->content = [
                $key => $result,
                'pagination' => [
                    'more' => $pagination->page < $pagination->pageCount - 1,
                ],
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
//            'title' => $this->model->getModelLabelOld(2),
            'modelClass' => $this->model->className(),
        ];
    }
}