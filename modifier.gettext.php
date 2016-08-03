<?php

function smarty_modifier_gettext($text) {
    if (!class_exists('FISTranslate')) {
        return $text;
    }
    $val = FISTranslate::getText($text);
    $cnt = func_num_args();
    if ($cnt > 1) {
        $params = func_get_args();
        for ($i = 1; $i < $cnt; $i++) {
            $val = str_replace('{'. ($i - 1) .'}', $params[$i], $val);
        }
    }
    return $val;
}
