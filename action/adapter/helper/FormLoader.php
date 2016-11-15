<?php
/**
 * User: execut
 * Date: 15.07.16
 * Time: 10:42
 */

namespace execut\actions\action\adapter\helper;


use execut\actions\action\adapter\Helper;
use yii\db\ActiveRecord;
use yii\web\UploadedFile;

class FormLoader extends Helper
{
    public $filesAttributes = [];
    public $model;
    public $data;
    public $isValidate = true;
    public function run() {
        /**
         * @var ActiveRecord $filter
         */
        $filter = $this->model;
        if ($filter->load($this->data)) {
            if (!empty($this->filesAttributes)) {
                foreach ($this->filesAttributes as $contentAttribute => $attribute) {
                    $file = UploadedFile::getInstance($filter, $attribute);
                    if ($file) {
                        $filter->$attribute = $file;
                        $filter->$contentAttribute = file_get_contents($file->tempName);
                    }
                }
            }

            if (!$this->isValidate || $filter->validate()) {
                return true;
            } else {
                return false;
            }
        }
    }
}