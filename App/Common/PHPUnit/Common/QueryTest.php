<?php

namespace Common\PHPUnit\Model;

use Common\PHPUnit\BaseTest;

class QueryTest extends BaseTest
{
    public function testQueryCache()
    {
        $model = M('facility');
        $options = [];
        $options['field'] = 'facility_id';
        $list = $model->queryList($options);

        $options = [];
        $options['field'] = 'facility_id,facility_name';
        $list = $model->queryList($options);
    }
}