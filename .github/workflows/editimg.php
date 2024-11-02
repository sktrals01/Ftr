<?php




require_once("initvars.inc.php");
require_once("config.inc.php");

?>
<div align="center">
<?php

if ($_REQUEST['imgid'] && $_REQUEST['codemd5'])
{
	$sql = "SELECT imgid, imgtitle, imgfilename FROM $t_imgs WHERE imgid = $_REQUEST[imgid] AND MD5(code) = '{$_REQUEST[codemd5]}'";
	list($imgid, $imgtitle, $imgfilename) = mysql_fetch_row(mysql_query($sql));

	if ($imgid)
	{
		$auth = TRUE;
		$codemd5 = $_REQUEST['codemd5'];
	}
	else
	{
		$auth = FALSE;
	}
}

if ($auth)
{
	if ($_POST['do'] == "del")
	{
		$sql = "SELECT imgfilename FROM $t_imgs WHERE imgid = $imgid";
		list($imgfilename) = mysql_fetch_row(mysql_query($sql));

		if ($imgfilename) unlink("{$datadir[userimgs]}/$imgfilename");

		$sql = "DELETE FROM $t_imgs WHERE imgid = $imgid";
		mysql_query($sql);

?>

			<?php echo $lang['MESSAGE_IMAGE_DELETED']; ?><br>
			<a href="index.php?cityid=<?php echo $xcityid; ?>"><?php echo $lang['BACK_TO_HOME']; ?></a>

<?php
			
	}
	else
	{

?>

		<form method="post" action="">

		<h2><?php echo $lang['EDIT_IMAGE']; ?></h2>
		<h3><?php echo $imgtitle; ?></h3>
		<img src="<?php echo "{$datadir[userimgs]}/$imgfilename"; ?>" border="1"><br><br>

		<input type="hidden" name="imgid" value="<?php echo $imgid; ?>">
		<input type="hidden" name="codemd5" value="<?php echo $codemd5; ?>">
		<input type="hidden" name="do" value="del">

		<button type="submit" value="Delete" onclick="return(confirm('<?php echo $lang['EDIT_AD_CONFIRM_DELETE']; ?>'));">Delete</button>
		</form>

<?php

	}
}
else
{
	echo "<div class=\"err\">{$lang[ERROR_INVALID_EDIT_LINK]}</div>";
}

?>

</div>