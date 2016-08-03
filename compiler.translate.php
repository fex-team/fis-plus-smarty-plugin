<?php

function smarty_compiler_translate($params,  $smarty){
    $strModule = isset($params['module']) ? trim($params['module']) : '';
    $strCode = '';
    if ($strModule) {
        $strCode .= '<?php ';
        $strResourceApiPath = preg_replace('/[\\/\\\\]+/', '/', dirname(__FILE__) . '/FIS.init.php');
        $strCode .= 'if(!class_exists(\'FISTranslate\', false)){require_once(\'' . $strResourceApiPath . '\');}';
        $strCode .= 'FISTranslate::scope('.$strModule.', $_smarty_tpl)';
        $strCode .= '?>';

    }
    return $strCode;
}
