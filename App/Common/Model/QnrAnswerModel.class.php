<?php

namespace Common\Model;

use Common\Interfaces\Action;

/**
 * 回答列表
 * Class QnrAnswerModel
 * @package Common\Model
 * User: czx
 * Date: 2019-1-12 14:11:45
 * Update: 2018-05-25 14:11:45
 * Version: 1.00
 * company 厦门市虚拟与增强显示产业协会
 * Copyright  ©2018   All rights reserved
 */
class QnrAnswerModel extends BaseModel implements Action
{
    protected $tableName = "qnr_answer";

    /**
     * 获取列表的查询条件
     * @param array $request
     * @return array
     * User: hjun
     * Date: 2019-01-16 01:04:33
     * Update: 2019-01-16 01:04:33
     * Version: 1.00
     */
    public function getAnswerListQueryWhere($request = [])
    {
        $where = [];
        // 问卷ID
        if (!empty($request['qnr_id'])) {
            $where['a.qnr_id'] = $request['qnr_id'];
        }
        // 问卷名称
        if (!empty($request['qnr_name'])) {
            $where['a.qnr_name'] = ['LIKE', "%{$request['qnr_name']}%"];
        }
        // 企业ID
        if (!empty($request['company_id'])) {
            $where['a.company_id'] = $request['company_id'];
        }
        // 企业名称
        if (!empty($request['company_name'])) {
            $where['a.company_name'] = ['LIKE', "%{$request['company_name']}%"];
        }
        // 回答时间
        $result = getRangeWhere($request, 'min_create_time', 'max_create_time');
        $createTimeWhere = $result['data'];
        if (!empty($createTimeWhere)) {
            $where['a.create_time'] = $createTimeWhere;
        }
        return $where;
    }

    /**
     * 获取回答列表
     * @param int $page
     * @param int $limit
     * @param array $queryWhere
     * @return array
     * User: hjun
     * Date: 2019-01-16 00:40:52
     * Update: 2019-01-16 00:40:52
     * Version: 1.00
     */
    public function getAnswerListData($page = DF_PAGE, $limit = DF_PAGE_LIMIT, $queryWhere = [])
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
     * 获取回答
     * @param int $answerId
     * @return array
     * User: hjun
     * Date: 2019-01-16 01:08:36
     * Update: 2019-01-16 01:08:36
     * Version: 1.00
     */
    public function getAnswer($answerId = 0)
    {
        if (empty($answerId)) {
            return [];
        }
        $field = [];
        $field = implode(',', $field);
        $where = [];
        $where['a.answer_id'] = $answerId;
        $where['a.is_delete'] = NOT_DELETED;
        $options = [];
        $options['alias'] = 'a';
        $options['field'] = $field;
        $options['where'] = $where;
        $info = $this->queryRow($options);
        if (empty($info)) {
            return [];
        }
        $info['detail'] = jsonDecodeToArr($info['detail']);
        return $info;
    }

    /**
     * 从缓存中获取答案
     * @param int $answerId
     * @return array
     * User: hjun
     * Date: 2019-01-17 01:56:58
     * Update: 2019-01-17 01:56:58
     * Version: 1.00
     */
    public function getAnswerFromCache($answerId = 0)
    {
        $info = $this->getLastQueryData("answer_{$answerId}");
        if (empty($info)) {
            $info = $this->getAnswer($answerId);
            $this->setLastQueryData("answer_{$answerId}", $info);
        }
        return $info;
    }

    /**
     *
     * @param array $questions
     * @return array
     * User: hjun
     * Date: 2019-01-17 01:38:12
     * Update: 2019-01-17 01:38:12
     * Version: 1.00
     */
    public function getQuestionsObject($questions = [])
    {
        $newQuestions = [];
        foreach ($questions as $question) {
            $newOptions = [];
            foreach ($question['options'] as $option) {
                $newOptions[$option['option_id']] = $option;
            }
            $question['options'] = $newOptions;
            $newQuestions[$question['question_id']] = $question;
        }
        return $newQuestions;
    }

    /**
     *
     * @param array $answers $answers
     * @return array
     * User: hjun
     * Date: 2019-01-17 01:38:12
     * Update: 2019-01-17 01:38:12
     * Version: 1.00
     */
    public function getAnswerObject($answers = [])
    {
        $new = [];
        foreach ($answers as $answer) {
            $new[$answer['question_id']] = $answer;
        }
        return $new;
    }

