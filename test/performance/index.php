<?php
ini_set('display_errors', 1);
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

$root = dirname(__FILE__);
require_once ($root . '/smarty/Smarty.class.php');
$smarty = new Smarty();
$smarty->setTemplateDir($root . '/template');
$smarty->setConfigDir($root . '/config');
$smarty->addPluginsDir($root . '/plugin');
$smarty->setLeftDelimiter('{%');
$smarty->setRightDelimiter('%}');

xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);
	
<*display*>

// stop profiler
$xhprof_data = xhprof_disable();

// display raw xhprof data for the profiler run
#print_r($xhprof_data);


$XHPROF_ROOT = realpath('/home/work/repos/xhprof-0.9.2');
include_once $XHPROF_ROOT . "/xhprof_lib/utils/xhprof_lib.php";
include_once $XHPROF_ROOT . "/xhprof_lib/utils/xhprof_runs.php";

// save raw data for this profiler run using default
// implementation of iXHProfRuns.
$xhprof_runs = new XHProfRuns_Default();

// save the run under a namespace "xhprof_foo"
$run_id = $xhprof_runs->save_run($xhprof_data, "xhprof_foo");
$time = date('Y-m-d H:i:s', time());
$result = "<tr><td>$argv[2]</td><td>$argv[3]</td><td><a href='http://cq01-testing-fis00.vm.baidu.com:8088/xhprof-0.9.2/xhprof_html/index.php?run=$run_id&source=xhprof_foo'>detail</a></td><td>$time</td></tr></table>";
file_put_contents($argv[1].'/result.html', str_replace("</table>", $result, file_get_contents($argv[1].'/result.html')));
