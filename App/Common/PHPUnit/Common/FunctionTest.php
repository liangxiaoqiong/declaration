<?php

namespace Common\PHPUnit\Model;

use Common\PHPUnit\BaseTest;

class FunctionTest extends BaseTest
{
    public function provider()
    {
        return [['provider1']];
    }

    public function testProducerFirst()
    {
        $this->assertTrue(true);
        return 'first';
    }

    public function testProducerSecond()
    {
        $this->assertTrue(true);
        return 'second';
    }

    /**
     * @depends      testProducerFirst
     * @depends      testProducerSecond
     * @dataProvider provider
     */
    public function testConsumer()
    {
        $this->assertEquals(
            ['provider1', 'first', 'second'],
            func_get_args()
        );
    }

    public function testRoomGetMeta()
    {
        $model = D('Room');
        $meta = $model->getMeta(74);
        $this->assertArrayHasKey('empty_room_num', $meta);
    }

    public function testRegex()
    {
        $price = '2014-31-21';
        $model = M('');
        $rule = '/^[0-9]{0,20}$/';
        $result = $model->regex('1.2', $rule);
    }

    public function testJsonSchema()
    {
        $feeItems = [];
        $item = [];
        $item['item_id'] = '1';
        $item['item_name'] = '123';
        $item['fee_type'] = '1';
        $item['fee'] = '123';
        $item['is_meter_type'] = '1';
        $item['unit_price'] = '1';
        $item['unit'] = '1';
        $item['runit'] = '1';
        $item['time'] = '2018-02-01';
        $item['current'] = '0';
        $item['last_time'] = '2018-02-01';
        $item['last_current'] = '0';
        $feeItems[] = $item;
        $item['item_id'] = '2';
        $feeItems[] = $item;
        $schema = getDefaultData('json/admin/register_schema');
        $validate = new \JsonSchema\Validator();
        $validate->check($item, $schema);
        if (!$validate->isValid()) {
            $errors = $validate->getErrors();
            $message = "{$errors[0]['property']}:{$errors[0]['message']}";
            return getReturn(CODE_ERROR, $message);
        }
        return getReturn(CODE_SUCCESS);
    }

    public function testSaveCompany()
    {
        $request = [];
        $request['company_name'] = '励志分队';
        $request['company_form'] = '1';
        $request['area_id_1'] = '1';
        $request['area_id_2'] = '2';
        $request['area_id_3'] = '3';
        $request['is_high_new'] = '1';
        $request['capital_form'] = '4';
        $request['license_no'] = '测试2';
        $request['address'] = '测试3';
        $request['capital'] = '5';
        $request['establishment_time'] = '2018-01-28';
        $request['legal_person'] = '13345678910';
        $request['lp_mobile'] = '13345678910';
        $request['tel'] = '05921234567';
        $request['email'] = 'a@qq.com';
        $request['introduction'] = '测试4';
        $request['license_image'] = 'http://a.jpg';
        $request['apply_fields'] = '6,7,8';
        $request['scope'] = '测试7';
        $request['contact_name'] = '测试8';
        $request['contact_duty'] = '测试9';
        $request['contact_mobile'] = '13345678911';
        $request['contact_tel'] = '0592-1234569';
        $request['contact_fax'] = '123-12345678';
        $request['contact_email'] = 'b@qq.com';
        $request['contact_qq'] = '';
        $request['contact_wechat'] = '测试15';
        session('ADMIN', ['id' => 101, 'username' => 'a@qq.com']);
        $model = D('Company');
        /*$request = [
            'company_id' => '7',
            'audit_reason' => '测试',
        ];*/
        $result = $model->saveInfo($request);
        dump($result);
    }

    public function testGetDataApply()
    {
        $request = [];
        $request['data_id'] = '19';
        $request['income_output_value'] = '2';
        $request['export_value'] = '3';
        $request['investment_value'] = '4';
        $request['profit_value'] = '5';
        $request['taxes_value'] = '6';
        $request['dev_people_value'] = '7';
        $request['invention_patent_value'] = '8';
        $request['utility_patent_value'] = '9';
        $request['facade_patent_value'] = '10';
        $request['software_copyright_value'] = '11';
        $request['other_value'] = '12';
        session('ADMIN', ['id' => 101, 'username' => 'a@qq.com', 'company_id' => 7]);
        $model = D('DataApply');
        $request = [
            'data_id' => '19',
            'audit_reason' => '',
        ];
        $result = $model->refuseDataApply($request);
        dump($result);
    }

