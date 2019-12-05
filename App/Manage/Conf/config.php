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
        '__MOMENT__' => __ROOT__ . '/Data/static/jq_plugins/moment/moment.js?v=' . EXTRA_VERSION
    ),

    /* SESSION设置 */
    'SESSION_PREFIX' => 'MANAGE_', // session 前缀

    /* Cookie设置 */
    'COOKIE_PREFIX' => 'MANAGE_', // Cookie前缀 避免冲突

    'URL_MODEL' => 3, //URL模式
    // 开启路由
    'URL_ROUTER_ON' => true,
    // 路由定义
    'URL_ROUTE_RULES' => array(
        // 登录
        'loginUp' => 'Login/login',

        // 共用
        'getSelectData' => ['Public/getSelectData', ''],
        'getTodoQnr' => ['Public/getTodoQnr', ''],
        'syncAuth' => ['Public/syncAuth', ''], // 更新权限
        'getIndexData' => ['Public/getIndexData', ''], // 获取管理员首页的数据

        // 其他
        'register' => 'Login/register', // 注册

        // region Company
        'saveCompany' => ['Company/companyInfo', 'action=save'], // 保存企业信息
        'getCompany' => ['Company/companyInfo', 'action=get'], // 获取企业信息
        'companyApply' => ['Company/companyApply', ''], // 提交企业认证申请
        'getDataApplyList' => ['Company/dataApplyList', 'action=query'], // 获取数据申报列表
        'exportDataApplyList' => ['Company/dataApplyList', 'action=export'], // 导出数据申报列表
        'getDataApply' => ['Company/dataApply', 'action=get'], // 获取数据申报数据
        'addDataApply' => ['Company/dataApply', 'action=add'], // 新增数据申报
        'saveDataApply' => ['Company/dataApply', 'action=save'], // 保存数据申报
        'getOldDataApply' => ['Company/dataApply2018', 'action=get'], // 获取2018数据申报数据
        'addOldDataApply' => ['Company/dataApply2018', 'action=add'], // 新增2018数据申报
        'saveOldDataApply' => ['Company/dataApply2018', 'action=save'], // 保存2018数据申报
        'getMyQnrList' => ['Company/myQnr', 'action=query'], // 获取我的问卷列表
        'getMyQnr' => ['Company/myQnr', 'action=get'], // 获取我的问卷数据
        'addAnswerQnr' => ['Company/myQnr', 'action=add'], // 新增回答
        'updateAnswerQnr' => ['Company/myQnr', 'action=save'], // 新增回答
        'syncQnr' => ['Company/myQnr', 'action=sync'], // 同步问卷获取最新数据
        // endregion

        // region CompanyMange
        'getAuditList' => ['CompanyManage/auditList', 'action=query'], // 获取企业认证申请列表
        'exportAuditList' => ['CompanyManage/auditList', 'action=export'], // 获取企业认证申请列表
        'passCompanyApply' => ['CompanyManage/auditCompany', 'action=pass'], // 通过企业认证申请
        'refuseCompanyApply' => ['CompanyManage/auditCompany', 'action=refuse'], // 拒绝企业认证申请
        'getCompanyDetail' => ['CompanyManage/companyInfo', 'action=get'], // 获取企业详细数据
        'sendEmail' => ['CompanyManage/sendEmail', ''], // 发送邮件
        // endregion

        // region DataApply
        'getMangeDataApplyList' => ['DataApply/applyList', 'action=query'], // 管理员获取申报列表
        'exportManageDataApplyList' => ['DataApply/applyList', 'action=export'], // 管理员导出申报列表
        'getManageDataApply' => ['DataApply/dataApply', 'action=get'], // 获取数据申报数据
        'passDataApply' => ['DataApply/audit', 'action=pass'], // 通过申报
        'refuseDataApply' => ['DataApply/audit', 'action=refuse'], // 拒绝申报
        'getOldMangeDataApplyList' => ['DataApply/applyList2018', 'action=query'], // 管理员获取2018申报列表
        'exportOldManageDataApplyList' => ['DataApply/applyList2018', 'action=export'], // 管理员导出2018申报列表
        'passOldDataApply' => ['DataApply/audit2018', 'action=pass'], // 通过2018申报
        'refuseOldDataApply' => ['DataApply/audit2018', 'action=refuse'], // 拒绝2018申报
        // endregion

        // region Question
        'getQnrList' => ['Question/qnrList', 'action=query'], // 获取问卷列表
        'getQnrAnswerList' => ['Question/qnrList', 'action=detail'], // 获取问卷回答列表
        'getAnswerDetail' => ['Question/qnrList', 'action=get'], // 获取回答详情
        'getQnr' => ['Question/updateQnr', 'action=get'], // 获取问卷数据
        'addQnr' => ['Question/addQnr', 'action=add'], // 新增问卷
        'updateQnr' => ['Question/updateQnr', 'action=save'], // 修改问卷
        'changeQnrOpen' => ['Question/updateQnr', 'action=changeOpen'], // 修改问卷的开启状态
        'changeQnrForce' => ['Question/updateQnr', 'action=changeForce'], // 修改问卷的开启状态
        // endregion

        // region Art
        'getArtList' => ['Art/index', 'action=query'], // 获取文章列表
        'getArt' => ['Art/update', 'action=get'], // 获取文章数据
        'addArt' => ['Art/add', 'action=add'], // 新增文章
        'updateArt' => ['Art/update', 'action=update'], // 修改文章
        'deleteArt' => ['Art/delete', 'action=delete'], // 删除文章
        'changeArtSort' => ['Art/update', 'action=changeSort'], // 修改文章排序
        'changeArtRecommend' => ['Art/update', 'action=changeRecommend'], // 修改文章推荐
        'changeArtShow' => ['Art/update', 'action=changeShow'], // 修改文章显示
        // endregion

        // region link
        'getLinkList' => ['Link/index', 'action=query'], // 获取友情链接列表
        'getLink' => ['Link/update', 'action=get'], // 获取友情链接数据
        'addLink' => ['Link/add', 'action=add'], // 新增友情链接
        'updateLink' => ['Link/update', 'action=update'], // 修改友情链接
        'deleteLink' => ['Link/delete', 'action=delete'], // 删除友情链接
        'changeLinkSort' => ['Link/update', 'action=changeSort'], // 修改友情链接排序
        'changeLinkShow' => ['Link/update', 'action=changeShow'], // 修改友情链接显示
        // endregion

        // region Expert
        'getExpertList' => ['Expert/index', 'action=query'], // 获取专家列表
        'getExpert' => ['Expert/update', 'action=get'], // 获取专家数据
        'addExpert' => ['Expert/add', 'action=add'], // 新增专家
        'updateExpert' => ['Expert/update', 'action=update'], // 修改专家
        'deleteExpert' => ['Expert/delete', 'action=delete'], // 删除专家
        'changeExpertSort' => ['Expert/update', 'action=changeSort'], // 修改专家排序
        'changeExpertShow' => ['Expert/update', 'action=changeShow'], // 修改专家显示
        // endregion

        // region Member
        'getMemberList' => ['Member/index', 'action=query'], // 获取会员列表
        'getMember' => ['Member/update', 'action=get'], // 获取会员数据
        'addMember' => ['Member/add', 'action=add'], // 新增会员
        'updateMember' => ['Member/update', 'action=update'], // 修改会员
        'deleteMember' => ['Member/delete', 'action=delete'], // 删除会员
        'changeMemberSort' => ['Member/update', 'action=changeSort'], // 修改会员排序
        'changeMemberRecommend' => ['Member/update', 'action=changeRecommend'], // 修改会员推荐
        'changeMemberShow' => ['Member/update', 'action=changeShow'], // 修改会员显示
        // endregion

        // region MemberTitle
        'getMemberTitleList' => ['MemberTitle/index', 'action=query'], // 获取会员职务列表
        'getMemberTitle' => ['MemberTitle/update', 'action=get'], // 获取会员职务数据
        'addMemberTitle' => ['MemberTitle/add', 'action=add'], // 新增专家
        'updateMemberTitle' => ['MemberTitle/update', 'action=update'], // 修改会员职务
        'deleteMemberTitle' => ['MemberTitle/delete', 'action=delete'], // 删除会员职务
        'changeMemberTitleSort' => ['MemberTitle/update', 'action=changeSort'], // 修改会员职务排序
        'changeMemberTitleShow' => ['MemberTitle/update', 'action=changeShow'], // 修改会员职务显示
        // endregion

        // region DataAnalysis
        'getOutputData' => ['DataAnalysis/outputValueCount', 'action=get'], //获取产值分析汇总
        'exportOutputData' => ['DataAnalysis/outputValueCount', 'action=export'],  //导出产值分析汇总
        'getDevelopDetailData' => ['DataAnalysis/developDetail', 'action=get'], //获取研发情况汇总明细
        'exportDevelopDetailData' => ['DataAnalysis/developDetail', 'action=export'],  //导出研发情况汇总明细
        'getOutputValueDetailData' => ['DataAnalysis/outputValueDetail', 'action=get'], //产值分析明细
        'exportOutputValueDetailData' => ['DataAnalysis/outputValueDetail', 'action=export'],  //产值分析明细
        // endregion

        // region Nav
        'navAdd' => ['Nav/add', ''], //添加顶部标题栏
        'navUpdate' => ['Nav/update', ''], //修改顶部标题栏
        'navDelete' => ['Nav/delete', ''], //删除顶部标题栏
        'getNavData' => ['Nav/index', 'action=query'], //获取标题栏
        'getParentNav' => ['Nav/getParentNav', ''], //获取父类导航栏
        // endregion

        // region Ad
        'getAdData' => ['Advertisement/index', 'action=query'],
        'addAd' => ['Advertisement/addAd', ''],
        'updateAd' => ['Advertisement/updateAd', ''],
        'deleteAd' => ['Advertisement/deleteAd', ''],
        // endregion


    ),
);


?>