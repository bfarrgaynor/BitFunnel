<?php

//include the deps
require("config.php");
require("BitFunnel.php");

//go!
$bitFunnel = new BitFunnel($config['account'][0]);
