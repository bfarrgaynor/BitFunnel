<html>
<head>
<title>Funnel Project</title>

<style type="text/css">

div.toggler  { border:1px solid #ccc; background:url(gmail2.jpg) 10px 12px #eee no-repeat; cursor:pointer; padding:10px 32px; }
div.toggler .subject  { font-weight:bold; }
div.read  { color:#666; }
div.toggler .from, div.toggler .date { font-style:italic; font-size:11px; }
div.body   { padding:10px 20px; }

</style>

</head>

<body>

<?php

//include the deps
require("config.php");
require("Funnel.php");

//go!
$funnel = new Funnel($config['account'][0]);

?>
</body>
</html>