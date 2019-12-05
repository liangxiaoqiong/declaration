<?php

namespace Common\Model;

use Common\Interfaces\Action;

/**
 * 问卷模型
 * Class QnrModel
 * @package Common\Model
 * User: czx
 * Date: 2019-1-12 14:11:45
 * Update: 2018-05-25 14:11:45
 * Version: 1.00
 * company 厦门市虚拟与增强显示产业协会
 * Copyright  ©2018   All rights reserved
 */
class QnrModel extends BaseModel implements Action
{
    protected $tableName = "qnr";

    /**
     * 获取列表的查询条件
     * @param array $request
     * @return array
     * User: hjun
     * Date: 2019-01-16 01:04:33
     * Update: 2019-01-16 01:04:33
     * Version: 1.00
     */
    public function getQnrListQueryWhere($request = [])
    {
        $where = [];
        // 问卷名称
        if (!empty($request['qnr_name'])) {
            $where['a.qnr_name'] = ['LIKE', "%{$request['qnr_name']}%"];
        }
        // 创建时间
        $result = getRangeWhere($request, 'min_create_time', 'max_create_time');
        $createTimeWhere = $result['data'];
        if (!empty($createTimeWhere)) {
            $where['a.create_time'] = $createTimeWhere;
        }
        return $where;
    }

    /**
     * 获取问卷列表
     * @param int $page
     * @param int $limit
     * @param array $queryWhere
     * @return array
     * User: hjun
     * Date: 2019-01-16 00:40:52
     * Update: 2019-01-16 00:40:52
     * Version: 1.00
     */
    public function getQnrListData($page = DF_PAGE, $limit = DF_PAGE_LIMIT, $queryWhere = [])
    {
        $field = [];
        $field = implode(',', $field);
        $where = [];
        $where['a.is_delete'] = NOT_DELETED;
        $where = array_merge($where, $queryWhere);
        $options = [];
        $options['alias'] = 'a';
        $options['field'] = $field;
        $options['where'] = $where;
        $options['page'] = $page;
        $options['limit'] = $limit;
        $total = $this->getCount($options);
        if (empty($total)) {
            $list = [];
        } else {
            $list = $this->queryList($options);
        }
        $data = [];
        $data['total'] = $total;
        $data['list'] = $list;
        return $data;
    }

    /**
     * 获取普通账号的问卷列表
     * @param int $page
     * @param int $limit
     * @param array $queryWhere
     * @return array
     * User: hjun
     * Date: 2019-01-16 00:47:46
     * Update: 2019-01-16 00:47:46
     * Version: 1.00
     */
    public function getMyQnrListData($page = DF_PAGE, $limit = DF_PAGE_LIMIT, $queryWhere = [])
    {
        $where = [];
        $where['a.is_open'] = QNR_OPEN_YES;
        $where = array_merge($where, $queryWhere);
        return $this->getQnrListData($page, $limit, $where);
    }

    /**
     * 获取问卷数据(包含问题)
     * @param int $qnrId
     * @return array
     * User: hjun
     * Date: 2019-01-16 01:46:59
     * Update: 2019-01-16 01:46:59
     * Version: 1.00
     */
    public function getQnr($qnrId = 0)
    {
        $info = S("qnr_{$qnrId}");
        if (!empty($info)) {
            return $info;
        }
        $field = [
            'a.qnr_id', 'a.qnr_name', 'a.is_open', 'a.is_force', 'a.num', 'a.create_time',
            'b.question_id', 'b.question_title', 'b.sort', 'b.answer_type', 'b.is_show',
            'b.is_require', 'b.is_delete question_is_delete',
            'c.option_id', 'c.option_value', 'c.sort', 'c.is_delete option_is_delete'
        ];
        $field = implode(',', $field);
        $join = [
            '__QNR_QUESTION__ b ON a.qnr_id = b.qnr_id',
            'LEFT JOIN __QNR_QUESTION_OPTION__ c ON b.question_id = c.question_id',
        ];
        $where = [];
        $where['a.qnr_id'] = $qnrId;
        $order = 'b.sort ASC,b.question_id ASC,c.sort ASC,c.option_id ASC';
        $options = [];
        $options['alias'] = 'a';
        $options['field'] = $field;
        $options['join'] = $join;
        $options['where'] = $where;
        $options['order'] = $order;
        $list = $this->queryList($options);
        // 组装数据
        $info = $list[0];
        if (empty($info)) {
            return [];
        }
        $info['questions'] = [];
        $questions = [];
        $options = [];
        foreach ($list as $value) {
            // 组装选项 如果是文本框类型 则初始化为空数组
            if ($value['answer_type'] == QNR_ANSWER_TYPE_TEXT) {
                $options[$value['question_id']] = [];
            } else {
                $options[$value['question_id']][] = [
                    'option_id' => $value['option_id'],
                    'option_value' => $value['option_value'],
                    'is_delete' => $value['option_is_delete'],
                ];
            }

            // 初始化问题
            if (!isset($questions[$value['question_id']])) {
                $questions[$value['question_id']] = [
                    'question_id' => $value['question_id'],
                    'question_title' => $value['question_title'],
                    'sort' => $value['sort'],
                    'answer_type' => $value['answer_type'],
                    'is_show' => $value['is_show'],
                    'is_require' => $value['is_require'],
                    'is_delete' => $value['question_is_delete'],
                ];
            }
        }
        // 将选项设置进问题中
        foreach ($questions as $questionId => $question) {
            $question['options'] = $options[$questionId];
            $info['questions'][] = $question;
        }
        S("qnr_{$qnrId}", $info);
        return $info;
    }

