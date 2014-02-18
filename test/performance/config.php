<?php
/** 根目录地址 */
if(!defined('DIFF_ROOT_PATH')) define('DIFF_ROOT_PATH', str_replace('\\', '/', dirname(__FILE__)) . '/');
/** 定义和产品线相关的产出目录 */
if(!defined('BATMAN_PATH')) define('BATMAN_PATH',DIFF_ROOT_PATH.'product_output/batman/');

$config = array(
    'product'=>array(      //产品线目录
        'batman'=>array(
            'name'=>'batman',
            'outputdir' => BATMAN_PATH,    //使用新版本编译后的产出
            "tpls" =>array(
                "/template/drive/widget/list/list.tpl",
                "/template/index/page/index.tpl"
            )
        )
    )
);
