<?php
return array(
    'URL_MODEL' => 3, //URL模式
    // 开启路由
    'URL_ROUTER_ON' => true,
    // 路由定义
    'URL_ROUTE_RULES' => array(

        // region 文章相关
        'getArt' => ['Article/article', 'action=get'], // 获取谋篇文章内容
        'clickArt' => ['Article/article', 'action=click'], // 阅读文章
        // endregion

        // region 首页相关
        'getIndexData' => ['Index/getIndexData'], // 获取首页主页内容
        // endregion

        // region 其他
        'getNavArt' => ['Article/article', 'action=getNavArt'], // 根据导航ID获取一篇文章
        'queryNavArt' => ['Article/article', 'action=queryNavArt'], // 根据导航ID获取文章列表
        'getMemberTitleData' => ['Member/member', 'action=getMemberTitleData'], // 获取会员风采页面数据
        'getMember' => ['Member/member', 'action=get'], // 获取会员的数据
        // endregion
    ),

    'TMPL_PARSE_STRING' => array(
        '__PUBLIC__' => __ROOT__ . '/' . APP_PATH_NAME . '/' . MODULE_NAME . '/View/Public',
        '__DATA__' => __ROOT__ . '/Data',
        '__ADMIN__' => __ROOT__ . '/Data/admin',
        '__VUE__' => VUE,
        '__APP_UTIL__' => APP_UTIL,
        '__CITY_JS__' => CITY_JS,
        '__MOMENT__' => __ROOT__ . '/Data/static/jq_plugins/moment/moment.js?v=' . EXTRA_VERSION
    ),
);