    /**
     * 从内存中获取问卷
     * @param int $qnrId
     * @return array
     * User: hjun
     * Date: 2019-01-16 04:00:58
     * Update: 2019-01-16 04:00:58
     * Version: 1.00
     */
    public function getQnrFromCache($qnrId = 0)
    {
        $info = $this->getLastQueryData("qnr_{$qnrId}");
        if (empty($info)) {
            $info = $this->getQnr($qnrId);
            $this->setLastQueryData("qnr_{$qnrId}", $info);
        }
        return $info;
    }

    /**
     * 验证问卷
     * @param int $qnrId
     * @return boolean
     * User: hjun
     * Date: 2019-01-16 04:01:27
     * Update: 2019-01-16 04:01:27
     * Version: 1.00
     */
    public function validateQnr($qnrId = 0)
    {
        $info = $this->getQnrFromCache($qnrId);
        if (empty($info)) {
            return false;
        }
        return true;
    }

    /**
     * 验证问题JSON格式
     * @param array $questions
     * @return boolean
     * User: hjun
     * Date: 2019-01-16 03:40:39
     * Update: 2019-01-16 03:40:39
     * Version: 1.00
     */
    public function validateQuestions($questions = [])
    {
        if (empty($questions)) {
            $this->setValidateError('未设置问题');
            return false;
        }
        // 检查问题的JSON格式
        $schema = getDefaultData('json/qnr/questions_schema');
        $this->setJsonValidate([
            'question_title' => ['请输入题目'],
            'sort' => ['请设置排序'],
            'answer_type' => ['请选择答卷类型'],
            'is_show' => ['请设置是否显示'],
            'is_require' => ['请设置是否必填'],
        ]);
        if (!$this->checkJsonSchema($questions, $schema)) {
            return false;
        }
        // 检查问题选项的JSON格式
        $schema = getDefaultData('json/qnr/options_schema');
        foreach ($questions as $key => $question) {
            // 如果是文本框类型 不需要验证
            if ($question['answer_type'] == 0) {
                continue;
            }
            // 没设置选项
            if (empty($question['options'])) {
                $this->setValidateError('请设置第' . ($key + 1) . '个问题的选项');
                return false;
            }
            // 验证JSON
            $this->setJsonValidate([
                'option_value' => ['请设置第' . ($key + 1) . '个问题的选项值'],
                'sort' => ['请设置第' . ($key + 1) . '个问题的选项的排序'],
            ]);
            if (!$this->checkJsonSchema($question['options'], $schema)) {
                return false;
            }
        }
        return true;
    }

    /**
     * 验证能否改变开启状态
     * @param int $isOpen
     * @param int $qnrId
     * @return boolean
     * User: hjun
     * Date: 2019-01-16 04:03:31
     * Update: 2019-01-16 04:03:31
     * Version: 1.00
     */
    public function validateChangeOpen($isOpen = QNR_OPEN_NO, $qnrId = 0)
    {
        // 关闭无限制
        if ($isOpen == QNR_OPEN_NO) {
            return true;
        }
        // 只能开启一个
        $where = [];
        $where['qnr_id'] = ['neq', $qnrId];
        $where['is_open'] = QNR_OPEN_YES;
        $where['is_delete'] = NOT_DELETED;
        $count = $this->field('qnr_id')->where($where)->find();
        if (!empty($count)) {
            $this->setValidateError("已经开启一份问卷,无法开启多个问卷");
            return false;
        }
        return true;
    }

