<?php
/**
 * Created by PhpStorm.
 * User: execut
 * Date: 11/29/16
 * Time: 3:27 PM
 */

namespace execut\actions\action\adapter;


use execut\actions\action\Adapter;

class Simple extends Adapter
{
    public function _run()
    {
        $response = $this->getResponse();
        $response->content = [];

        return $response;
    }
}