<?php

function smarty_compiler_body($arrParams,  $smarty){
    $strAttr = '';

    foreach ($arrParams as $_key => $_value) {
        if (is_numeric($_key)) {
            $strAttr .= ' <?php echo ' . $_value .';?>';
        } else {
            $strAttr .= ' ' . $_key . '="<?php echo ' . $_value . ';?>"';
        }
    }

    return '<body' . $strAttr . '>';
}

function smarty_compiler_bodyclose($arrParams,  $smarty){
    $strCode  = '<?php ';
    $strCode .= 'if(class_exists(\'FISResource\', false)){';
    $strCode .= 'echo FISResource::jsHook();';
    $strCode .= '}';
    $strCode .= '?>';
    $strCode .= '</body>';
    return $strCode;
}
