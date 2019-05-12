<?php
/**
 * Created by PhpStorm.
 * User: execut
 * Date: 3/11/19
 * Time: 4:53 PM
 */

namespace execut\actions\action\adapter;


use execut\actions\action\adapter\helper\FormLoader;
use execut\actions\models\MassDelete;
use execut\actions\widgets\MassDeleteForm;
use execut\crudFields\fields\Field;
use yii\db\ActiveRecord;

class MassHandler extends Form
{
    public $scenario = Field::SCENARIO_GRID;
    public function getDefaultViewRendererConfig()
    {
        return [
            'class' => \execut\actions\action\adapter\viewRenderer\MassHandler::className(),
//            'title' => $this->model->getModelLabelOld(2),
        ];
    }

    protected function _run() {
        \yii::beginProfile('Run action', 'execut.yii2-actions');
        /**
         * @var ActiveRecord $filter
         */
        $filter = $this->model;
        /**
         * @TODO Delete
         */
        \yii::$app->navigation->addPage([
            'name' => 'Массовое удаление',
        ]);
//        if ($filter->getBehavior('relationsSaver')) {
//            $filter->detachBehavior('relationsSaver');
//        }

        if ($this->scenario !== ActiveRecord::SCENARIO_DEFAULT) {
            $filter->scenario = $this->scenario;
        }

        $model = new MassDelete([
            'owner' => $filter
        ]);

        $data = $this->getData();

        if (\yii::$app->request->post('stop')) {
            $model->stop();
        }

        if (!empty($data['getProgress']) && \yii::$app->request->isAjax) {
            return $this->getResponse([
                'content' => [
                    'progress' => $model->getDeletedProgress(),
                    'errors' => MassDeleteForm::renderErrors($model->getDeleteErrors()),
                ],
                'format' => \yii\web\Response::FORMAT_JSON
            ]);
        }

        $result = parent::_run();

        $isValid = $filter->validate();
        /**
         * @var ArrayDataProvider $dataProvider
         */
        $dataProvider = $filter->search();
        if (!$isValid) {
            $dataProvider->query->where = 'false';
        }

        if (!$model->isDeletingInProgress()) {
            if ($model->getCount()) {
//                $redirect = $this->redirectToMainPage();
//
//                $result->content = $redirect;
//                return $result;

                $loader = new FormLoader();
                $loader->model = $model;
                $loader->data = \yii::$app->request->post();
                if ($loader->run()) {
                    $model->delete();
//                if (empty($model->deleteErrors)) {
//                    $result->flashes = [
//                        'kv-detail-success' => 'Успешно удалено ' . $deletedCount . ' записей',
//                    ];
//                    $redirect = $this->redirectToMainPage();
//                    $result->content = $redirect;
//
//                    return $result;
//                }
                }
            }
        }

        $result->content['model'] = $model;

        return $result;
    }

    /**
     * @return \yii\web\Response
     */
    protected function redirectToMainPage(): \yii\web\Response
    {
        $urlParams = \yii::$app->request->getQueryParams();
        $urlParams[0] = str_replace('/mass-delete', '', $this->getUniqueId());
        $model = $this->getModel();
        if (!empty($urlParams[$model->formName()])) {
            unset($urlParams[$model->formName()][current($model->primaryKey())]);
        }

        $redirect = \yii::$app->controller->redirect($urlParams);
        return $redirect;
    }
}