    /**
     * 验证问卷有效性
     * @param int $qnrId
     * @return boolean
     * User: hjun
     * Date: 2019-01-17 00:16:33
     * Update: 2019-01-17 00:16:33
     * Version: 1.00
     */
    public function validateAddAnswer($qnrId = 0)
    {
        $qnr = D('Qnr')->getQnr($qnrId);
        if (empty($qnr)) {
            $this->setValidateError("问卷已失效");
            return false;
        }
        $admin = $this->getAdmin();
        $where = [];
        $where['qnr_id'] = $qnrId;
        $where['company_id'] = $admin['company_id'];
        $where['is_delete'] = NOT_DELETED;
        $answer = $this->field('answer_id')->where($where)->find();
        if (!empty($answer)) {
            $this->setValidateError("您已回答过该问卷,无需重复答卷");
            return false;
        }
        return true;
    }

    /**
     * 验证能否修改答案
     * @param int $answerId
     * @param int $qnrId
     * @return boolean
     * User: hjun
     * Date: 2019-01-17 00:47:54
     * Update: 2019-01-17 00:47:54
     * Version: 1.00
     */
    public function validateUpdateAnswer($answerId = 0, $qnrId = 0)
    {
        $answer = $this->getAnswerFromCache($answerId);
        if (empty($answer)) {
            $this->setValidateError("答案已失效");
            return false;
        }
        if ($qnrId != $answer['qnr_id']) {
            $this->setValidateError("当前问卷与您的答案无法匹配,无法修改");
            return false;
        }
        return true;
    }

    /**
     * 验证答案详情
     * @param array $detail
     * @param int $qnrId
     * @return boolean
     * User: hjun
     * Date: 2019-01-17 00:26:01
     * Update: 2019-01-17 00:26:01
     * Version: 1.00
     */
    public function validateDetail($detail = [], $qnrId = 0)
    {
        $qnr = D('Qnr')->getQnr($qnrId);
        $questions = $qnr['questions'];
        $answers = $this->getAnswerObject($detail);
        // 检查回答
        foreach ($questions as $key => $currentQuestion) {
            $answer = $answers[$currentQuestion['question_id']];
            $index = "第" . ($key + 1);
            // 答案类型
            switch ($currentQuestion['answer_type']) {
                // 文本类型
                case QNR_ANSWER_TYPE_TEXT:
                    // 必填没填
                    if (empty($answer['option_value']) && $currentQuestion['is_require'] == 1) {
                        $this->setValidateError("请输入{$index}题的答案");
                        return false;
                    }
                    break;
                // 单选 多选
                case QNR_ANSWER_TYPE_ONE:
                case QNR_ANSWER_TYPE_MULTI:
                    // 必填没填
                    if (empty($answer['option_id']) && $currentQuestion['is_require'] == 1) {
                        $this->setValidateError("请选择{$index}题的答案");
                        return false;
                    }
                    break;
                default:
                    $this->setValidateError("未知题目");
                    return false;
                    break;
            }
            if (empty($answer['question_id'])) {
                $this->setValidateError("问卷已更新,请同步");
                return false;
            }
        }
        return true;
    }

    /**
     * 自动完成问卷名称
     * @param int $qnrId
     * @return string
     * User: hjun
     * Date: 2019-01-17 01:17:05
     * Update: 2019-01-17 01:17:05
     * Version: 1.00
     */
    public function autoQnrName($qnrId = 0)
    {
        $qnr = D('Qnr')->getQnr($qnrId);
        return $qnr['qnr_name'];
    }

