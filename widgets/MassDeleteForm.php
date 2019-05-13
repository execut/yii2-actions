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
        $this->registerWidget();
    }

    public function renderForm() {
        $form = ActiveForm::begin([
            'id' => $this->id,
        ]);
        if (!$this->model->isDeletingInProgress()) {
            $buttons = [
                'attributes' => [
                    'cancel' => [
                        'type' => Form::INPUT_RAW,
                        'value' => '<div style="text-align: right; margin-bottom: 20px">' . \yii\helpers\Html::submitButton('Удалить', ['class' => 'btn btn-danger']) . '&nbsp;&nbsp;&nbsp;' . \yii\helpers\Html::a('Отмена', ['#'], ['class' => 'btn btn-default', 'onclick' => 'javascript:history.back();return false',]) . '</div>',
                    ],
                ]
            ];
        } else {
            $buttons = [
                'attributes' => [
                    'cancel' => [
                        'type' => Form::INPUT_RAW,
                        'value' => '<div style="text-align: right; margin-bottom: 20px">' . \yii\helpers\Html::input('submit', 'stop', 'Стоп', ['class' => 'btn btn-danger']) . '&nbsp;&nbsp;&nbsp;' . \yii\helpers\Html::a('Назад', ['#'], ['class' => 'btn btn-default', 'onclick' => 'javascript:history.back();return false',]) . '</div>',
                    ],
                ]
            ];
        }

        $rows = [
            $buttons,
        ];

        if (!$this->model->isDeletingInProgress()) {
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

            $rows[] = [
                'attributes' => [
                    'count' => [
                        'type' => Form::INPUT_STATIC,
                    ],
                ]
            ];

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

            $rows[] = [
                'attributes' => [
                    'isEmulation' => [
                        'type' => Form::INPUT_CHECKBOX,
                        'attribute' => 'isEmulation',
                    ]
                ]
            ];
        } else {
            $rows[] = [
                'attributes' => [
                    [
                        'type' => Form::INPUT_RAW,
                        'value' => '<b>Прогресс удаления:</b> <div class="progress">
      <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: ' . $this->model->getDeletedProgress() . '%">
        <span class="sr-only">0% Complete</span>
      </div>
    </div>',
                    ],
                ],
            ];
        }
        $rows[] = [
            'attributes' => [
                [
                    'type' => Form::INPUT_RAW,
                    'value' => self::renderErrors($this->model->getDeleteErrors()),
                ]
            ],
        ];

        $rows[] = $buttons;

        echo \kartik\builder\FormGrid::widget([
            'model' => $this->model,
            'form' => $form,
            'autoGenerateColumns' => true,
            'rows' => $rows
        ]);

        $form->end();
    }

    /**
     * @param $errors
     * @return string
     * @throws \Exception
     */
    public static function renderErrors($errors): string
    {
        if (empty($errors)) {
            $errors = [];
        }

        $dataProvider = new ArrayDataProvider([
            'allModels' => $errors,
        ]);

        $result = '<div class="delete-errors"><b>Ошибки во время последнего удаления:</b>';
        $result .= \kartik\grid\GridView::widget([
            'columns' => [
                [
                    'label' => 'Запись',
                    'attribute' => 'model',
                ],
                [
                    'label' => 'Ошибка',
                    'attribute' => 'error',
                ],
            ],
            'dataProvider' => $dataProvider,
        ]);

        return $result . '</div>';
    }
}