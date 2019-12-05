<?php

namespace Common\PHPUnit\Model;

use Common\PHPUnit\BaseTest;
use Common\Util\ArraySet;

class ArraySetTest extends BaseTest
{
    public function arrayList()
    {
        return [
            [
                'a',
                [
                    [
                        'table' => 'a',
                        'key' => '1',
                    ],
                    [
                        'table' => 'a',
                        'key' => '2',
                    ],
                    [
                        'table' => 'b',
                        'key' => '1',
                    ],
                    [
                        'table' => 'b',
                        'key' => '2',
                    ],
                    [
                        'table' => 'b',
                        'key' => '3',
                    ],
                    [
                        'table' => 'c',
                        'key' => '3',
                    ],
                    [
                        'table' => 'c',
                        'key' => '3',
                    ],
                ]
            ]
        ];
    }

    /**
     * @dataProvider arrayList
     * @param $mark
     * @param $data
     * User: hjun
     * Date: 2018-06-01 13:16:28
     * Update: 2018-06-01 13:16:28
     * Version: 1.00
     */
    public function testSelect($mark, $data)
    {
        $arrSet = new ArraySet($data);
        $where = [];
        $where['table'] = 'a';
        $result = $arrSet->select($where);
        $list = [
            [
                'table' => 'a',
                'key' => '1',
            ],
            [
                'table' => 'a',
                'key' => '2',
            ]
        ];
        $this->assertEquals($list, $result);
    }

    /**
     * @dataProvider arrayList
     * @param $mark
     * @param $data
     * User: hjun
     * Date: 2018-06-01 13:20:50
     * Update: 2018-06-01 13:20:50
     * Version: 1.00
     */
    public function testSelectOR($mark, $data)
    {
        $arrSet = new ArraySet($data);
        $where = [];
        $where['table'] = 'a';
        $where['key'] = '3';
        $where['_logic'] = 'or';
        $result = $arrSet->select($where);
        $list = [
            [
                'table' => 'a',
                'key' => '1',
            ],
            [
                'table' => 'a',
                'key' => '2',
            ],
            [
                'table' => 'b',
                'key' => '3',
            ],
            [
                'table' => 'c',
                'key' => '3',
            ],
            [
                'table' => 'c',
                'key' => '3',
            ],
        ];
        $this->assertEquals($list, $result);
    }

    /**
     * @dataProvider arrayList
     * @param $mark
     * @param $data
     * User: hjun
     * Date: 2018-06-01 13:20:59
     * Update: 2018-06-01 13:20:59
     * Version: 1.00
     */
    public function testSelectAND($mark, $data)
    {
        $arrSet = new ArraySet($data);
        $where = [];
        $where['table'] = 'c';
        $where['key'] = '3';
        $where['_logic'] = 'AND';
        $result = $arrSet->select($where);
        $list = [
            [
                'table' => 'c',
                'key' => '3',
            ],
            [
                'table' => 'c',
                'key' => '3',
            ]
        ];
        $this->assertEquals($list, $result);
    }

    /**
     * @dataProvider arrayList
     * @param $mark
     * @param $data
     * User: hjun
     * Date: 2018-06-01 13:20:59
     * Update: 2018-06-01 13:20:59
     * Version: 1.00
     */
    public function testFindAND($mark, $data)
    {
        $arrSet = new ArraySet($data);
        $where = [];
        $where['table'] = 'c';
        $where['key'] = '3';
        $where['_logic'] = 'AND';
        $result = $arrSet->find($where);
        $list = [
            'table' => 'c',
            'key' => 3,
        ];
        $this->assertEquals($list, $result);
    }

    /**
     * @dataProvider arrayList
     * @param $mark
     * @param $data
     * User: hjun
     * Date: 2018-06-01 15:39:47
     * Update: 2018-06-01 15:39:47
     * Version: 1.00
     */
    public function testGetField($mark, $data)
    {
        $arrSet = new ArraySet($data);
        $where = [];
        $where['table'] = 'c';
        $caches = $arrSet->getField($where, 'key', true);
        $expected = ['3', '3'];
        $this->assertEquals($expected, $caches);
    }

    /**
     * @dataProvider arrayList
     * @param $mark
     * @param $data
     * User: hjun
     * Date: 2018-06-01 16:01:57
     * Update: 2018-06-01 16:01:57
     * Version: 1.00
     */
    public function testDelete($mark, $data)
    {
        $arrSet = new ArraySet($data);
        $where = [];
        $where['table'] = 'a';
        $arrSet->delete($where);
        $result = $arrSet->toArray();
        $expected = [
/*            [
                'table' => 'a',
                'key' => '1',
            ],
            [
                'table' => 'a',
                'key' => '2',
            ],*/
            [
                'table' => 'b',
                'key' => '1',
            ],
            [
                'table' => 'b',
                'key' => '2',
            ],
            [
                'table' => 'b',
                'key' => '3',
            ],
            [
                'table' => 'c',
                'key' => '3',
            ],
            [
                'table' => 'c',
                'key' => '3',
            ],
        ];
        $this->assertEquals($expected, $result);
    }
}