<?php
/**
 * User: execut
 * Date: 21.07.16
 * Time: 15:23
 */

namespace execut\action;


use yii\base\Object;

class Params extends Object
{
    public $post;
    public $get;
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

    public static function createFromAction($action) {
        $controller = $action->controller;
        return new self([
            'post' => $controller->getPost(),
            'get' => $controller->getGet(),
            'files' => $controller->getFiles(),
            'isPjax' => $controller->isPjax(),
            'isAjax' => $controller->isAjax(),
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