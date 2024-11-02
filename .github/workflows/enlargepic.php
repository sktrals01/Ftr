<?php

require_once("initvars.inc.php");
require_once("config.inc.php");
if(!$_GET['picid']) exit;

$sql = "SELECT picfile FROM $t_adpics WHERE picid = $_GET[picid]";
list($filename) = @mysql_fetch_array(mysql_query($sql));

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title><?php echo $page_title; ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" type="text/css" href="style.css">
</head>

<body>
<p><p><p>
<div align="center">
<img src="<?php echo "{$datadir[adpics]}/{$filename}"; ?>" border="1">
</div>
<p><p><p>
</body>