<?php
/**
 * 系统管理
 * Class SystemController
 * @package Manage\Controller
 * User: hjun
 * Date: 2018-03-29 22:58:04
 * Update: 2018-03-29 22:58:04
 * Version: 1.00
 * company 厦门市虚拟与增强显示产业协会
 * Copyright  ©2018   All rights reserved
 */

namespace Manage\Controller;

use Common\Util\DbCache;

class SystemController extends CommonController
{

    public function index()
    {

        $groupid = I('groupid', 0, 'intval'); //类别ID
        $keyword = I('keyword', '', 'htmlspecialchars,trim'); //关键字

        $where = array('id' => array('GT', 0));
        if (!empty($groupid)) {
            $where['groupid'] = $groupid;
        }
        if (!empty($keyword)) {
            $where['name'] = array('LIKE', "%{$keyword}%");
        }

        $count = M("config")->where($where)->count();
        $page = new \Common\Lib\PageAdmin($count, 10);
        $page->rollPage = 7;
        $page->setConfig('theme', '%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        $limit = $page->firstRow . ',' . $page->listRows;
        $vlist = M("config")->where($where)->order('sort,id DESC')->limit($limit)->select();

        $this->assign('groupid', $groupid);
        $this->assign('keyword', $keyword);
        $this->assign('page', $page->show());
        $this->assign('vlist', $vlist);
        $this->assign('configgroup', get_item('configgroup'));
        $this->assign('type', '配置项管理');
        $this->display();
    }

    public function add()
    {

        if (IS_POST) {
            $data = I('post.');
            $data['groupid'] = I('groupid', 0, 'intval');
            $data['typeid'] = I('typeid', 0, 'intval');
            $data['sort'] = I('sort', 0, 'intval');
            $data['remark'] = I('remark', '', '');

            if (empty($data['name'])) {
                $this->error('请填写名称(标识)');
            }
            if (empty($data['title'])) {
                $this->error('请填写标题');
            }

            if (!preg_match('/^[a-zA-Z0-9_]+$/', $data['name'])) {
                $this->error('名称只能由字母、数字和"_"组成');
            }
            $data['name'] = strtoupper($data['name']);

            if (M('config')->where(array('name' => $data['name']))->find()) {
                $this->error('配置项名称(标识)已经存在，请更换');
            }

            if (M('config')->add($data)) {
                $this->success('添加成功', U('index'));
            } else {
                $this->error('添加失败');
            }

            exit();
        }

        $this->assign('configgroup', get_item('configgroup'));
        $this->assign('configtype', get_item('configtype'));
        $this->display();
    }

    public function edit()
    {
        $id = I('id', 0, 'intval');
        if (IS_POST) {
            $data = I('post.');
            $id = $data['id'] = I('id', 0, 'intval');
            $data['groupid'] = I('groupid', 0, 'intval');
            $data['typeid'] = I('typeid', 0, 'intval');
            $data['sort'] = I('sort', 0, 'intval');
            $data['remark'] = I('remark', '', '');

            if (empty($data['name'])) {
                $this->error('请填写名称(标识)');
            }
            if (empty($data['title'])) {
                $this->error('请填写标题');
            }

            if (!preg_match('/^[a-zA-Z0-9_]+$/', $data['name'])) {
                $this->error('名称只能由字母、数字和"_"组成');
            }
            $data['name'] = strtoupper($data['name']);

            if (M('config')->where(array('name' => $data['name'], 'id' => array('neq', $id)))->find()) {
                $this->error('配置项名称(标识)已经存在，请更换');
            }

            if (false !== M('config')->save($data)) {
                $this->success('修改成功', U('index'));
            } else {
                $this->error('修改失败');
            }

            exit();
        }
        $vo = M('config')->find($id);
        $vo['value'] = htmlspecialchars($vo['value']); //ueditor

        $this->assign('vo', $vo);
        $this->assign('configgroup', get_item('configgroup'));
        $this->assign('configtype', get_item('configtype'));
        $this->display();
    }

    //删除
    public function del()
    {

        $id = I('id', 0, 'intval');
        $groupid = I('groupid', 0, '');
        //批量删除
        if (empty($id)) {
            $this->error('参数错误!');
        }

        if (M('config')->delete($id)) {
            $this->success('删除成功', U('index', array('groupid' => $groupid)));

        } else {
            $this->error('删除失败');
        }
    }

    //批量更新排序
    public function sort()
    {
        $sortlist = I('sortlist', array(), 'intval');
        $groupid = I('groupid', 0, 'intval');
        foreach ($sortlist as $k => $v) {
            $data = array(
                'id' => $k,
                'sort' => $v,
            );
            M('config')->save($data);
        }
        $this->redirect('System/index', array('groupid' => $groupid));
    }

    public function site()
    {
        if (IS_POST) {

            $data = I('config', array(), 'trim');
            foreach ($data as $k => $v) {
                $ret = M('config')->where(array('name' => $k))->save(array('value' => $v));
            }
            if ($ret !== false) {
                S('config/site', null);
                $this->success('修改成功', U('System/site'));

            } else {

                $this->error('修改失败！');
            }

            exit();
        }
        $vlist = M("config")->order('groupid,sort')->select();
        if (!$vlist) {
            $vlist = array();
        }
        $configgroup = get_item('configgroup');
        // 只需要第一个站点设置
        foreach ($configgroup as $k => $group) {
            if ($group == '站点配置') {
                $configgroup = [
                    $k => $group
                ];
                break;
            }
        }
        $glist = array();
        $active = [
            'CFG_OFFICIAL_TITLE', // 官网标题
            'CFG_OFFICIAL_TITLE_EN', // 官网标题(英文)
            'CFG_MAIN_UNIT',  // 主办单位
            'CFG_DO_UNIT',  // 承办单位
            'CFG_TECHNICAL_SUPPORT', // 技术支持
            'CFG_PHONE', // 联系方式
            'CFG_FAX', // 传真
            'CFG_ADDRESS', // 地址
            'CFG_ZIP_CODE', // 邮编
            'CFG_PHONE', // 电话
            'CFG_WEBURL', // 网址
            'CFG_POWERBY', // 版权
            'CFG_BEIAN', // 备案号
        ];
        foreach ($configgroup as $k => $v) {
            $glist[$k] = array();
            foreach ($vlist as $k2 => $v2) {
                if ($k == $v2['groupid'] && in_array($v2['name'], $active)) {
                    $glist[$k][] = $v2;
                    //unset($vlist[$k2]);
                }

            }
        }

        $this->assign('vlist', $glist);
        $this->assign('configgroup', $configgroup);
        $this->assign('groupnum', count($configgroup));
        $this->assign('configtype', get_item('configtype'));
        $this->display();

    }

    public function url()
    {
        if (IS_POST) {
            $data = I('config', '', '');

            if ($data['HOME_URL_MODEL'] == 0 || $data['HOME_URL_MODEL'] == 3) {
                $data['HOME_URL_ROUTER_ON'] = 0;
            }
            $data['HOME_HTML_CACHE_ON'] = isset($data['HOME_HTML_CACHE_ON']) ? $data['HOME_HTML_CACHE_ON'] : 0;
            $data['MOBILE_HTML_CACHE_ON'] = isset($data['MOBILE_HTML_CACHE_ON']) ? $data['MOBILE_HTML_CACHE_ON'] : 0;
            $data['HTML_CACHE_INDEX_ON'] = isset($data['HTML_CACHE_INDEX_ON']) ? $data['HTML_CACHE_INDEX_ON'] : 0;
            $data['HTML_CACHE_LIST_ON'] = isset($data['HTML_CACHE_LIST_ON']) ? $data['HTML_CACHE_LIST_ON'] : 0;
            $data['HTML_CACHE_SHOW_ON'] = isset($data['HTML_CACHE_SHOW_ON']) ? $data['HTML_CACHE_SHOW_ON'] : 0;
            $data['HTML_CACHE_SPECIAL_ON'] = isset($data['HTML_CACHE_SPECIAL_ON']) ? $data['HTML_CACHE_SPECIAL_ON'] : 0;

            foreach ($data as $k => $v) {
                $ret = M('meta')->where(array('name' => $k))->save(array('value' => $v));
            }
            if ($ret !== false) {
                F('config/meta', null);
                $this->success('修改成功', U('System/url'));

            } else {

                $this->error('修改失败！');
            }

            exit();
        }

        $list = M('meta')->where(array('groupid' => 1))->select();
        $vo = array();
        foreach ($list as $k => $v) {
            $vo[$v['name']] = $v['value'];
        }

        $this->assign('vo', $vo);
        $this->display();
    }

    public function online()
    {
        if (IS_POST) {
            $data = I('post.', '');
            //$data['cfg_online_qq'] = str_replace(array("\r","\n"), array("","|||"), $data['cfg_online_qq']);
            //$data['cfg_online_wangwang'] = str_replace(array("\r","\n"), array("","|||"), $data['cfg_online_wangwang']);
            //$data['cfg_online_qq_param'] = I('cfg_online_qq_param', '', '');//html
            //$data['cfg_online_wangwang_param'] = I('cfg_online_wangwang_param', '', '');//html

            $data = I('config', '', '');
            foreach ($data as $k => $v) {
                $ret = M('meta')->where(array('name' => $k))->save(array('value' => $v));
            }
            if ($ret !== false) {
                F('config/meta', null);
                $this->success('修改成功', U('System/online'));

            } else {

                $this->error('修改失败！');
            }

            exit();

        }

        $list = M('meta')->where(array('groupid' => 9))->select();
        $vo = array();
        foreach ($list as $k => $v) {
            $vo[$v['name']] = $v['value'];
        }

        $onlineStyleList = get_file_folder_List('./Data/static/js_plugins/online/', 2, '*.css');
        $onlineStyleList = str_replace('.css', '', $onlineStyleList);

        $this->assign('vo', $vo);
        $this->assign('onlineStyleList', $onlineStyleList);
        $this->display();
    }

    public function update()
    {
        header("Content-Type:text/html; charset=utf-8"); //不然返回中文乱码
        //清除缓存
        $this->clearCache();
    }

    public function clearCache($dellog = false)
    {
        header("Content-Type:text/html; charset=utf-8"); //不然返回中文乱码

        //清除缓存
        is_dir(DATA_PATH . '_fields/') && del_dir_file(DATA_PATH . '_fields/', false);
        is_dir(CACHE_PATH) && del_dir_file(CACHE_PATH, false); //模板缓存（混编后的）
        is_dir(DATA_PATH) && del_dir_file(DATA_PATH, false); //项目数据（当使用快速缓存函数F的时候，缓存的数据）
        is_dir(TEMP_PATH) && del_dir_file(TEMP_PATH, false); //项目缓存（当S方法缓存类型为File的时候，这里每个文件存放的就是缓存的数据）
        if ($dellog) {
            is_dir(LOG_PATH) && del_dir_file(LOG_PATH, false); //日志
        }
        is_file(RUNTIME_PATH . APP_MODE . '~runtime.php') && @unlink(RUNTIME_PATH . APP_MODE . '~runtime.php'); //RUNTIME_FILE
        $dbCache = new DbCache();
        $dbCache->clear();
        $this->success('清除完成！');
        //echo '清除完成';
    }

    /**
     * 操作日志
     * User: hjun
     * Date: 2018-05-25 17:58:46
     * Update: 2018-05-25 17:58:46
     * Version: 1.00
     */
    public function logList()
    {
        if (IS_POST) {
            $where = [];
            $page = $this->req['page'] > 0 ? $this->req['page'] : 1;
            $limit = $this->req['limit'] > 0 ? $this->req['limit'] : 20;
            // 操作时间
            if (!empty($this->req['time_min']) || !empty($this->req['time_max'])) {
                $this->req['time_min'] = strtotime($this->req['time_min']);
                $this->req['time_max'] = strtotime($this->req['time_max']);
                $result = getRangeWhere($this->req, 'time_min', 'time_max', '开始时间不能大于结束时间');
                if (!isSuccess($result)) {
                    $this->apiResponse($result);
                }
                if (!empty($result['data'])) {
                    $where['create_time'] = $result['data'];
                }
            }


            // 操作者
            if (!empty($this->req['name'])) {
                $where['admin_name'] = ['LIKE', "%{$this->req['name']}%"];
            }

            $where['admin_type'] = $this->admin['usertype'];
            if ($this->admin['usertype'] == NORMAL_ADMIN) {
                $where['company_id'] = $this->admin['company_id'];
            }

            $data = D('AdminLog')->logList($page, $limit, $where);
            $this->apiResponse(getReturn(CODE_SUCCESS, 'success', $data));
        } else {
            $this->display();
        }
    }
}
