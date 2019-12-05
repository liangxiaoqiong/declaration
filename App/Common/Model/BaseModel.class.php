<?php

/**
 * 基类函数
 * Class BaseModel
 * @package Common\Model
 * User: hjun
 * Date: 2018-04-04 15:19:58
 * Update: 2018-04-04 15:19:58
 * Version: 1.00
 * company 厦门市虚拟与增强显示产业协会
 * Copyright  ©2018   All rights reserved
 */

namespace Common\Model;

use Common\Util\DbCache;
use JsonSchema\Validator;
use Think\Model;

class BaseModel extends Model
{
    const MODEL_DELETE = 4;      //  删除
    const MAX_TAKE = 999999999;

    /*
     * 自动验证
     * array(验证字段1,验证规则,错误提示,[验证条件,附加规则,验证时间,参数列表]),
     * 验证规则: require 字段必须、email 邮箱、url URL地址、currency 货币、number 数字
     * 验证条件: 0 存在字段就验证（默认） 1 必须验证 2 不为空就验证
     * 附加规则:
        regex 正则验证，定义的验证规则是一个正则表达式（默认）
        function 函数验证，定义的验证规则是一个函数名
        callback 方法验证，定义的验证规则是当前模型类的一个方法
        confirm 验证表单中的两个字段是否相同，定义的验证规则是一个字段名
        equal 验证是否等于某个值，该值由前面的验证规则定义
        notequal 验证是否不等于某个值，该值由前面的验证规则定义（3.1.2版本新增）
        in 验证是否在某个范围内，定义的验证规则可以是一个数组或者逗号分割的字符串
        notin 验证是否不在某个范围内，定义的验证规则可以是一个数组或者逗号分割的字符串（3.1.2版本新增）
        length
        验证长度，定义的验证规则可以是一个数字（表示固定长度）或者数字范围（例如3,12 表示长度从3到12
        的范围）
        between 验证范围，定义的验证规则表示范围，可以使用字符串或者数组，例如1,31或者array(1,31)
        notbetween 验证不在某个范围，定义的验证规则表示范围，可以使用字符串或者数组（3.1.2版本新增）
        expire 验证是否在有效期，定义的验证规则表示时间范围，可以到时间，例如可以使用 2012-1-15,2013-1-15 表示当前提交有效期在2012-1-15到2013-1-15之间，也可以使用时间戳定义
        ip_allow 验证IP是否允许，定义的验证规则表示允许的IP地址列表，用逗号分隔，例如201.12.2.5,201.12.2.6
        ip_deny 验证IP是否禁止，定义的验证规则表示禁止的ip地址列表，用逗号分隔，例如201.12.2.5,201.12.2.6
        unique 验证是否唯一，系统会根据字段目前的值查询数据库来判断是否存在相同的值，当表单数据中包含主键字段时unique不可用于判断主键字段本身
     * 验证时间: 1 新增 2 编辑 3 全部(默认)
     * 参数列表: array() callback的参数列表
     */
    protected $_validate = [];

    /*
     * 自动完成
     * array(完成字段1,完成规则,[完成时间,附加规则]),
     * 完成字段: 需要进行处理的数据表实际字段名称。
     * 完成规则：需要处理的规则，配合附加规则完成
     * 完成时间：设置自动完成的时间 1-新增 2-更新 3-所有
     * 附加规则：配合验证规则使用:
     *  function 使用函数，表示填充的内容是一个函数名
        callback 回调方法 ，表示填充的内容是一个当前模型的方法
        field 用其它字段填充，表示填充的内容是一个其他字段的值
        string 字符串（默认方式）
        ignore 为空则忽略（3.1.2新增）
     */
    protected $_auto = [];

    protected $jsonValidate = [];

    /**
     * @return array
     */
    public function getJsonValidate()
    {
        return $this->jsonValidate;
    }

    /**
     * @param array $jsonValidate
     */
    public function setJsonValidate($jsonValidate)
    {
        $this->jsonValidate = $jsonValidate;
    }

