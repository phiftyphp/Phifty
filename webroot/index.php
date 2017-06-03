<?php
use Funk\Environment;
use Funk\Responder\SAPIResponder;

$app = require '../bootstrap.php';
$environment = Environment::createFromGlobals();
$resp = $app->call($environment, []);
(new SAPIResponder)->respond($resp);
