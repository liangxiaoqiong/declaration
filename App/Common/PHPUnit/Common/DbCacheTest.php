<?php

namespace Common\PHPUnit\Model;

use Common\PHPUnit\BaseTest;

class DbCacheTest extends BaseTest
{
    public function testAfterUpdate()
    {
        $model = D('Bill');

        $model->startTrans();
        $data = [];
        $data['garden_id'] = self::$gardenId;
        $data['bill_sn'] = '123';
        $billId = $model->add($data);
        $oldBill = $model->getBill(self::$gardenId, $billId);

        $data = [];
        $data['bill_id'] = $billId;
        $data['bill_sn'] = '1234';
        $model->save($data);
        $newBill = $model->getBill(self::$gardenId, $billId);
        $model->rollback();
    }
}