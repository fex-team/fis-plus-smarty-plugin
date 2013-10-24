<?php
require_once dirname(__FILE__) . "/change.class.php";

$config = array();
$config = change::loadConfig(dirname(__FILE__).'/config.php');
$productlist = $config['product'];

foreach($productlist as $pro => $value){
    $change = new Change($pro);
    $change->ChangeIndex();
}