    /**
     * 自动完成快照
     * @param array $detail
     * @param int $qnrId
     * @return string
     * User: hjun
     * Date: 2019-01-17 00:51:29
     * Update: 2019-01-17 00:51:29
     * Version: 1.00
     */
    public function autoDetailSnapshot($detail = [], $qnrId = 0)
    {
        $qnr = D('Qnr')->getQnr($qnrId);
        $questions = $qnr['questions'];
        $newQuestions = $this->getQuestionsObject($questions);
        $data = [];
        foreach ($detail as $key => $answer) {
            $item = [];
            $item['question_id'] = $answer['question_id'];
            $item['option_id'] = empty($answer['option_id']) ? '' : $answer['option_id'];
            $item['option_value'] = '';
            $currentQuestion = $newQuestions[$answer['question_id']];
            // 答案类型
            switch ($currentQuestion['answer_type']) {
                // 文本类型
                case QNR_ANSWER_TYPE_TEXT:
                    $item['option_value'] = $answer['option_value'];
                    break;
                // 单选
                case QNR_ANSWER_TYPE_ONE:
                    $item['option_value'] = $currentQuestion['options'][$answer['option_id']]['option_value'];
                    break;
                // 多选
                case QNR_ANSWER_TYPE_MULTI:
                    if (is_array($answer['option_id'])) {
                        foreach ($answer['option_id'] as $id) {
                            if (!empty($item['option_value'])) {
                                $item['option_value'] .= ',';
                            }
                            $item['option_value'] .= $currentQuestion['options'][$id]['option_value'];
                        }
                    } else {
                        $item['option_value'] = $currentQuestion['options'][$answer['option_id']]['option_value'];
                    }
                    break;
                default:
                    break;
            }
            $data[] = $item;
        }
        return jsonEncode($data);
    }

