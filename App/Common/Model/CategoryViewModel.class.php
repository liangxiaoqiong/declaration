<?php
/**
 * 视图模型
 * Class CategoryViewModel
 * @package Common\Model
 * User: hjun
 * Date: 2018-03-29 23:10:34
 * Update: 2018-03-29 23:10:34
 * Version: 1.00
 * company 厦门市虚拟与增强显示产业协会
 * Copyright  ©2018   All rights reserved
 */
namespace Common\Model;

//视图模型
class CategoryViewModel extends ExViewModel
{

    protected $viewFields = array(
        'category' => array('*', '_type' => 'LEFT'),
        'model'    => array(
            'name'      => 'modelname', //显示字段name as model
            'tablename' => 'tablename', //显示字段name as model
            '_on'       => 'category.modelid = model.id', //_on 对应上面LEFT关联条件
        ),

    );
}