    /**
     * @var DbCache
     */
    protected $dbCache;

    private $validateError;
    private $lastQueryData;

    public function __construct($name = '', $tablePrefix = '', $connection = '')
    {
        parent::__construct($name, $tablePrefix, $connection);
        $this->dbCache = DbCache::getInstance();
        $this->setAdmin(session('ADMIN'));
        G('a');
    }

    private $admin = [];

    /**
     * @return array
     */
    public function getAdmin()
    {
        return $this->admin;
    }

    /**
     * @param array $admin
     */
    public function setAdmin($admin)
    {
        $this->admin = $admin;
    }

    /**
     * @return mixed
     */
    public function getValidateError()
    {
        return $this->validateError;
    }

    /**
     * @param mixed $validateError
     * @return $this
     */
    public function setValidateError($validateError)
    {
        $this->validateError = $validateError;
        return $this;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getLastQueryData($name = '')
    {
        return $this->lastQueryData[$name];
    }

    /**
     * @param string $name
     * @param mixed $data
     * @return $this
     */
    public function setLastQueryData($name = '', $data)
    {
        $this->lastQueryData[$name] = $data;
        return $this;
    }

    public function getError()
    {
        $error = $this->getValidateError();
        if (isset($error)) {
            return $error;
        }
        return parent::getError();
    }

    /**
     * 获取表的所有字段
     * @param string $alias 别名
     * @return mixed
     * User: hjun
     * Date: 2018-05-22 01:28:16
     * Update: 2018-05-22 01:28:16
     * Version: 1.00
     */
    public function getDbFields($alias = '')
    {
        $fields = parent::getDbFields();
        if (false === $fields) return false;
        if (!empty($alias)) {
            foreach ($fields as &$field) {
                $field = "{$alias}.{$field}";
            }
        }
        return $fields;
    }

    /**
     * 获取上次查询是否强制进行了分页
     * @return bool
     * User: hjun
     * Date: 2018-08-11 14:58:33
     * Update: 2018-08-11 14:58:33
     * Version: 1.00
     */
    public function getLastQueryNeedPage()
    {
        return $this->dbCache->getLastQueryNeedPage();
    }

    /**
     * 获取数量
     * @param array $options
     * @return mixed ['code'=>200,'msg'=>'','data'=>[]]
     * User: hj
     * Date: 2017-10-28 12:31:44
     * Desc:
     * Update: 2017-10-28 12:31:45
     * Version: 1.0
     */
    public function getCount($options = [])
    {
        // 如果是获取数量 则直接返回数量 并且不是分组查询 才能使用count函数直接返回结果数量
        $this->exeQuery($options);
        if (empty($options['group'])) {
            $result = $this->count();
        } else {
            // 只查主键 提高效率
            $pk = empty($options['alias']) ? $this->getPk() : $options['alias'] . '.' . $this->getPk();
            $this->field($pk);
            $result = $this->select();
        }
        if (false === $result) {
            return 0;
        }
        return is_array($result) ? count($result) : (int)$result;
    }

    /**
     * 获取列表
     * @param array $options
     * @return array
     * User: hjun
     * Date: 2018-05-20 02:48:35
     * Update: 2018-05-20 02:48:35
     * Version: 1.00
     */
    public function queryList($options = [])
    {
        $this->dbCache->setModel($this);
        return $this->dbCache->select($options);
    }

    /**
     * 获取信息
     * @param array $options
     * @return array
     * User: hjun
     * Date: 2018-05-20 02:48:47
     * Update: 2018-05-20 02:48:47
     * Version: 1.00
     */
    public function queryRow($options = [])
    {
        $this->dbCache->setModel($this);
        return $this->dbCache->find($options);
    }


    /**
     * 从列表中根据分页获取部分列表
     * @param int $page
     * @param int $limit
     * @param array $list
     * @param mixed $offset
     * @param mixed $length
     * @return array ['code'=>200, 'msg'=>'', 'data'=>null]
     * User: hjun
     * Date: 2018-04-02 04:01:25
     * Update: 2018-04-02 04:01:25
     * Version: 1.00
     */
    public function getListByPage($page = 1, $limit = 20, $list = [], $offset = null, $length = null)
    {
        // 有分页才进行分页截取
        if (isset($offset) && isset($length)) {
            $offset = $offset < 0 ? 0 : $offset;
            $length = $length <= 0 ? null : $length;
            $list = array_slice($list, $offset, $length);
        } else {
            if ($page > 1 || $limit > 0) {
                $page = $page > 0 ? $page : 1;
                $limit = $limit > 0 ? $limit : 20;
                $offset = ($page - 1) * $limit;
                $length = $limit;
                $list = empty($list) ? [] : $list;
                $list = array_slice($list, $offset, $length);
            }
        }
        return empty($list) ? [] : $list;
    }

    /**
     *
     * @param $data
     * @param $options
     * User: hjun
     * Date: 2018-05-24 17:43:01
     * Update: 2018-05-24 17:43:01
     * Version: 1.00
     */
    public function _after_insert($data, $options)
    {
        $this->dbCache->setModel($this);
        $this->dbCache->afterInsert($data, $options);
        parent::_after_insert($data, $options);
    }

    /**
     *
     * @param $data
     * @param $options
     * User: hjun
     * Date: 2018-05-24 17:43:05
     * Update: 2018-05-24 17:43:05
     * Version: 1.00
     */
    public function _after_update($data, $options)
    {
        $this->dbCache->setModel($this);
        $this->dbCache->afterUpdate($data, $options);
        parent::_after_update($data, $options);
    }

    /**
     *
     * @param $data
     * @param $options
     * User: hjun
     * Date: 2018-05-24 17:43:09
     * Update: 2018-05-24 17:43:09
     * Version: 1.00
     */
    public function _after_delete($data, $options)
    {
        $this->dbCache->setModel($this);
        $this->dbCache->afterDelete($data, $options);
        parent::_after_delete($data, $options);
    }

    /**
     * 从请求参数中获取数据对象
     * @param array $fields 需要的字段值
     * @param array $request 请求参数
     * @param array $validate 自动验证规则
     * @param array $auto 自动完成规则
     * @param int $type
     * @return array ['code'=>200, 'msg'=>'', 'data'=>null]
     * User: hjun
     * Date: 2018-04-28 09:41:58
     * Update: 2018-04-28 09:41:58
     * Version: 1.00
     */
    public function getAndValidateDataFromRequest($fields = [], $request = [], $validate = [], $auto = [], $type = self::MODEL_BOTH)
    {
        // 先手动进行自动验证 和 自动完成, 防止两者冲突,  验证前先清空错误信息
        $this->error = '';
        if (!$this->validate($validate)->autoValidation($request, $type)) {
            return getReturn(CODE_ERROR, $this->getError());
        }
        $this->auto($auto)->autoOperation($request, $type);

        if (!empty($fields)) {
            if (is_array($fields)) {
                $fields = implode(',', $fields);
            }
            $this->field($fields);
        }
        $data = $this->create($request, $type);
        if (false === $data) {
            return getReturn(CODE_ERROR, $this->getError(), $request);
        }
        foreach ($data as $key => $value) {
            if (is_null($value)) {
                $data[$key] = '';
            }
        }
        return getReturn(CODE_SUCCESS, 'success', $data);
    }

    /**
     * 自动表单处理
     * @access public
     * @param array $data 创建数据
     * @param string $type 创建类型
     * @return mixed
     */
    public function autoOperation(&$data, $type)
    {
        if (!empty($this->options['auto'])) {
            $_auto = $this->options['auto'];
            unset($this->options['auto']);
        } elseif (!empty($this->_auto)) {
            $_auto = $this->_auto;
        }
        // 自动填充
        if (isset($_auto)) {
            foreach ($_auto as $auto) {
                // 填充因子定义格式
                // array('field','填充内容','填充条件','附加规则',[额外参数])
                if (empty($auto[2])) $auto[2] = self::MODEL_INSERT; // 默认为新增的时候自动填充
                if ($type == $auto[2] || $auto[2] == self::MODEL_BOTH) {
                    if (empty($auto[3])) $auto[3] = 'string';
                    switch (trim($auto[3])) {
                        case 'function':    //  使用函数进行填充 字段的值作为参数
                        case 'callback': // 使用回调方法
                            $args = isset($auto[4]) ? (array)$auto[4] : array();
                            if (isset($data[$auto[0]])) {
                                // 自动完成时我希望参数由自己定义,所以注释了这里,否则会自动传入数组中存在的数据
                                // array_unshift($args,$data[$auto[0]]);
                            }
                            if ('function' == $auto[3]) {
                                $data[$auto[0]] = call_user_func_array($auto[1], $args);
                            } else {
                                $data[$auto[0]] = call_user_func_array(array(&$this, $auto[1]), $args);
                            }
                            break;
                        case 'field':    // 用其它字段的值进行填充
                            $data[$auto[0]] = $data[$auto[1]];
                            break;
                        case 'ignore': // 为空忽略
                            if ($auto[1] === $data[$auto[0]])
                                unset($data[$auto[0]]);
                            break;
                        case 'string':
                        default: // 默认作为字符串填充
                            $data[$auto[0]] = $auto[1];
                    }
                    if (isset($data[$auto[0]]) && false === $data[$auto[0]]) unset($data[$auto[0]]);
                }
            }
        }
        return $data;
    }

    /**
     * 执行查询参数的连贯操作
     * @param array $options
     * where 用于查询或者更新条件的定义 字符串、数组和对象
     * table 用于定义要操作的数据表名称 字符串和数组
     * alias 用于给当前数据表定义别名 字符串
     * field 用于定义要查询的字段（支持字段排除） 字符串和数组
     * order 用于对结果排序 字符串和数组
     * group 用于对查询的group支持 字符串
     * having 用于对查询的having支持 字符串
     * join 用于对查询的join支持 字符串和数组
     * union 用于对查询的union支持 字符串、数组和对象
     * distinct 用于查询的distinct支持 布尔值
     * lock 用于数据库的锁机制 布尔值
     * cache 用于查询缓存 支持多个参数
     * relation 用于关联查询（需要关联模型支持） 字符串
     * result 用于返回数据转换 字符串
     * scope 用于命名范围 字符串、数组
     * bind 用于数据绑定操作 数组
     * comment 用于SQL注释 字符串
     * fetchSql 不执行SQL而只是返回SQL 布尔值
     * @return $this
     * User: hj
     * Date: 2017-10-28 02:45:32
     * Desc:
     * Update: 2017-10-28 02:45:33
     * Version: 1.0
     */
    public function exeQuery($options = [])
    {
        // 支持的连贯操作
        $exeArr = [
            'where', 'table', 'alias', 'field', 'order', 'group', 'having', 'join', 'distinct',
            'lock', 'cache', 'relation', 'result', 'scope', 'bind', 'comment', 'fetchSql'
        ];
        // 执行查询
        foreach ($options as $key => $value) {
            switch ($key) {
                case 'cache':
                    if (!empty($value)) call_user_func_array(array(&$this, 'cache'), $value);
                    break;
                default:
                    // 允许的操作执行
                    if (!empty($value) && in_array($key, $exeArr)) $this->$key($value);
                    break;
            }
        }
        return $this;
    }

    /**
     * 执行数据创建
     * @param array $options
     * @return $this
     * User: hjun
     * Date: 2018-01-08 22:43:26
     * Update: 2018-01-08 22:43:26
     * Version: 1.00
     */
    public function exeCreate($options = [])
    {
        $exeArr = array('field', 'validate', 'auto', 'token');
        foreach ($options as $key => $value) {
            if (!empty($value) && in_array($key, $exeArr)) $this->$key($value);
        }
        return $this;
    }

    /**
     * 执行更新的连贯操作
     * @param array $options
     * 支持的连贯操作
     * where 用于查询或者更新条件的定义 字符串、数组和对象
     * table 用于定义要操作的数据表名称 字符串和数组
     * alias 用于给当前数据表定义别名 字符串
     * field 用于定义允许更新的字段 字符串和数组
     * order 用于对数据排序 字符串和数组
     * lock 用于数据库的锁机制 布尔值
     * relation 用于关联更新（需要关联模型支持） 字符串
     * scope 用于命名范围 字符串、数组
     * bind 用于数据绑定操作 数组
     * comment 用于SQL注释 字符串
     * fetchSql 不执行SQL而只是返回SQL 布尔值
     * @return $this
     * User: hj
     * Desc:
     * Date: 2017-11-02 16:31:05
     * Update: 2017-11-02 16:31:06
     * Version: 1.0
     */
    public function exeSave($options = [])
    {
        $exeArr = ['where', 'table', 'alias', 'field', 'order', 'lock', 'relation', 'scope', 'bind', 'comment', 'fetchSql'];
        foreach ($options as $key => $value) {
            if (!empty($value) && in_array($key, $exeArr)) $this->$key($value);
        }
        return $this;
    }

    /**
     * 执行写入的连贯操作
     * @param array $options
     * table 用于定义要操作的数据表名称 字符串和数组
     * data 用于指定要写入的数据对象 数组和对象
     * field 用于定义要写入的字段 字符串和数组
     * relation 用于关联查询（需要关联模型支持） 字符串
     * validate 用于数据自动验证 数组
     * auto 用于数据自动完成 数组
     * filter 用于数据过滤 字符串
     * scope 用于命名范围 字符串、数组
     * bind 用于数据绑定操作 数组
     * token 用于令牌验证 布尔值
     * comment 用于SQL注释 字符串
     * fetchSql 不执行SQL而只是返回SQL 布尔值
     * @return $this
     * User: hj
     * Desc:
     * Date: 2017-11-02 16:32:01
     * Update: 2017-11-02 16:32:04
     * Version: 1.0
     */
    public function exeAdd($options = [])
    {
        $exeArr = ['table', 'data', 'field', 'relation', 'validate', 'auto', 'filter', 'scope', 'bind', 'token', 'comment', 'fetchSql'];
        foreach ($options as $key => $value) {
            if (!empty($value) && in_array($key, $exeArr)) $this->$key($value);
        }
        return $this;
    }

    /**
     * 执行删除的连贯操作
     * @param array $options
     * where 用于查询或者更新条件的定义 字符串、数组和对象
     * table 用于定义要操作的数据表名称 字符串和数组
     * alias 用于给当前数据表定义别名 字符串
     * order 用于对数据排序 字符串和数组
     * lock 用于数据库的锁机制 布尔值
     * relation 用于关联删除（需要关联模型支持） 字符串
     * scope 用于命名范围 字符串、数组
     * bind 用于数据绑定操作 数组
     * comment 用于SQL注释 字符串
     * fetchSql 不执行SQL而只是返回SQL 布尔值
     * @return $this
     * User: hj
     * Desc:
     * Date: 2017-11-02 17:10:00
     * Update: 2017-11-02 17:10:01
     * Version: 1.0
     */
    public function exeDel($options = [])
    {
        $exeArr = ['where', 'table', 'alias', 'order', 'lock', 'relation', 'scope', 'bind', 'comment', 'fetchSql'];
        foreach ($options as $key => $value) {
            if (!empty($value) && in_array($key, $exeArr)) $this->$key($value);
        }
        return $this;
    }

    /**
     * 解析分页参数 计算 获取limit连贯操作的两个参数
     * @param array $options \
     * - skip 跳过多少条记录
     * - take 取多少条记录
     * - page 页数
     * - limit 取多少条
     * @return array ['code'=>200,'msg'=>'','data'=>[]]
     * User: hj
     * Date: 2017-10-28 11:20:58
     * Desc:
     * Update: 2017-10-28 11:20:59
     * Version: 1.0
     */
    public function parsePage($options = [])
    {
        // take和limit的值统一 如果take设置了就取take 没设置取limit -1表示取所有 mysql新版不支持 这里设置最大值
        if (isset($options['take']) && $options['take'] > 0) {
            $options['limit'] = $options['take'];
        } elseif (isset($options['limit']) && $options['limit'] > 0) {
            $options['take'] = $options['limit'];
        } elseif (
            (
                (isset($options['take']) && $options['take'] < 0) ||
                (isset($options['limit']) && $options['limit'] < 0)
            ) &&
            (isset($options['skip']) && $options['skip'] > 0)
        ) {
            $options['take'] = $options['limit'] = self::MAX_TAKE;
        } else {
            $options['take'] = 0;
            $options['limit'] = 0;
        }

        // skip 和 page
        if (isset($options['skip']) && isset($options['take']) && $options['skip'] > 0 && $options['take'] == 0) {
            $options['take'] = $options['limit'] = self::MAX_TAKE;
        } elseif (isset($options['page']) && isset($options['take']) && $options['page'] > 0 && $options['take'] == 0) {
            // 默认取20条
            $options['take'] = 0;
            $options['limit'] = 0;
            $options['skip'] = ($options['page'] - 1) * $options['take'];
        } elseif (isset($options['page']) && isset($options['take']) && $options['page'] > 0 && $options['take'] > 0) {
            $options['skip'] = ($options['page'] - 1) * $options['take'];
        } else {
            $options['skip'] = (isset($options['skip']) && $options['skip'] > 0) ? $options['skip'] : 0;
        }
        return $options;
    }

    /**
     * 从缓存中获取上次查询的Admin数据
     * @return array
     * User: hjun
     * Date: 2019-01-13 16:56:49
     * Update: 2019-01-13 16:56:49
     * Version: 1.00
     */
    public function getAdminFromCache()
    {
        $admin = $this->getAdmin();
        $info = $this->getLastQueryData("admin_{$admin['id']}");
        if (empty($info)) {
            $info = D('Admin')->find($admin['id']);
            $this->setLastQueryData("admin_{$admin['id']}", $info);
        }
        return $info;
    }

    /**
     * 从缓存中获取企业信息
     * @param int $companyId
     * @return array
     * User: hjun
     * Date: 2019-01-13 15:31:15
     * Update: 2019-01-13 15:31:15
     * Version: 1.00
     */
    public function getCompanyFromCache($companyId = 0)
    {
        $info = $this->getLastQueryData("company_{$companyId}");
        if (empty($info)) {
            $info = D('Company')->getCompany($companyId);
            $this->setLastQueryData("company_{$companyId}", $info);
        }
        return $info;
    }

    /**
     * 验证是否已经有企业数据了
     * @return boolean
     * User: hjun
     * Date: 2019-01-13 16:54:12
     * Update: 2019-01-13 16:54:12
     * Version: 1.00
     */
    public function validateHasCompany()
    {
        $admin = $this->getAdminFromCache();
        return !empty($admin['company_id']);
    }

    /**
     * 自动完成企业ID
     * @return int
     * User: hjun
     * Date: 2019-01-14 00:49:12
     * Update: 2019-01-14 00:49:12
     * Version: 1.00
     */
    public function autoCompanyId()
    {
        $admin = $this->getAdmin();
        $company = $this->getCompanyFromCache($admin['company_id']);
        if (empty($company)) {
            return 0;
        }
        return $company['company_id'];
    }

    /**
     * 自动完成企业名称
     * @return string
     * User: hjun
     * Date: 2019-01-14 00:49:39
     * Update: 2019-01-14 00:49:39
     * Version: 1.00
     */
    public function autoCompanyName()
    {
        $admin = $this->getAdmin();
        $company = $this->getCompanyFromCache($admin['company_id']);
        if (empty($company)) {
            return '';
        }
        return $company['company_name'];
    }

    /**
     * 判断是否认证
     * @param array $company
     * @return boolean
     * User: hjun
     * Date: 2019-01-13 17:12:18
     * Update: 2019-01-13 17:12:18
     * Version: 1.00
     */
    public function isAuth($company = [])
    {
        if ($company['auth_status'] == COMPANY_AUTH_STATUS_YES) {
            return true;
        }
        return false;
    }

    /**
     * 判断是否提交了认证申请
     * @param array $company
     * @return boolean
     * User: hjun
     * Date: 2019-01-13 17:13:39
     * Update: 2019-01-13 17:13:39
     * Version: 1.00
     */
    public function isCompanyApply($company = [])
    {
        if ($company['company_apply_status'] == COMPANY_APPLY_STATUS_YES) {
            return true;
        }
        return false;
    }

    /**
     * 判断是否审核中
     * @param array $company
     * @return boolean
     * User: hjun
     * Date: 2019-01-13 17:13:08
     * Update: 2019-01-13 17:13:08
     * Version: 1.00
     */
    public function isAuditing($company = [])
    {
        if ($this->isCompanyApply($company)) {
            if ($company['audit_status'] == COMPANY_AUDIT_STATUS_WAIT) {
                return true;
            }
        }
        return false;
    }

    /**
     * 判断是否拒绝了认证申请
     * @param array $company
     * @return boolean
     * User: hjun
     * Date: 2019-01-13 17:23:09
     * Update: 2019-01-13 17:23:09
     * Version: 1.00
     */
    public function isRefuseAudit($company = [])
    {
        if ($this->isCompanyApply($company)) {
            if ($company['audit_status'] == COMPANY_AUDIT_STATUS_REFUSE) {
                return true;
            }
        }
        return false;
    }

    /**
     * 判断企业是否提交了数据申报
     * @param array $company
     * @return boolean
     * User: hjun
     * Date: 2019-01-14 00:18:57
     * Update: 2019-01-14 00:18:57
     * Version: 1.00
     */
    public function isDataApply($company = [])
    {
        if ($company['data_apply_status'] == COMPANY_DATA_APPLY_STATUS_YES) {
            return true;
        }
        return false;
    }

    /**
     * 判断企业是否提交了2018数据申报
     * @param array $company
     * @return boolean
     * User: hjun
     * Date: 2019-01-14 00:18:57
     * Update: 2019-01-14 00:18:57
     * Version: 1.00
     */
    public function is2018DataApply($company = [])
    {
        if ($company['data_apply_2018_status'] == COMPANY_DATA_APPLY_STATUS_YES) {
            return true;
        }
        return false;
    }

    /**
     * 获取错误提示
     * @param array $errors
     * @return string
     * User: hjun
     * Date: 2019-01-16 03:01:51
     * Update: 2019-01-16 03:01:51
     * Version: 1.00
     */
    public function getJsonSchemaError($errors = [])
    {
        $message = "{$errors[0]['property']}:{$errors[0]['message']}";
        $jsonValidate = $this->getJsonValidate();
        if (isset($jsonValidate[$errors[0]['customer_property']])) {
            return $jsonValidate[$errors['0']['customer_property']][0];
        }
        return $message;
    }

    /**
     * 检查JSON格式
     * @param array $data
     * @param array $schema
     * @return boolean
     * User: hjun
     * Date: 2019-01-16 02:59:45
     * Update: 2019-01-16 02:59:45
     * Version: 1.00
     */
    public function checkJsonSchema($data = [], $schema = [])
    {
        $validate = new Validator();
        $validate->check($data, $schema);
        if (!$validate->isValid()) {
            $errors = $validate->getErrors();
            $msg = $this->getJsonSchemaError($errors);
            $this->setValidateError($msg);
            return false;
        }
        return true;
    }

    public function __destruct()
    {
        G('b');
    }

}