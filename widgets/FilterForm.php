<?php
/**
 * Created by PhpStorm.
 * User: execut
 * Date: 6/14/19
 * Time: 11:57 AM
 */

namespace execut\actions\widgets;


use execut\yii\jui\Widget;

class FilterForm extends Widget
{
    public $model = null;
    public function run()
    {
        return $this->_renderContainer(DetailView::widget([
            'formOptions' => [
                'method' => 'get',
                'action' => '',
            ],
            'buttonsTemplate' => '{apply}',
            'applyButton' => '<input type="submit" name="save" value="Поиск" class="btn btn-primary">',
            'model' => $this->model,
            'mode' => 'edit',
            'attributes' => $this->model->getFormFields(),
        ]));
    }
}