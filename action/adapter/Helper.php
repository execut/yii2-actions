<?php
namespace execut\actions\action\adapter;
/**
 * User: execut
 * Date: 15.07.16
 * Time: 10:42
 */
abstract class Helper {
    public $adapter = null;
    abstract public function run();
}