    /**
     * 获取操作字段
     * @param int $action
     * @param array $request
     * @return array
     * User: hjun
     * Date: 2019-01-13 13:33:25
     * Update: 2019-01-13 13:33:25
     * Version: 1.00
     */
    public function getFieldsByAction($action = QNR_ACTION_ADD, $request = [])
    {
        $table = [];
        $table[QNR_ACTION_ADD] = [
            'qnr_name', 'create_time', 'save_time'
        ];
        $table[QNR_ACTION_UPDATE] = [
            'qnr_name', 'save_time'
        ];
        $table[QNR_ACTION_CHANGE_OPEN] = ['is_open'];
        $table[QNR_ACTION_CHANGE_FORCE] = ['is_force'];
        return isset($table[$action]) ? $table[$action] : [];
    }

    /**
     * 根据操作获取验证规则
     * @param int $action
     * @param array $request
     * @return array
     * User: hjun
     * Date: 2019-01-13 14:55:28
     * Update: 2019-01-13 14:55:28
     * Version: 1.00
     */
    public function getValidateByAction($action = QNR_ACTION_ADD, $request = [])
    {
        $table = [];
        $table[QNR_ACTION_ADD] = [
            ['qnr_name', 'require', '请输入问卷名称', self::MUST_VALIDATE, 'regex', self::MODEL_INSERT],
            ['questions', 'validateQuestions', '问题设置有误,请检查', self::MUST_VALIDATE, 'callback', self::MODEL_INSERT],
        ];
        $table[QNR_ACTION_UPDATE] = [
            ['qnr_id', 'validateQnr', '问卷已失效', self::MUST_VALIDATE, 'callback', self::MODEL_UPDATE],
            ['qnr_name', 'require', '请输入问卷名称', self::MUST_VALIDATE, 'regex', self::MODEL_UPDATE],
            ['questions', 'validateQuestions', '问题设置有误,请检查', self::MUST_VALIDATE, 'callback', self::MODEL_UPDATE],
        ];
        $table[QNR_ACTION_CHANGE_OPEN] = [
            ['qnr_id', 'validateQnr', '问卷已失效', self::MUST_VALIDATE, 'callback', self::MODEL_UPDATE],
            ['is_open', 'validateChangeOpen', '当期无法改变开启状态', self::MUST_VALIDATE, 'callback', self::MODEL_UPDATE, [$request['qnr_id']]],
        ];
        $table[QNR_ACTION_CHANGE_FORCE] = [
            ['qnr_id', 'validateQnr', '问卷已失效', self::MUST_VALIDATE, 'callback', self::MODEL_UPDATE],
        ];
        return isset($table[$action]) ? $table[$action] : [];
    }

    /**
     * 自动完成问题
     * @param array $request
     * @param array $qnr
     * @return array
     * User: hjun
     * Date: 2019-01-16 04:08:34
     * Update: 2019-01-16 04:08:34
     * Version: 1.00
     */
    public function autoQuestions($request = [], $qnr = [])
    {
        $questions = $request['questions'];
        $data = [];
        $i = 0;
        $add = [];
        $save = [];
        foreach ($questions as $question) {
            $item = [
                'qnr_id' => $qnr['qnr_id'],
                'question_title' => $question['question_title'],
                'sort' => $i,
                'answer_type' => $question['answer_type'],
                'is_show' => $question['is_show'],
                'is_require' => $question['is_require'],
            ];
            if (empty($question['question_id'])) {
                // 无ID说明是新增的 放到一个数组中
                $add[] = $item;
            } else {
                // 有ID说明是修改的 放到一个数组中
                $item['question_id'] = $question['question_id'];
                $save[] = $item;
            }
            $i++;
        }
        $data['save'] = $save;
        $data['add'] = $add;
        return $data;
    }

