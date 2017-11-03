<?php
/**
 * User: execut
 * Date: 21.07.16
 * Time: 15:23
 */

namespace execut\actions\action;


use yii\base\Object;

class Params extends Object
{
    public $post = [];
    public $get = [];
    public $files;
    public $isAjax;
    public $isPjax;
    public $module;
    public $controller;
    public $action;
    public function getUniqueId($keys = [
        'module',
        'controller',
        'action'
    ]) {
        $parts = [];
        foreach ($keys as $key) {
            $parts[$key] = $this->$key;
        }

        return '/' . implode('/', $parts);
    }

    public static function createFromAction($action, $helper = null) {
        $controller = $action->controller;
        if ($helper === null) {
            $helper = new RequestHelper();
        }

        return new self([
            'post' => $helper->getPost(),
            'get' => $helper->getGet(),
            'files' => $helper->getFiles(),
            'isPjax' => $helper->isPjax(),
            'isAjax' => $helper->isAjax(),
            'action' => $action->id,
            'controller' => $controller->id,
            'module' => $controller->module->uniqueId,
        ]);
    }

    public function getData() {
        if ($this->post) {
            return $this->post;
        } else {
            return $this->get;
        }
    }

    public function getUniqueIdWithoutAction() {
        return $this->getUniqueId([
            'module',
            'controller'
        ]);
    }
}