<?php
use Funk\Environment;
use Funk\Responder\SAPIResponder;

header('P3P:CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');
header('X-FRAME-OPTIONS:SAMEORIGIN');
header('Pragma:No-cache');
header('Cache-Control: no-cache');
header('Expires: 0');

$app = require '../bootstrap.php';
$environment = Environment::createFromGlobals();
$resp = $app->call($environment, []);
(new SAPIResponder)->respond($resp);