    /**
     * 自动完成问题的选项
     * @param array $request
     * @param array $autoQuestionData 自动完成的问题列表
     * @return array
     * User: hjun
     * Date: 2019-01-16 04:14:08
     * Update: 2019-01-16 04:14:08
     * Version: 1.00
     */
    public function autoQuestionsOptions($request = [], $autoQuestionData = [])
    {
        $questions = $request['questions'];
        $optionsData = [
            'save' => [], 'add' => [],
        ];
        $save = [];
        $add = [];
        foreach ($questions as $question) {
            if (empty($question['question_id'])) {
                // 无ID说明是新增的 放到一个数组中
                $optionsData['add'][] = [
                    'options' => $question['options'],
                ];
            } else {
                // 有ID说明是修改的 放到一个数组中
                $optionsData['save'][] = [
                    'options' => $question['options'],
                ];
            }
        }
        $i = 0;
        $types = ['save', 'add'];
        foreach ($types as $type) {
            foreach ($autoQuestionData[$type] as $key => $question) {
                if ($question['answer_type'] != QNR_ANSWER_TYPE_TEXT) {
                    $options = $optionsData[$type][$key]['options'];
                    foreach ($options as $option) {
                        $item = [
                            'qnr_id' => $question['qnr_id'],
                            'question_id' => $question['question_id'],
                            'option_value' => $option['option_value'],
                            'sort' => $i++,
                        ];
                        if (empty($option['option_id'])) {
                            $add[] = $item;
                        } else {
                            $item['option_id'] = $option['option_id'];
                            $save[] = $item;
                        }
                    }
                }
            }
        }
        $data = [
            'save' => $save,
            'add' => $add,
        ];
        return $data;
    }

    /**
     * 根据操作获取完成规则
     * @param int $action
     * @param array $request
     * @return array
     * User: hjun
     * Date: 2019-01-13 15:06:04
     * Update: 2019-01-13 15:06:04
     * Version: 1.00
     */
    public function getAutoByAction($action = QNR_ACTION_ADD, $request = [])
    {
        $table = [];
        $table[QNR_ACTION_ADD] = [
            ['create_time', NOW_TIME, self::MODEL_INSERT, 'string'],
            ['save_time', NOW_TIME, self::MODEL_INSERT, 'string'],
        ];
        $table[QNR_ACTION_UPDATE] = [
            ['save_time', NOW_TIME, self::MODEL_UPDATE, 'string'],
        ];
        $table[QNR_ACTION_CHANGE_OPEN] = [
            ['is_open', $request['is_open'], self::MODEL_UPDATE, 'string'],
        ];
        $table[QNR_ACTION_CHANGE_FORCE] = [
            ['is_force', $request['is_force'], self::MODEL_UPDATE, 'string'],
        ];
        return isset($table[$action]) ? $table[$action] : [];
    }

    /**
     * 根据操作获取 数据库操作类型
     * @param int $action
     * @param array $request
     * @return int
     * User: hjun
     * Date: 2019-01-13 15:06:26
     * Update: 2019-01-13 15:06:26
     * Version: 1.00
     */
    public function getTypeByAction($action = QNR_ACTION_ADD, $request = [])
    {
        $table = [];
        $table[QNR_ACTION_ADD] = self::MODEL_INSERT;
        $table[QNR_ACTION_UPDATE] = self::MODEL_UPDATE;
        $table[QNR_ACTION_CHANGE_OPEN] = self::MODEL_UPDATE;
        $table[QNR_ACTION_CHANGE_FORCE] = self::MODEL_UPDATE;
        return isset($table[$action]) ? $table[$action] : self::MODEL_UPDATE;
    }

    /**
     * 根据操作获取描述
     * @param int $action
     * @param array $request
     * @return string
     * User: hjun
     * Date: 2019-01-13 18:26:59
     * Update: 2019-01-13 18:26:59
     * Version: 1.00
     */
    public function getDescByAction($action = QNR_ACTION_ADD, $request = [])
    {
        $table = [];
        $table[QNR_ACTION_ADD] = '新增问卷';
        $table[QNR_ACTION_UPDATE] = '修改问卷';
        $table[QNR_ACTION_CHANGE_OPEN] = '修改开启状态';
        $table[QNR_ACTION_CHANGE_OPEN] = '修改强制状态';
        return isset($table[$action]) ? $table[$action] : '';
    }

