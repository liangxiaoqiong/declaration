<?php

namespace Super\Controller;

class IteminfoController extends CommonController
{

    public function index()
    {

        $group = I('group', '', 'trim');
        if (empty($group)) {
            $this->error('参数不正确!');
        }

        $count = M('iteminfo')->where(array('group' => $group))->count();

        $page           = new \Common\Lib\PageAdmin($count, 10);
        $page->rollPage = 7;
        $page->setConfig('theme', '%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        $limit = $page->firstRow . ',' . $page->listRows;
        $list  = M('iteminfo')->where(array('group' => $group))->order('sort')->limit($limit)->select();

        $this->assign('page', $page->show());
        $this->assign('vlist', $list);
        $this->assign('group', $group);
        $this->assign('type', '联动信息列表');
        $this->display();
    }
    //添加
    public function add()
    {
        //当前控制器名称
        $actionName = strtolower(CONTROLLER_NAME);
        $group      = I('group', '', 'trim');

        if (IS_POST) {
            //M验证
            $data['name']  = I('name', '', 'trim');
            $data['value'] = I('value', 1, 'intval');
            $data['group'] = I('group', '', 'trim');
            $data['sort']  = I('sort', 0, 'intval');

            if (empty($data['name'])) {
                $this->error('名称不能为空');
            }
            if (empty($data['group'])) {
                $this->error('请选择分组！');
            }
            $vo = M('iteminfo')->where(array('group' => $data['group'], 'value' => $data['value']))->find();
            if ($vo) {
                $this->error('枚举值已经存在，请重新填写');
            }

            if ($id = M('iteminfo')->add($data)) {
                $this->success('添加成功', U('Iteminfo/index', array('group' => $data['group'])));
            } else {
                $this->error('添加失败');
            }
            exit();
        }

        $list = M('itemgroup')->select();
        $data = M('iteminfo')->where(array('group' => $group))->field('MAX(value) as maxv')->find();

        $this->assign('vlist', $list);
        $this->assign('maxValue', isset($data['maxv']) ? $data['maxv'] + 1 : 1);
        $this->assign('group', $group);
        $this->assign('type', '添加联动信息');
        $this->display();
    }

    //编辑
    public function edit()
    {
        //当前控制器名称
        $id         = I('id', 0, 'intval');
        $actionName = strtolower(CONTROLLER_NAME);
        if (IS_POST) {
            //M验证
            $data['id']    = I('id', 0, 'intval');
            $data['name']  = I('name', '', 'trim');
            $data['value'] = I('value', 1, 'intval');
            $data['group'] = I('group', '', 'trim');
            $data['sort']  = I('sort', 0, 'intval');

            if (empty($data['name'])) {
                $this->error('名称不能为空');
            }
            if (empty($data['group'])) {
                $this->error('请选择分组！');
            }
            $vo = M('iteminfo')->where(array('id' => array('neq', $data['id']), 'group' => $data['group'], 'value' => $data['value']))->find();
            if ($vo) {
                $this->error('枚举值已经存在，请重新填写');
            }

            if (false !== M('iteminfo')->save($data)) {
                $this->success('修改成功', U('Iteminfo/index', array('group' => $data['group'])));
            } else {

                $this->error('修改失败');
            }
            exit();
        }
        $group = I('group', '', 'trim');
        $list  = M('itemgroup')->select();
        $vo    = M($actionName)->find($id);

        $this->assign('vlist', $list);
        $this->assign('vo', $vo);
        $this->assign('group', $group);
        $this->assign('type', '修改联动信息');
        $this->display();
    }

    //批量更新排序
    public function sort()
    {
        $group = I('get.group', '');
        //exit();
        foreach ($_POST as $k => $v) {
            if ($k == 'key') {
                continue;
            }
            M('iteminfo')->where(array('id' => $k))->setField('sort', $v);
            //echo 'id:'.$k.'___v:'.$v.'<br/>';//debug
        }
        //$this->redirect('Iteminfo/index', array('group' => $group));
		$this->success('更新成功', U('Iteminfo/index', array('group' => $group)));
    }

    //彻底删除
    public function del()
    {

        $id        = I('id', 0, 'intval');
        $batchFlag = I('get.batchFlag', 0, 'intval');
        //批量删除
        if ($batchFlag) {
            $this->delBatch();
            return;
        }

        $group = I('group', '', 'trim');
        if (M('iteminfo')->delete($id)) {
            $this->success('彻底删除成功', U('Iteminfo/index', array('group' => $group)));
        } else {
            $this->error('彻底删除失败');
        }
    }

    //批量彻底删除
    public function delBatch()
    {

        $idArr = I('key', 0, 'intval');
        $group = I('get.group', '');
        if (!is_array($idArr)) {
            $this->error('请选择要彻底删除的项');
        }
        $where = array('id' => array('in', $idArr));

        if (M('iteminfo')->where($where)->delete()) {
            $this->success('彻底删除成功', U('Iteminfo/index', array('group' => $group)));
        } else {
            $this->error('彻底删除失败');
        }
    }

}
