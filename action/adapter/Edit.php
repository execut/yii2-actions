<?php
/**
 * User: execut
 * Date: 14.07.16
 * Time: 16:00
 */

namespace execut\actions\action\adapter;


use execut\actions\action\Adapter;
use execut\actions\action\adapter\viewRenderer\DetailView;
use execut\actions\action\Response;
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
        $actionParams = $this->actionParams;
        if ($this->actionParams && (!empty($actionParams->get['id']) || !empty($actionParams->post['id']))) {
            if (!empty($actionParams->get['id'])) {
                $id = $actionParams->get['id'];
            } else {
                $id = $actionParams->post['id'];
            }

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

            $model = $class::findByPk($id);
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

            if (\yii::$app->has('db') && $t = \yii::$app->db->transaction) {
                while ($t->getIsActive()) {
                    $t->commit();
                }
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