    /**
     * 操作
     * @param int $action
     * @param array $request
     * @return array ['code'=>200, 'msg'=>'', 'data'=>null]
     * User: hjun
     * Date: 2019-01-13 14:44:47
     * Update: 2019-01-13 14:44:47
     * Version: 1.00
     */
    public function action($action = QNR_ACTION_ADD, $request = [])
    {
        $fields = $this->getFieldsByAction($action, $request);
        $validate = $this->getValidateByAction($action, $request);
        $auto = $this->getAutoByAction($action, $request);
        $type = $this->getTypeByAction($action, $request);
        $result = $this->getAndValidateDataFromRequest($fields, $request, $validate, $auto, $type);
        if (!isSuccess($result)) {
            return $result;
        }

        $data = $result['data'];

        $this->startTrans();
        $qnrQuestion = M('qnr_question');
        $qnrQuestionOption = M('qnr_question_option');
        $results = [];
        if ($type === self::MODEL_INSERT) {
            $data['qnr_id'] = $this->add($data);
            $results[] = $data['qnr_id'];
        } else {
            $data['qnr_id'] = $request['qnr_id'];
            $results[] = $this->save($data);
            if ($action == QNR_ACTION_UPDATE) {
                // 更新名称
                $oldData = $this->getQnrFromCache($data['qnr_id']);
                if ($oldData['qnr_name'] != $data['qnr_name']) {
                    $options = [];
                    $options['id_key'] = 'qnr_id';
                    $options['id'] = $data['qnr_id'];
                    $options['name_key'] = 'qnr_name';
                    $options['name'] = $data['qnr_name'];
                    $options['tables'] = ['qnr_answer'];
                    $results[] = syncUpdateName($options);
                }
                // 修改之前 把问题和选项都清空
                $where = [];
                $where['qnr_id'] = $data['qnr_id'];
                $results[] = $qnrQuestion->where($where)->delete();
                $results[] = $qnrQuestionOption->where($where)->delete();
            }
        }

        // 新增和修改时 才需要变更问题、选项
        if ($action == QNR_ACTION_ADD || $action == QNR_ACTION_UPDATE) {
            // 新增问题
            $questionData = $this->autoQuestions($request, $data);
            if (!empty($questionData['save'])) {
                $results[] = $qnrQuestion->addAll($questionData['save']);
            }
            if (!empty($questionData['add'])) {
                $firstQuestionId = $qnrQuestion->addAll($questionData['add']);
                foreach ($questionData['add'] as $key => $value) {
                    $value['question_id'] = $firstQuestionId++;
                    $questionData['add'][$key] = $value;
                }
                $results[] = $firstQuestionId;
            }

            // 新增问题的选项
            $optionData = $this->autoQuestionsOptions($request, $questionData);
            if (!empty($optionData['save'])) {
                $results[] = $qnrQuestionOption->addAll($optionData['save']);
            }
            if (!empty($optionData['add'])) {
                $firstOptionId = $qnrQuestionOption->addAll($optionData['add']);
                foreach ($optionData['add'] as $key => $value) {
                    $value['option_id'] = $firstOptionId++;
                    $optionData['add'][$key] = $value;
                }
                $results[] = $firstOptionId;
            }
        }

        if (in_array(false, $results, true)) {
            $this->rollback();
            return getReturn(CODE_ERROR);
        }

        $this->commit();
        return getReturn(CODE_SUCCESS, 'success', $data);
    }

    /**
     * 新增问卷
     * @param array $request
     * @return array ['code'=>200, 'msg'=>'', 'data'=>null]
     * User: hjun
     * Date: 2019-01-16 21:28:43
     * Update: 2019-01-16 21:28:43
     * Version: 1.00
     */
    public function addQnr($request = [])
    {
        $result = $this->action(QNR_ACTION_ADD, $request);
        return $result;
    }

    /**
     * 修改问卷
     * @param array $request
     * @return array ['code'=>200, 'msg'=>'', 'data'=>null]
     * User: hjun
     * Date: 2019-01-16 21:29:37
     * Update: 2019-01-16 21:29:37
     * Version: 1.00
     */
    public function updateQnr($request = [])
    {
        $result = $this->action(QNR_ACTION_UPDATE, $request);
        return $result;
    }

    /**
     * 改变问卷的开启
     * @param array $request
     * @return array ['code'=>200, 'msg'=>'', 'data'=>null]
     * User: hjun
     * Date: 2019-01-16 21:30:20
     * Update: 2019-01-16 21:30:20
     * Version: 1.00
     */
    public function changeQnrOpen($request = [])
    {
        $result = $this->action(QNR_ACTION_CHANGE_OPEN, $request);
        return $result;
    }

    /**
     * 改变问卷的强制
     * @param array $request
     * @return array ['code'=>200, 'msg'=>'', 'data'=>null]
     * User: hjun
     * Date: 2019-01-16 21:30:29
     * Update: 2019-01-16 21:30:29
     * Version: 1.00
     */
    public function changeQnrForce($request = [])
    {
        $result = $this->action(QNR_ACTION_CHANGE_FORCE, $request);
        return $result;
    }

}