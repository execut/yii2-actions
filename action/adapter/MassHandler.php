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
use execut\crudFields\fields\Field;

class MassHandler extends GridView
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
        $result = parent::_run();
        $model = new MassDelete([
            'owner' => $this->model
        ]);
        if (!$model->getCount()) {
            $redirect = $this->redirectToMainPage();

            $result->content = $redirect;
            return $result;
        }

        $loader = new FormLoader();
        $loader->model = $model;
        $loader->data = \yii::$app->request->post();
        $deletedCount = null;
        if ($loader->run()) {
            $deletedCount = $model->delete();
            if (empty($model->deleteErrors)) {
                $result->flashes = [
                    'kv-detail-success' => 'Успешно удалено ' . $deletedCount . ' записей',
                ];
                $redirect = $this->redirectToMainPage();
                $result->content = $redirect;

                return $result;
            }
        }
        $result->content['deletedCount'] = $deletedCount;
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

        $redirect = \yii::$app->controller->redirect($urlParams);
        return $redirect;
    }
}