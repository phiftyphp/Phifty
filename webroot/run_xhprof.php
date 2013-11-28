<?php
// precheck, we don't need to run profilter under /xhprof path
$pathinfo = $_SERVER['PATH_INFO'];
if( preg_match( '#/xhprof#',$pathinfo ) ) {
    require 'index.php';
    exit(0);
}

// Saving the XHProf run
// using the default implementation of iXHProfRuns.
$XHPROF_ROOT = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . '/vendor/xhprof';
include_once $XHPROF_ROOT . "/xhprof_lib/utils/xhprof_lib.php";
include_once $XHPROF_ROOT . "/xhprof_lib/utils/xhprof_runs.php";

xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);

require 'index.php';

$xhprof_data = xhprof_disable();
$xhprof_runs = new XHProfRuns_Default();

$profiler_namespace = preg_replace( '#\W+#' , '' , $pathinfo ?: 'default_run' );
$run_id = $xhprof_runs->save_run($xhprof_data, $profiler_namespace);
$profiler_url = sprintf('/xhprof?run=%s&source=%s',$run_id, $profiler_namespace);
?>
<style> 
.xhprof-bar { 
    position: fixed;
    bottom: 0px;
    width: 100%;
    background-color: #ddd;
    border-top: 1px solid #aaa;
    padding: 3px;
    text-align: left;
}
.xhprof-bar a.btn { 
    display: inline-block;
    text-decoration: none;
    border: 1px solid #AAA;
    border-radius: 3px;
    padding: 5px;
    color: white;
    background-color: #4BA5FF;
    box-shadow: inset 0 0 10px #DDD;
}
.xhprof-bar a.btn:hover { 
    color: #fff;
    background-color: #5197D8;
}
</style>
<!-- use javascript to insert xhprof link into debug bar -->
<div class="xhprof-bar">
    <a class="btn" target="_blank" href="<?=$profiler_url?>">XHProf [<?=$profiler_namespace?>]</a>
    <a class="btn" onclick="$(this).parent('.xhprof-bar').fadeOut();">x</a>
</div>
