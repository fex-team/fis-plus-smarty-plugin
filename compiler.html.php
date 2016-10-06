<?php
function smarty_compiler_html($arrParams,  $smarty){
    $strResourceApiPath = preg_replace('/[\\/\\\\]+/', '/', dirname(__FILE__) . '/FISResource.class.php');
    $strFramework = $arrParams['framework'];
    unset($arrParams['framework']);
    $rendermode = isset($arrParams['rendermode']) ? $arrParams['rendermode'] : false;
    unset($arrParams['rendermode']);

    $strAttr = '';
    $strCode  = '<?php ';
    if (isset($strFramework)) {
        $strCode .= 'if(!class_exists(\'FISResource\', false)){require_once(\'' . $strResourceApiPath . '\');}';
        $strCode .= 'FISResource::setFramework(FISResource::getUri('.$strFramework.', $_smarty_tpl->smarty));';
    }
    if ($rendermode) {
        $strCode .= 'if(!class_exists(\'FISResource\', false)){require_once(\'' . $strResourceApiPath . '\');}';
        $strCode .= 'FISResource::setRenderMode(' . $rendermode . ');';
    }


    $strCode .= ' ?>';

    foreach ($arrParams as $_key => $_value) {
        if (is_numeric($_key)) {
            $strAttr .= ' <?php echo ' . $_value .';?>';
        } else {
            $strAttr .= ' ' . $_key . '="<?php echo ' . $_value . ';?>"';
        }
    }
    /**
     * 后端的服务器进程为常住进程，需要在页面头部取消上次 register 的事件，防止在 smarty.fetch 方法调用 ，导致清空临时资源数据
     * Date: 2016-07-21
     * Author: raisezhang@hotmail.com
     */
    $strCode .= '<?php ';
    $strCode .= '$_smarty_tpl->unregisterFilter(\'output\', array(\'FISResource\', \'renderResponse\'));';
    $strCode .= '?>';

    return $strCode . "<html{$strAttr}>";
}

function smarty_compiler_htmlclose($arrParams,  $smarty){
    $strCode = '<?php ';
    $strCode .= '$_smarty_tpl->registerFilter(\'output\', array(\'FISResource\', \'renderResponse\'));';
    $strCode .= '?>';
    $strCode .= '</html>';
    return $strCode;
}
