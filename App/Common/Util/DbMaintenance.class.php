<?php

namespace Common\Util;

use Think\Upload;

/**
 * 数据库维护类
 * Class DbMaintenance
 * @package Common\Util
 * User: hjun
 * Date: 2018-07-16 15:39:33
 * Update: 2018-07-16 15:39:33
 * Version: 1.00
 */
class DbMaintenance extends Base
{
    // 要操作的表
    private $tables = [
        'building' => '楼宇数据',
        'floor' => '楼层数据',
        'room' => '房间数据',
        'contract' => '合同数据',
        'bill' => '账单数据',
        'public_bill' => '账单数据',
        'bill_log' => '账单数据',
        'trade_detail' => '账单数据',
        'garden_member' => '会员数据',
        'door_lock' => '智能门锁数据',
        'parking_car' => '智能停车数据',
        'power_meter' => '智能电表数据',
        'service_provider' => '园区服务数据',
        'service_apply' => '园区服务数据',
        'service_type' => '园区服务数据',
        'service_apply_log' => '园区服务数据',
        'customer' => '客户数据',
        'customer_log' => '客户数据',
    ];

    // 备份表的筛选条件
    private $where = [];

    // 错误信息
    private $error = '';

    // SQL集合
    private $sqlSet;

    private $id;

    public function __construct()
    {
        $this->id = session_id();
    }

    /**
     * @return array
     */
    public function getTables()
    {
        return $this->tables;
    }

    /**
     * @param array $tables
     */
    public function setTables($tables)
    {
        $this->tables = $tables;
    }

    /**
     * @return array
     */
    public function getWhere()
    {
        return $this->where;
    }

    /**
     * @param array $where
     */
    public function setWhere($where)
    {
        $this->where = $where;
    }

    /**
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @param string $error
     */
    public function setError($error)
    {
        $this->error = $error;
    }

    /**
     * @return mixed
     */
    public function getSqlSet()
    {
        return $this->sqlSet;
    }

    /**
     * @param mixed $sqlSet
     */
    public function setSqlSet($sqlSet)
    {
        $this->sqlSet = $sqlSet;
    }

    /**
     * 下载sql文件
     * @param $sql
     * User: hjun
     * Date: 2018-07-16 19:18:10
     * Update: 2018-07-16 19:18:10
     * Version: 1.00
     */
    public function exportSql($sql)
    {
        header("Content-type:application/octet-stream");
        header("Accept-Ranges:bytes");
        header("Content-Disposition:attachment;filename=" . date('YmdHi') . ".sql");
        header("Expires:0");
        header("Cache-Control:must-revalidate,post-check=0,pre-check=0");
        header("Pragma:public");
        echo "{$sql}";
        exit;
    }

    /**
     * 导入SQL文件
     * @param string $hyphen 连接符号
     * @return string 返回文件路径
     * User: hjun
     * Date: 2018-07-17 11:33:21
     * Update: 2018-07-17 11:33:21
     * Version: 1.00
     */
    public function importSql($hyphen)
    {
        $upload = new Upload();
        $upload->rootPath = realpath(THINK_PATH . '../back/') . '/';
        $where = $this->getWhere();
        $gardenId = $where['garden_id'] ? $where['garden_id'] : 0;
        $upload->savePath = "{$gardenId}/";
        $info = $upload->upload();
        if (false === $info) {
            $this->setError($upload->getError());
            return false;
        }
        $path = "{$upload->rootPath}{$info['file']['savepath']}{$info['file']['savename']}";
        $content = file_get_contents($path);
        $sql = explode($hyphen, $content);
        $this->setSqlSet($sql);
        return $path;
    }

    /**
     * SQL语句中的insert into 加上 ignore 关键字
     * @param $sql
     * @return string
     * Date: 2018-07-18 18:02:05
     * Update: 2018-07-18 18:02:05
     * Version: 1.00
     */
    public function insertIntoToIgnore($sql)
    {
        return str_replace('INSERT INTO', 'INSERT IGNORE INTO', $sql);
    }

    /**
     * 数据恢复前对sql处理
     * @param $sql
     * @return string
     * User: hjun
     * Date: 2018-07-18 18:12:38
     * Update: 2018-07-18 18:12:38
     * Version: 1.00
     */
    public function beforeRec(&$sql)
    {
        $sql = $this->insertIntoToIgnore($sql);
        return $sql;
    }

    /**
     * 数据恢复后
     * User: hjun
     * Date: 2018-07-18 18:14:25
     * Update: 2018-07-18 18:14:25
     * Version: 1.00
     */
    public function afterRec()
    {
        $dbCache = new DbCache();
        $dbCache->clear();
    }

