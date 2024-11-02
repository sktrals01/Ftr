<?php
include_once("initvars.inc.php");
include_once("config.inc.php");
include_once("captcha.cls.php");
$captcha = new captcha();
ob_clean();
$captcha->image();
?>