<?php

return array(

    //配置RBAC权限
    //'RBAC_SUPERADMIN' => 'mengj',		//超级管理员名称(非系统带),
    'ADMIN_AUTH_KEY' => 'yang_adm_superadmin',    //超级管理员识别
    'USER_AUTH_ON' => true,            //开启验证
    'USER_AUTH_TYPE' => 1,                //验证类型(1:登录验证,2:实时验证[每步操作都去读数据库])
    'USER_AUTH_KEY' => 'yang_adm_uid',            //用户认证识别号
    'NOT_AUTH_MODULE' => 'Index,Public,Test',            //无需认证的模块(控制器)
    //退出不需要验证//安装的时候表前缀一定要更改(yy_)//debug
    'NOT_AUTH_ACTION' => 'logout',        //无需认证的动作方法
    'RBAC_ROLE_TABLE' => C('DB_PREFIX') . 'role',        //数据库角色表名称
    'RBAC_USER_TABLE' => C('DB_PREFIX') . 'role_user',//角色与用户的中间表名,不是用户表名
    'RBAC_ACCESS_TABLE' => C('DB_PREFIX') . 'access',    //权限表名
    'RBAC_NODE_TABLE' => C('DB_PREFIX') . 'node',    //节点表名称

    //'VAR_SESSION_ID' => 'PHPSESSID',//post方式 提交 session_id//Public uploadFile
    'TMPL_TEMPLATE_SUFFIX' => '.html',//模板后缀

    //去掉伪静态后缀
    'URL_HTML_SUFFIX' => '',

    //禁止静态缓存
    'HTML_CACHE_ON' => false,

    'TMPL_PARSE_STRING' => array(
        '__PUBLIC__' => __ROOT__ . '/' . APP_PATH_NAME . '/' . MODULE_NAME . '/View/Public',
        '__DATA__' => __ROOT__ . '/Data',
        '__ADMIN__' => __ROOT__ . '/Data/admin',
        '__VUE__' => VUE,
        '__APP_UTIL__' => APP_UTIL,
        '__CITY_JS__' => CITY_JS,
    ),

    /* SESSION设置 */
    'SESSION_PREFIX' => 'SUPER_', // session 前缀

    /* Cookie设置 */
    'COOKIE_PREFIX' => 'SUPER_', // Cookie前缀 避免冲突

    'URL_MODEL' => 3, //URL模式
    // 开启路由
    'URL_ROUTER_ON' => true,
    // 路由定义
    'URL_ROUTE_RULES' => array(
        // 登录
        'loginUp' => 'Login/login',
    ),
);


?>