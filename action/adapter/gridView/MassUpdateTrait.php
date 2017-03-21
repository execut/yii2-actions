<?php
/**
 * Created by PhpStorm.
 * User: execut
 * Date: 3/10/17
 * Time: 3:22 PM
 */

namespace execut\actions\action\adapter\gridView;


trait MassUpdateTrait
{
    public static function massUpdateAll($attributes = [], $condition = null, $params = []) {
        if (empty($condition['id'])) {
            return false;
        }

        $transaction = \yii::$app->db->beginTransaction();
        $idsPacks = array_chunk($condition['id'], 10000);
        $result = 0;
        foreach ($idsPacks as $idsPack) {
            $result += parent::updateAll($attributes, ['id' => $idsPack], $params);
        }

        $transaction->commit();

        return $result;
    }
}