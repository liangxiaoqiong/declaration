<?php

$mode = defined('MODE') ? MODE : 'common';
return array(
    'DEFAULT_MODULE' => 'Manage', //默认分组

    //强制区分大小写
    'URL_CASE_INSENSITIVE' => false,

    'DEFAULT_FILTER' => 'trim', // 默认参数过滤方法 用于I函数...

    'SIGN_KEY' => 'WJD',

    /*杂项*/
    'DB_CACHE_MAP_TYPE' => 0, // 数据库缓存的映射的存储类型 0-使用数据库存 1-使用数组存

    'AUTOLOAD_NAMESPACE' => array(
        'PhpOffice' => LIB_PATH . 'PhpOffice',
        'JsonSchema' => VENDOR_PATH . 'JsonSchema',
    ),

    // =======================================    根据环境的而不同配置不同的配置    =======================================
    'LOAD_EXT_CONFIG' => "config.{$mode}", // 加载扩展配置文件
);