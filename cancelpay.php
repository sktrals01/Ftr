<?php
$path_escape = "";
require_once("initvars.inc.php");
require_once("config.inc.php");
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Payment Cancelled</title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link rel="stylesheet" type="text/css" href="style.css">
</head>

<body>
<br><br><br><br>
<br><br><br><br>
<div align="center">
<div id="logo">
<a href="index.php?view=main&cityid=<?php echo $xcityid; ?>">
<?php echo $site_name; ?></a>
</div>
<br>
<div class="err">The payment was cancelled</div><br />
<a href="index.php">Go to Homepage</a>
</div>
</body>
</html>