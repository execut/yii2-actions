<?php
/**
 * User: execut
 * Date: 21.07.16
 * Time: 13:47
 */

namespace execut\actions\action\adapter\viewRenderer;


use yii\helpers\Url;

class DetailView extends Widget
{
    public $uniqueId = null;
    public $model = null;
    public $mode = null;

    public function getDefaultWidgetOptions()
    {
        return [
            'class' => \kartik\detail\DetailView::className(),
            'panel'=>[
                'heading'=>'Редактирование',
                'type'=>\kartik\detail\DetailView::TYPE_PRIMARY,
            ],
            'buttons1' => '{update}',
            'deleteOptions' => [
                'url' => Url::to([
                    $this->uniqueId . '/delete',
                    'id' => '{id}',
                ]),
            ],
//                        'mainTemplate' => '{buttons}{detail}{buttons}',
            'model' => $this->model,
            'mode' => $this->mode,
            'bordered' => true,
            'striped' => true,
            'condensed' => true,
            'responsive' => true,
            'hideIfEmpty' => true,
            'hover' => true,
//                        'hAlign'=> true,
//                        'vAlign'=> true,
//                        'fadeDelay'=> 2000,
            'deleteOptions'=>[ // your ajax delete parameters
                'params' => ['id' => 1000, 'kvdelete'=>true],
            ],
//                        'container' => ['id'=>'kv-demo'],
            'formOptions' => [
                'options' => [
                    'enctype'=>'multipart/form-data',
                ],
            ],
            'attributes' => $this->model->getFormFields(),
        ];
    }
}