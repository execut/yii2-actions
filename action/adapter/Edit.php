<?php
/**
 * User: execut
 * Date: 14.07.16
 * Time: 16:00
 */

namespace execut\action\adapter;


use execut\action\Adapter;
use execut\action\adapter\viewRenderer\DetailView;
use execut\action\Response;
use yii\helpers\Url;
use yii\web\UploadedFile;

class Edit extends Form
{
    public $modelClass = null;
    public $relations = [];
    public $additionalAttributes = [];
    public $requestType = self::POST;
    protected function _run() {
        $class = $this->modelClass;
        if ($this->actionParams && !empty($this->actionParams->get['id'])) {
            $id = $this->actionParams->get['id'];
            $mode = 'view';
//            $modelInstance = new $class;
            if (!empty($this->filesAttributes)) {
//                $selectAttributes = $modelInstance->attributes();
//                foreach ($this->filesAttributes as $fileAttribute => $uploadAttribute) {
//                    if (false !== ($fileAttributeKey = array_search($fileAttribute, $selectAttributes))) {
//                        unset($selectAttributes[$fileAttributeKey]);
//                    }
//
//                    if (false !== ($fileAttributeKey = array_search($uploadAttribute, $selectAttributes))) {
//                        unset($selectAttributes[$fileAttributeKey]);
//                    }
//                }
//
//                $selectAttributes = array_values($selectAttributes);
                $selectAttributes = '*';
            } else {
                $selectAttributes = '*';
            }

            $model = $class::find()->select($selectAttributes)->andWhere([
                'id' => $id,
            ])->one();
        } else {
            $model = new $class;
            $mode = 'edit';
        }

//        foreach ($this->relations as $relation) {
//            var_dump($model->getRelation($relation));
//            exit;
//        }
        $this->model = $model;
        foreach ($this->additionalAttributes as $attribute) {
            if ((isset($this->actionParams->get[$attribute])) && ($value = $this->actionParams->get[$attribute])) {
                $model->$attribute = $value;
            }
        }

        $result = parent::loadAndValidateForm();

        if (is_array($result)) {
            return $this->getResponse([
                'content' => $result
            ]);
        }

        $flashes = [];
        if ($result === true) {
            if ($model->isNewRecord) {
                $operation = 'created';
            } else {
                $operation = 'updated';
            }

            $flashes['kv-detail-success'] = 'Record ' . $model . ' ' .  $operation;
            $model->save();
            $params = [
                $this->actionParams->uniqueId,
                'id' => $model->id
            ];

            foreach ($this->additionalAttributes as $attribute) {
                $params[$attribute] = $model->$attribute;
            }

            $result = \yii::$app->response->redirect($params);
        } else {
            $result = [
                'mode' => $mode,
                'model' => $model
            ];
        }

        return $this->getResponse([
            'flashes' => $flashes,
            'content' => $result,
        ]);
    }

    public function getDefaultViewRendererConfig() {
        return [
            'class' => DetailView::className(),
            'uniqueId' => $this->uniqueId,
        ];
    }
}