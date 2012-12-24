<?php

//include the deps

require("BitFunnel.php");

$check = __DIR__ . DIRECTORY_SEPARATOR . 'config.php';
$account = (is_file($check) ? require $check : array());

//go!
$bitFunnel = new BitFunnel($account);