    /**
     * 备份数据表
     * @return mixed
     * User: hjun
     * Date: 2018-07-16 15:36:45
     * Update: 2018-07-16 15:36:45
     * Version: 1.00
     */
    public function backup()
    {
        set_time_limit(0);
        if (!empty($this->tables)) {
            $id = $this->id;
            F("backup_{$id}", '');
            $sql = [];
            foreach ($this->tables as $table => $name) {
                $model = M($table);
                // 查询出需要备份的数据
                $data = $model->field(true)->where($this->where)->select();
                // hjun 2018-07-26 16:54:56 防止null值导致缺少列
                foreach ($data as $key => $item) {
                    foreach ($item as $k => $value) {
                        if (is_null($value)) {
                            $data[$key][$k] = '';
                        }
                    }
                }
                if (!empty($data)) {
                    // 根据数据生成相应的SQL语句
                    $sql[] = $model->fetchSql(true)->addAll($data);
                }
                F("backup_{$id}", "进度：" . round((count($sql) / count($this->tables)) * 100, 2) . '%');
            }
            F("backup_{$id}", null);
            return $sql;
        }
        return false;
    }

    /**
     * 数据删除
     * @param  boolean $needTrans
     * @return boolean
     * User: hjun
     * Date: 2018-07-16 15:40:01
     * Update: 2018-07-16 15:40:01
     * Version: 1.00
     */
    public function delete($needTrans = true)
    {
        if (!empty($this->tables)) {
            $model = M('');
            $needTrans && $model->startTrans();
            $id = $this->id;
            F("delete_{$id}", '');
            $i = 0;
            foreach ($this->tables as $table => $name) {
                $result = M($table)->where($this->where)->delete();
                $i++;
                F("delete_{$id}", "正在删除{$name}, 进度：" . round(($i / count($this->tables)) * 100, 2) . '%');
                if (false === $result) {
                    F("delete_{$id}", null);
                    $needTrans && $model->rollback();
                    $this->setError("删除{$name}时出错");
                    return false;
                }
            }
            F("delete_{$id}", null);
            $needTrans && $model->commit();
            return true;
        }
        return false;
    }

    /**
     * 全量恢复
     * @return boolean
     * User: hjun
     * Date: 2018-07-16 15:42:26
     * Update: 2018-07-16 15:42:26
     * Version: 1.00
     */
    public function fullRecovery()
    {
        if (!empty($this->tables) && !empty($this->sqlSet)) {
            // 执行备份的SQL语句
            $model = M('');
            $model->startTrans();
            // 全量恢复前先删除
            F("fullrec_{$this->id}", "正在进行数据覆盖...");
            $result = $this->delete(false);
            if (false === $result) {
                $model->rollback();
                return false;
            }
            foreach ($this->sqlSet as $key => $sql) {
                if (!empty($sql)) {
                    $this->beforeRec($sql);
                    $result = $model->execute($sql);
                } else {
                    $result = true;
                }
                F("fullrec_{$this->id}", "进度：" . round((($key + 1) / count($this->sqlSet)) * 100, 2) . '%');
                if (false === $result) {
                    logWrite('执行全量恢复出错：' . $model->getError() . '-' . $model->getDbError());
                    $this->setError('系统繁忙,请稍候重试...');
                    $model->rollback();
                    return false;
                }
            }
            F("fullrec_{$this->id}", null);
            $model->commit();
            $this->afterRec();
            return true;
        }
        return false;
    }

    /**
     * 增量恢复
     * @return boolean
     * User: hjun
     * Date: 2018-07-16 15:42:22
     * Update: 2018-07-16 15:42:22
     * Version: 1.00
     */
    public function incRecovery()
    {
        if (!empty($this->tables) && !empty($this->sqlSet)) {
            // 执行备份的SQL语句
            $model = M('');
            $model->startTrans();
            F("increc_{$this->id}", '');
            foreach ($this->sqlSet as $key => $sql) {
                if (!empty($sql)) {
                    $this->beforeRec($sql);
                    $result = $model->execute($sql);
                } else {
                    $result = true;
                }
                F("increc_{$this->id}", "进度：" . round((($key + 1) / count($this->sqlSet)) * 100, 2) . '%');
                if (false === $result) {
                    logWrite('执行增量恢复出错：' . $model->getError() . '-' . $model->getDbError());
                    $this->setError('系统繁忙,请稍候重试...');
                    $model->rollback();
                    return false;
                }
            }
            F("increc_{$this->id}", null);
            $model->commit();
            $this->afterRec();
            return true;
        }
        return false;
    }
}