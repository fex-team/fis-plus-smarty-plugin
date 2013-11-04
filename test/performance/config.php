<?php
/** 根目录地址 */
if(!defined('DIFF_ROOT_PATH')) define('DIFF_ROOT_PATH', str_replace('\\', '/', dirname(__FILE__)) . '/');
/** 定义和产品线相关的产出目录 */
if(!defined('IKNOW_PATH')) define('IKNOW_PATH',DIFF_ROOT_PATH.'product_output/iknow/');

$config = array(
    'product'=>array(      //产品线目录
        'iknow'=>array(
            'name'=>'iknow',
            'outputdir' => IKNOW_PATH,    //使用新版本编译后的产出
            "tpls" =>array(
                "/template/search/page/layout/search.tpl",
                "/template/search/page/index.tpl",
                "/template/search/page/download.tpl",
                "/template/search/page/symptom.tpl",
                "/template/search/page/api/list.tpl",
                "/template/search/page/api/ikasnews.tpl",
                "/template/search/page/api/nsatomaslist.tpl",
                "/template/search/page/api/nsbkatomaslist.tpl"
            )
        )
    )
);