    /**
     * 自动完成要插入答案详情表的数据
     * @param array $detail
     * @param array $answer
     * @return array
     * User: hjun
     * Date: 2019-01-17 00:53:09
     * Update: 2019-01-17 00:53:09
     * Version: 1.00
     */
    public function autoDetail($detail = [], $answer = [])
    {
        $qnrId = $answer['qnr_id'];
        $answerId = $answer['answer_id'];
        $qnr = D('Qnr')->getQnr($qnrId);
        $questions = $qnr['questions'];
        $newQuestions = $this->getQuestionsObject($questions);
        $data = [];
        foreach ($detail as $key => $answer) {
            $item = [];
            $item['qnr_id'] = $qnrId;
            $item['answer_id'] = $answerId;
            $item['question_id'] = $answer['question_id'];
            $item['option_id'] = empty($answer['option_id']) ? '' : $answer['option_id'];
            $item['option_value'] = '';
            $currentQuestion = $newQuestions[$answer['question_id']];
            // 答案类型
            switch ($currentQuestion['answer_type']) {
                // 文本类型
                case QNR_ANSWER_TYPE_TEXT:
                    $item['option_value'] = $answer['option_value'];
                    break;
                // 单选
                case QNR_ANSWER_TYPE_ONE:
                    $item['option_value'] = $currentQuestion['options'][$answer['option_id']]['option_value'];
                    break;
                // 多选
                case QNR_ANSWER_TYPE_MULTI:
                    if (is_array($answer['option_id'])) {
                        foreach ($answer['option_id'] as $id) {
                            $item['option_id'] = $id;
                            $item['option_value'] = $currentQuestion['options'][$id]['option_value'];
                            $data[] = $item;
                        }
                        continue 2;
                    } else {
                        $item['option_value'] = $currentQuestion['options'][$answer['option_id']]['option_value'];
                    }
                    break;
                default:
                    break;
            }
            $data[] = $item;
        }
        return $data;
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
    public function getFieldsByAction($action = QNR_ANSWER_ACTION_ADD, $request = [])
    {
        $table = [];
        $table[QNR_ANSWER_ACTION_ADD] = [
            'qnr_id', 'qnr_name', 'company_id', 'company_name', 'detail', 'create_time', 'save_time'
        ];
        $table[QNR_ANSWER_ACTION_UPDATE] = [
            'detail', 'save_time'
        ];
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
    public function getValidateByAction($action = QNR_ANSWER_ACTION_ADD, $request = [])
    {
        $table = [];
        $table[QNR_ANSWER_ACTION_ADD] = [
            ['qnr_id', 'validateAddAnswer', '问卷已失效', self::MUST_VALIDATE, 'callback', self::MODEL_INSERT],
            ['detail', 'validateDetail', '请填写答案', self::MUST_VALIDATE, 'callback', self::MODEL_INSERT, [$request['qnr_id']]],
        ];
        $table[QNR_ANSWER_ACTION_UPDATE] = [
            ['answer_id', 'validateUpdateAnswer', '答案已失效', self::MUST_VALIDATE, 'callback', self::MODEL_UPDATE, [$request['qnr_id']]],
            ['detail', 'validateDetail', '请填写答案', self::MUST_VALIDATE, 'callback', self::MODEL_UPDATE, [$request['qnr_id']]],
        ];
        return isset($table[$action]) ? $table[$action] : [];
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
    public function getAutoByAction($action = QNR_ANSWER_ACTION_ADD, $request = [])
    {
        $table = [];
        $admin = $this->getAdmin();
        $table[QNR_ANSWER_ACTION_ADD] = [
            ['qnr_id', $request['qnr_id'], self::MODEL_INSERT, 'string'],
            ['qnr_name', $this->autoQnrName($request['qnr_id']), self::MODEL_INSERT, 'string'],
            ['company_id', $admin['company_id'], self::MODEL_INSERT, 'string'],
            ['company_name', $admin['company_name'], self::MODEL_INSERT, 'string'],
            ['detail', $this->autoDetailSnapshot($request['detail'], $request['qnr_id']), self::MODEL_INSERT, 'string'],
            ['create_time', NOW_TIME, self::MODEL_INSERT, 'string'],
            ['save_time', NOW_TIME, self::MODEL_INSERT, 'string'],
        ];
        $table[QNR_ANSWER_ACTION_UPDATE] = [
            ['detail', $this->autoDetailSnapshot($request['detail'], $request['qnr_id']), self::MODEL_UPDATE, 'string'],
            ['save_time', NOW_TIME, self::MODEL_UPDATE, 'string'],
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
    public function getTypeByAction($action = QNR_ANSWER_ACTION_ADD, $request = [])
    {
        $table = [];
        $table[QNR_ANSWER_ACTION_ADD] = self::MODEL_INSERT;
        $table[QNR_ANSWER_ACTION_UPDATE] = self::MODEL_UPDATE;
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
    public function getDescByAction($action = QNR_ANSWER_ACTION_ADD, $request = [])
    {
        $table = [];
        $table[QNR_ANSWER_ACTION_ADD] = "回答问卷";
        $table[QNR_ANSWER_ACTION_UPDATE] = "修改问卷答案";
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
    public function action($action = QNR_ANSWER_ACTION_ADD, $request = [])
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
        $results = [];
        $answerDetail = M('qnr_answer_detail');
        if ($action == QNR_ANSWER_ACTION_ADD) {
            $oldData = [];
            $data['answer_id'] = $this->add($data);
            $results[] = $data['answer_id'];
            // 问卷回答数量+1
            $where = [];
            $where['qnr_id'] = $request['qnr_id'];
            $results[] = D('Qnr')->where($where)->setInc('num');
        } else {
            $oldData = $this->getAnswerFromCache($request['answer_id']);
            $data['answer_id'] = $request['answer_id'];
            $results[] = $this->save($data);
            // 如果是修改答案 清空之前的答案
            if ($action == QNR_ANSWER_ACTION_UPDATE) {
                $where = [];
                $where['answer_id'] = $data['answer_id'];
                $results[] = $answerDetail->where($where)->delete();
            }
        }
        $data = array_merge($oldData, $data);

        // 答案明细增加
        if ($action == QNR_ANSWER_ACTION_ADD || $action == QNR_ANSWER_ACTION_UPDATE) {
            $detail = $this->autoDetail($request['detail'], $data);
            $results[] = $answerDetail->addAll($detail);
        }

        if (in_array(false, $results, true)) {
            $this->rollback();
            return getReturn(CODE_ERROR);
        }
        $this->commit();
        return getReturn(CODE_SUCCESS, 'success', $data);
    }

    /**
     * 回答问卷
     * @param array $request
     * @return array ['code'=>200, 'msg'=>'', 'data'=>null]
     * User: hjun
     * Date: 2019-01-17 00:09:10
     * Update: 2019-01-17 00:09:10
     * Version: 1.00
     */
    public function addAnswer($request = [])
    {
        $result = $this->action(QNR_ANSWER_ACTION_ADD, $request);
        return $result;
    }

    /**
     * 修改问卷答案
     * @param array $request
     * @return array ['code'=>200, 'msg'=>'', 'data'=>null]
     * User: hjun
     * Date: 2019-01-17 00:09:15
     * Update: 2019-01-17 00:09:15
     * Version: 1.00
     */
    public function updateAnswer($request = [])
    {
        $result = $this->action(QNR_ANSWER_ACTION_UPDATE, $request);
        return $result;
    }
}