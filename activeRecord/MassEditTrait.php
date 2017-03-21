<?php
/**
 * Created by PhpStorm.
 * User: execut
 * Date: 12/14/16
 * Time: 1:59 PM
 */

namespace execut\actions\activeRecord;


use execut\yii\db\query\ActiveQuery;
use kartik\detail\DetailView;
use kartik\select2\Select2;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

trait MassEditTrait
{
    public $id = null;
    public static function find()
    {
        return new self;
    }

    public function andWhere($where) {
//        $ids = \yii::$app->request->post('ids');
        $ids = $where['id'];
        $result = new self;
        /**
         * @var ActiveQuery $q
         */
        $q = $result->getModelsList();
        $q->primaryModel = null;
        $q = $q->byId($ids);

//        $q->via = null;
        $models = $q->all();
        $attributes = false;
        foreach ($models as $model) {
            $currentModelAttributes = $model->getAttributes($model->safeAttributes());
            if ($attributes === false) {
                $attributes = $currentModelAttributes;
            } else {
                $attributes = self::getEqualAttributes($attributes, $currentModelAttributes);
            }
        }

        foreach ($attributes as $key => $value) {
            $result->$key = $value;
        }

        $result->oldAttributes = $attributes;
        $result->id = $ids;

        return $result;
    }

    public function one() {
        return $this;
    }

    protected static function getEqualAttributes($prevAttributes, $currentAttributes) {
        foreach ($prevAttributes as $prevAttribute => &$prevValue) {
            if (!isset($currentAttributes[$prevAttribute])) {
                unset($prevAttributes[$prevAttribute]);
                continue;
            }

            $currentValue = $currentAttributes[$prevAttribute];
            if (!is_array($currentValue) && $currentValue !== $prevValue) {
                unset($prevAttributes[$prevAttribute]);
                continue;
            }

            if (is_array($currentValue)) {
                foreach ($prevValue as $prevKey => $prevVal) {
                    $prevVal = self::extractAttributes($prevVal);

                    $isHas = false;
                    foreach ($currentValue as $curKey => $curValue) {
                        $curValue = self::extractAttributes($curValue);
                        if (count(array_diff($prevVal, $curValue)) === 0) {
                            $isHas = true;
                            break;
                        }
                    }

                    if (!$isHas) {
                        unset($prevValue[$prevKey]);
                    }
                }
            }
        }

        return $prevAttributes;
    }

    protected static function extractAttributes($attributes) {
        if ($attributes instanceof ActiveRecord) {
            $attributes = $attributes->attributes;
        }

        $unsettedAttributes = [
            'id',
            'created',
            'updated',
            'ext_uuid',
        ];
        $unsettedAttributes[] = self::getModelRelationId();
        foreach ($attributes as $attribute => $value) {
            if (in_array($attribute, $unsettedAttributes)) {
                unset($attributes[$attribute]);
                continue;
            }

            if (is_int($value)) {
                $attributes[$attribute] = (string) $value;
            } else if (empty($value)) {
                $attributes[$attribute] = null;
            }
        }

        return $attributes;
    }

    public function getRelationUniqueId($relationKey, $data) {
        $attributes = $this->getRelationKeys($relationKey);
        $keyParts = [];
        foreach ($attributes as $attribute) {
            if ($data instanceof ActiveRecord) {
                $keyParts[] = $data->$attribute;
            } else {
                $keyParts[] = $data[$attribute];
            }
        }

        return implode('-', $keyParts);
    }

    protected function convertToUniqueKeys($relationKey, $list) {
        $result = [];
        foreach ($list as $model) {
            $result[$this->getRelationUniqueId($relationKey, $model)] = $model;
        }

        return $result;
    }

    public function validate($attributeNames = null, $clearErrors = true)
    {
        $updatedAttributes = $this->getUpdatedAttributes();
        if (!empty($updatedAttributes)) {
            $relations = $this->getRelationsKeys();
            foreach ($this->modelsList as $model) {
                foreach ($updatedAttributes as $attribute => $newValue) {
                    if (is_array($newValue)) {
                        $newValue = $this->convertToUniqueKeys($attribute, $newValue);
                        $currentValue = $this->convertToUniqueKeys($attribute, $model->$attribute);
                        $oldValue = [];
                        if (isset($this->oldAttributes[$attribute])) {
                            $oldValue = $this->convertToUniqueKeys($attribute, $this->oldAttributes[$attribute]);
                        }

                        foreach ($oldValue as $oldKey => $oldVal) {
                            if (!isset($newValue[$oldKey])) {
                                unset($currentValue[$oldKey]);
                            }
                        }

                        foreach ($newValue as $newKey => $newVal) {
                            $currentValue[$newKey] = $newVal;
                        }

                        $newValue = $currentValue;

                        if (isset($relations[$attribute])) {
                            $relationId = $this->getModelRelationId();
                            foreach ($newValue as &$value) {
                                if (is_array($value)) {
                                    $value[$relationId] = $model->id;
                                } else {
                                    $value->$relationId = $model->id;
                                }
                            }
                        }
                    }

                    $model->$attribute = $newValue;
                }

                $attributesNames = array_keys($updatedAttributes);
                if (!$model->validate()) {
                    $this->addErrors($model->errors);

                    return false;
                }
            }
        }

        return parent::validate($attributeNames, $clearErrors); // TODO: Change the autogenerated stub
    }

    public function save($runValidation = true, $attributeNames = null)
    {
        $updatedAttributes = $this->getUpdatedAttributes();
        if (!empty($updatedAttributes)) {
            foreach ($this->modelsList as $model) {
//                $attributesNames = array_keys($updatedAttributes);
                if (!$model->save()) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @return array
     */
    protected function getUpdatedAttributes(): array
    {
        $oldAttributes = $this->oldAttributes;
        $updatedAttributes = [];
        $attributes = $this->getAttributes($this->safeAttributes());
        foreach ($attributes as $attribute => $value) {
            if (!isset($oldAttributes[$attribute]) || $oldAttributes[$attribute] === null) {
                $oldAttributes[$attribute] = '';
            }
        }

        foreach ($attributes as $key => $value) {
            if ($value === null) {
                $value = '';
            }

            if ((!isset($oldAttributes[$key]) && $value !== null)) {
                $updatedAttributes[$key] = $value;
            } else if (isset($oldAttributes[$key]) && !$this->compareValues($oldAttributes[$key], $value)) {
                $updatedAttributes[$key] = $value;
            }
        }

        unset($updatedAttributes['id']);

        return $updatedAttributes;
    }

    protected function compareValues($valueA, $valueB) {
        return $valueA === $valueB;
    }

    public function getFormFields() {
        $data = [];
        $modelsNames = [];
        foreach ($this->getModelsList()->all() as $row) {
            $data[$row->id] = $row->name;
            $modelsNames[] = $row->name;
        }

        $fields = parent::getFormFields();
        return ArrayHelper::merge($fields, [
            'id' => [
                'displayOnly' => false,
                'type' => DetailView::INPUT_WIDGET,
                'attribute' => 'id',
                'value' => function () use ($modelsNames) {
                    return implode(', ', $modelsNames);
                },
                'widgetOptions' => [
                    'class' => Select2::className(),
                    //            'format' => 'raw',
                    'data' => $data,
                    'pluginOptions' => [
                        'multiple' => true,
                    ],
                ],
            ],
        ]);
    }

    public function toString() {
        return implode(', ', $this->id);
    }
}