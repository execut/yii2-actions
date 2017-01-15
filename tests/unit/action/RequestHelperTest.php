<?php
/**
 */

namespace execut\actions\action;


use execut\actions\TestCase;

class RequestHelperTest extends TestCase
{
    public function setUp()
    {
        parent::setUp(); // TODO: Change the autogenerated stub
    }

    public function testGetPost() {
        $helper = new RequestHelper();
        $_POST = [
            '_method' => 'post',
            'postKey' => 'postValue',
        ];

        $this->assertEquals([
            'postKey' => 'postValue',
        ], $helper->getPost());
    }

    public function testGetGet() {
        $helper = new RequestHelper();
        $get = [
            'getKey' => 'getValue',
        ];
        $_GET = $get;

        $this->assertEquals($get, $helper->getGet());
    }

    public function testGetFiles() {
        $helper = new RequestHelper();
        $files = [
            'fileKey' => 'fileValue',
        ];
        $_FILES = $files;

        $this->assertEquals($files, $helper->getFiles());
    }

    public function testGetIsAjax() {
        $helper = new RequestHelper();
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';

        $this->assertTrue($helper->isAjax());
    }

    public function testGetIsPjax() {
        $helper = new RequestHelper();
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $_SERVER['HTTP_X_PJAX'] = 'asdasd';

        $this->assertTrue($helper->isPjax());
    }
}