    public function testQnr()
    {
        $model = D('Qnr');
        /*$data = [
            'qnr_name' => '测试',
            'is_open' => '1',
            'is_force' => '0',
            'questions' => [
                [
                    'question_title' => '选择性别',
                    'answer_type' => '1',
                    'is_show' => '1',
                    'is_require' => '1',
                    'options' => [
                        [
                            'option_value' => '男',
                        ],
                        [
                            'option_value' => '女',
                        ]
                    ]
                ],
                [
                    'question_title' => '真实姓名',
                    'answer_type' => '0',
                    'is_show' => '1',
                    'is_require' => '1',
                    'options' => [],
                ],
                [
                    'question_title' => '选择爱好',
                    'answer_type' => '2',
                    'is_show' => '1',
                    'is_require' => '0',
                    'options' => [
                        [
                            'option_value' => '篮球',
                        ],
                        [
                            'option_value' => '足球',
                        ],
                        [
                            'option_value' => '羽毛球',
                        ],
                        [
                            'option_value' => '台球',
                        ],
                    ]
                ],
            ]
        ];*/
        $data = [
            'qnr_id' => '20',
            'qnr_name' => '无敌问卷',
            'is_open' => '1',
            'is_force' => '1',
            'questions' => [
                [
                    'question_id' => '38',
                    'question_title' => '真实姓名',
                    'answer_type' => '0',
                    'is_show' => '1',
                    'is_require' => '1',
                    'options' => [],
                ],
                [
                    'question_id' => '37',
                    'question_title' => '选择性别',
                    'answer_type' => '1',
                    'is_show' => '1',
                    'is_require' => '1',
                    'options' => [
                        [
                            'option_id' => '10',
                            'option_value' => '女',
                        ],
                        [
                            'option_id' => '9',
                            'option_value' => '男',
                        ],
                    ]
                ],
                [
                    'question_id' => '39',
                    'question_title' => '选择爱好',
                    'answer_type' => '2',
                    'is_show' => '1',
                    'is_require' => '0',
                    'options' => [
                        [
                            'option_id' => '11',
                            'option_value' => '篮球',
                        ],
                        [
                            'option_id' => '13',
                            'option_value' => '羽毛球',
                        ],
                        [
                            'option_id' => '14',
                            'option_value' => '台球',
                        ],
                        [
                            'option_id' => '12',
                            'option_value' => '足球',
                        ],
                    ]
                ],
            ]
        ];
        $result = $model->updateQnr($data);
        dump($result);
    }

    public function testAnswer()
    {
        $admin = [
            'id' => '106',
            'username' => 'test@qq.com',
            'company_id' => '11',
            'company_name' => '测试2'
        ];
        session('ADMIN', $admin);
        $model = D('QnrAnswer');

        $data = file_get_contents(RUNTIME_PATH . '/data.json');
        $data = jsonDecodeToArr($data);
        $result = $model->updateAnswer($data);
    }

    public function get()
    {
        static $data;
        if (isset($data)) {
            return $data;
        } else {
            $data = '1';
        }
        return $data;
    }

    public function testStatic()
    {
        $model = D('Qnr');
        $data = [];
        $data['qnr_id'] = '20';
        $data['num'] = ['exp', 'num + 1'];
        $model->startTrans();
        $model->save($data);
        $sql = $model->_sql();
        $model->rollback();
    }

    public function testTree()
    {
        $list = [
            ['id' => 1, 'pid' => 0, 'name' => '1'],
            ['id' => 2, 'pid' => 1, 'name' => '1-1'],
            ['id' => 3, 'pid' => 1, 'name' => '1-2'],
            ['id' => 4, 'pid' => 3, 'name' => '1-2-1'],
            ['id' => 5, 'pid' => 0, 'name' => '2'],
            ['id' => 6, 'pid' => 5, 'name' => '2-1'],
            ['id' => 7, 'pid' => 5, 'name' => '2-2'],
            ['id' => 8, 'pid' => 6, 'name' => '2-1-1'],
        ];
        $other = [
            ['name' => 'name', 'pname' => 'name', 'link' => ' > ']
        ];
        $list = getTreeArr($list, 'id', 'pid', 'child', '0');
        $list = unTree($list);
        $list = getTreeArr($list, 'id', 'pid', 'child', '0', $other);
        $list = unTree($list);
    }

    public function testNavArt()
    {
        $model = D('Nav');
        $data = $model->getRecommendNavAndArt();
        $result = 1;
    }
}