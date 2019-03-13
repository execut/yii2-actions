<?php
/**
 * Created by PhpStorm.
 * User: execut
 * Date: 3/13/19
 * Time: 11:07 AM
 */

namespace execut\actions\widgets;


use execut\actions\models\MassDelete;
use execut\yii\jui\Widget;
use kartik\builder\Form;
use kartik\form\ActiveForm;
use unclead\multipleinput\MultipleInputColumn;
use yii\data\ArrayDataProvider;

class MassDeleteForm extends Widget
{
    /**
     * @var MassDelete
     */
    public $model = null;
    public $deletedCount = null;
    public function run()
    {
        $this->renderForm();
    }

    public function renderForm() {
        $form = ActiveForm::begin();
        $buttons = [
            'attributes' => [
                'cancel' => [
                    'type' => Form::INPUT_RAW,
                    'value' => '<div style="text-align: right; margin-bottom: 20px">' . \kartik\helpers\Html::submitButton('Удалить', ['class' => 'btn btn-danger']) . '&nbsp;&nbsp;&nbsp;' . \kartik\helpers\Html::a('Отмена', ['#'], ['class' => 'btn btn-default', 'onclick' => 'javascript:history.back();return false',]) . '</div>',
                ],
            ]
        ];
//        $data = $this->model->getAllSelectedData();
        $data = [];

        $articlesPluginOptions = [
            'allowClear' => true,
            'ajax' => [
                'data' => new \yii\web\JsExpression(<<<JS
            function(params) {
                return {
                    "Articles[name]": params.term,
                    page: params.page
                };
            }
JS
                )
            ],
        ];
        $rows = [
            $buttons,
            [
                'attributes' => [
                    'count' => [
                        'type' => Form::INPUT_STATIC,
                    ],
                ]
            ],
        ];

        if ($this->deletedCount) {
            $rows[] = [
                'attributes' => [
                    [
                        'type' => Form::INPUT_RAW,
                        'value' => '<div class="alert alert-success">Успешно удалено записей: ' . $this->deletedCount . '</div>',
                    ],
                ],
            ];
        }

        if (!empty($this->model->deleteRelationsModels)) {
            $rows[] = [
                'attributes' => [
                    'deleteRelationsModels' => [
                        'type' => Form::INPUT_WIDGET,
                        'widgetClass' => \unclead\multipleinput\MultipleInput::class,
                        'options' => [
                            'min' => 0,
                            'addButtonOptions' => [
                                'class' => 'hidden',
                            ],
                            'removeButtonOptions' => [
                                'class' => 'hidden',
                            ],
                            'allowEmptyList' => true,
                            'columns' => [
                                'name' => [
                                    'type' => \unclead\multipleinput\MultipleInputColumn::TYPE_STATIC,
                                    'name' => 'label',
                                ],
                                'is_delete' => [
                                    'type' => MultipleInputColumn::TYPE_CHECKBOX,
                                    'name' => 'is_delete',
                                    'enableError' => true,
                                ],
                            ]
                        ],
                    ]
                ]
            ];

            //foreach ($model->relations as $key => $relation) {
            //    $rows[] = [
            //        'attributes' => [
            //            'relations.' . $key . '.name' => [
            //                'type' => Form::INPUT_STATIC,
            //                'fieldConfig' => [
            //                    'template' => '{input}{hint}{error}',
            //                ],
            //            ],
            //            'relations.' . $key . '.count' => [
            //                'type' => Form::INPUT_STATIC,
            //                'fieldConfig' => [
            //                    'template' => '{input}{hint}{error}',
            //                ],
            //            ],
            //            'relations[' . $key . '][action_id]' => [
            //                'type' => Form::INPUT_WIDGET,
            //                'widgetClass' => \kartik\select2\Select2::class,
            //                'options' => [
            //                    'options' => [
            //                        'placeholder' => 'Выберите действие...',
            //                    ],
            //                    'pluginOptions' => [
            //                        'allowClear' => true,
            //                    ],
            //                    'data' => \yii\helpers\ArrayHelper::merge(['' => ''], $model->relations[$key]->getActionsList()),
            //                ],
            //                'fieldConfig' => [
            //                    'template' => '{input}{hint}{error}',
            //                ],
            //            ],
            //            'relations.' . $key . '.target_id' => [
            //                'type' => Form::INPUT_WIDGET,
            //                'widgetClass' => \kartik\select2\Select2::class,
            //                'options' => [
            //                    'options' => [
            //                        'placeholder' => 'Цель перепривязки...',
            //                    ],
            //                    'pluginOptions' => [
            //                        'allowClear' => true,
            //                        'ajax' => [
            //                            'url' => '/goods/articles',
            //                        ],
            //                    ],
            //                ],
            //                'fieldConfig' => [
            //                    'template' => '{input}{hint}{error}',
            //                ],
            //            ],
            //        ]
            //    ];
            //}
        }

        if (!empty($this->model->deleteErrors)) {
            $errors = $this->model->deleteErrors;
            $allModels = $errors;
//            foreach ($errors as $key => $error) {
//                $allModels = array_merge($allModels, $error);
//            }

            $dataProvider = new ArrayDataProvider([
                'allModels' => $allModels,
            ]);

            $rows[] = [
                'attributes' => [
                    'deleteRelationsModels' => [
                        'type' => Form::INPUT_RAW,
                        'value' => \kartik\grid\GridView::widget([
                            'columns' => [
                                [
                                    'label' => 'Запись',
                                    'attribute' => 'model.name',
                                ],
                                [
                                    'label' => 'Ошибка',
                                    'attribute' => 'error',
                                ],
                            ],
                            'dataProvider' => $dataProvider,
                        ]),
                    ]
                ],
            ];
        }

        $rows[] = $buttons;

        echo \kartik\builder\FormGrid::widget([
            'model' => $this->model,
            'form' => $form,
            'autoGenerateColumns' => true,
            'rows' => $rows
        ]);

        $form->end();
    }
}