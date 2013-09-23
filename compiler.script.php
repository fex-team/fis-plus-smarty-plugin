<?php

function smarty_compiler_script($params,  $smarty){
    $strCode = '<?php ';
    if (isset($params['id'])) {
        $strResourceApiPath = preg_replace('/[\\/\\\\]+/', '/', dirname(__FILE__) . '/FISResource.class.php');
        $strCode .= 'if(!class_exists(\'FISResource\')){require_once(\'' . $strResourceApiPath . '\');}';
        $strCode .= 'FISResource::$cp = ' . $params['id'].';';
    }
    $strCode .= 'ob_start();?>';
    return $strCode;
}

function smarty_compiler_scriptclose($params,  $smarty){
    $strResourceApiPath = preg_replace('/[\\/\\\\]+/', '/', dirname(__FILE__) . '/FISResource.class.php');
    $strCode  = '<?php ';
    $strCode .= '$script=ob_get_clean();';
    $strCode .= 'if($script!==false){';
    $strCode .=     'if(!class_exists(\'FISResource\')){require_once(\'' . $strResourceApiPath . '\');}';
    $strCode .=     'if(FISResource::$cp) {';
    $strCode .=         'if (!in_array(FISResource::$cp, FISResource::$arrEmbeded)){';
    $strCode .=             'FISResource::addScriptPool($script);';
    $strCode .=             'FISResource::$arrEmbeded[] = FISResource::$cp;';
    $strCode .=         '}';
    $strCode .=     '} else {';
    $strCode .=         'FISResource::addScriptPool($script);';
    $strCode .=     '}';
    $strCode .= '}';
    $strCode .= 'FISResource::$cp = null;?>';
    return $strCode;
}