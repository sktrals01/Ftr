<?php




require_once("initvars.inc.php");
require_once("config.inc.php");


if (($_GET['type'] == "event" || $_GET['type'] == "ad") && $_GET['adid'] && $_GET['codemd5'])
{
	$adid = $_GET['adid'];

	if($_GET['type'] == "event")
	{
		$adtable = $t_events;
		$targetview = "showevent";

        /* Begin Version 5.0 */
        $sql = "SELECT cityid, starton FROM $t_events WHERE adid = $adid";
		list($xcityid, $xdate) = mysql_fetch_array(mysql_query($sql));

		if($sef_urls) $adlink = "$script_url/{$vbasedir}$xcityid/events/$adid.html";
		else $adlink = "$script_url/?view=$targetview&adid=$_GET[adid]&cityid=$xcityid";

        $adlink = "{$script_url}/" . buildURL("showevent", array($xcityid, $xdate, $adid));
        /* End Version 5.0 */
        
	}
	else
	{
		$adtable = $t_ads;
		$targetview = "showad";

        /* Begin Version 5.0 */
        $sql = "SELECT a.adtitle, a.cityid, scat.subcatid, scat.subcatname, cat.catid, cat.catname FROM $t_ads a INNER JOIN $t_subcats scat ON a.subcatid = scat.subcatid INNER JOIN $t_cats cat ON scat.catid = cat.catid WHERE adid = $adid";
		list($adtitle, $xcityid, $xsubcatid, $xsubcatname, $xcatid, $xcatname) = mysql_fetch_row(mysql_query($sql));

		if($sef_urls) $adlink = "$script_url/{$vbasedir}$xcityid/posts/$xcatid/$xsubcatid/$adid.html";
		else $adlink = "$script_url/?view=$targetview&adid=$_GET[adid]&cityid=$xcityid";

        $adlink = "{$script_url}/" . buildURL("showad", array($xcityid, $xcatid, $xcatname, 
            $xsubcatid, $xsubcatname, $_GET['adid'], $adtitle));
        /* End Version 5.0 */
	}

	$sql = "UPDATE $adtable SET verified = '1'
			WHERE adid = $_GET[adid] AND
				MD5(code) = '$_GET[codemd5]' AND
				verified = '0'";
	
	mysql_query($sql) or die(mysql_error());

	if(mysql_affected_rows())
	{

		$success = 1;
		$showlink = 1;
		
		$title = $lang['EMAIL_VERIFIED'];
		$msg = $lang['MESSAGE_EMAIL_VERIFIED_AD'];

		/*$sql = "SELECT email, password, adtitle FROM $adtable WHERE adid = $_GET[adid]";
		list($useremail, $password, $adtitle) = mysql_fetch_array(mysql_query($sql));
		
		$mailmsg = file_get_contents("mailtemplates/approved.txt");
		$mailmsg = str_replace("{@SITENAME}", $site_name, $mailmsg);
		$mailmsg = str_replace("{@EXPIREAFTER}", $delete_after, $mailmsg);
		$mailmsg = str_replace("{@PASSWORD}", $password, $mailmsg);

		$mailmsg = str_replace("{@ADTITLE}", $adtitle, $mailmsg);

		$mailmsg = str_replace("{@ADURL}", "$adlink", $mailmsg);

		$editlink = "$script_url/?view=edit" . 
					($targetview=="showevent" ? "&isevent=1" : "") .
					"&adid=$_GET[adid]&cityid=$xcityid&lang=$xlang";
		$mailmsg = str_replace("{@EDITURL}", "$editlink", $mailmsg);


		if (!@xmail($useremail, $lang['MAILSUBJECT_POST_APPROVED'], $mailmsg, $site_email, $langx['charset']))
		{
			$err = "Error sending approval mail";
			$mailerr = TRUE;
		}*/

	}
	else
	{
		$err = $lang['ERROR_INVALID_ACTIVATION_LINK'];
	}

	unset($_GET['type'], $_GET['codemd5']);

}

elseif ($_GET['type'] == "img" && $_GET['imgid'] && $_GET['codemd5'])
{

	$sql = "UPDATE $t_imgs SET verified = '1'
			WHERE imgid = $_GET[imgid] AND
				MD5(code) = '$_GET[codemd5]' AND
				verified = '0'";
	mysql_query($sql) or die(mysql_error());

	if(mysql_affected_rows())
	{
		$success = 1;
		$showlink = 0;
		
		$title = $lang['EMAIL_VERIFIED'];
		$msg = $lang['MESSAGE_EMAIL_VERIFIED_IMAGE'];

		/*$adlink = "$script_url/?view=showimg&posterenc=$posterenc&imgid=$_GET[imgid]&cityid=$xcityid&lang=$xlang";

		$sql = "SELECT postername, posteremail, password, imgtitle FROM $t_imgs WHERE imgid = $_GET[imgid]";
		list($postername, $useremail, $password, $imgtitle) = mysql_fetch_array(mysql_query($sql));
		echo mysql_error();
		$posterenc = EncryptPoster("IMG", $postername, $useremail);
		
		$mailmsg = file_get_contents("mailtemplates/imgapproved.txt");
		$mailmsg = str_replace("{@SITENAME}", $site_name, $mailmsg);
		$mailmsg = str_replace("{@EXPIREAFTER}", $images_delete_after, $mailmsg);
		$mailmsg = str_replace("{@PASSWORD}", $password, $mailmsg);

		$mailmsg = str_replace("{@IMAGETITLE}", $imgtitle, $mailmsg);

		$adlink = "$script_url/?view=showimg&posterenc=$posterenc&imgid=$_GET[imgid]&cityid=$xcityid&lang=$xlang";
		$mailmsg = str_replace("{@IMAGEURL}", "<a href=\"$adlink\">$adlink</a>", $mailmsg);

		$editlink = "$script_url/?view=editimg&imgid=$_GET[imgid]&cityid=$xcityid&lang=$xlang";
		$mailmsg = str_replace("{@DELETEURL}", "<a href=\"$editlink\">$editlink</a>", $mailmsg);


		if (!@htmlmail($useremail, $lang['MAILSUBJECT_POST_APPROVED'], nl2br($mailmsg), $site_email))
		{
			$err = "Error sending approval mail";
			$mailerr = TRUE;

		}*/

	}
	else
	{
		$err = $lang['ERROR_INVALID_ACTIVATION_LINK'];
	}

	unset($_GET['type'], $_GET['codemd5']);

}


?>

<div>

<?php if ($success) { ?>

<h2><?php echo $title; ?></h2>

<div><?php echo $msg; ?></div>

<?php if ($showlink) { ?>
<p><?php echo "$lang[SEE_YOUR_POST_HERE]:<br><a href=\"$adlink\">".htmlspecialchars($adlink)."</a>"; ?></p>

<?php } ?>

<?php } ?>

<?php if ($err) { ?>

<div class="err"><?php echo $err; ?></div><br>
<?php
if($mailerr && $debug) echo "<p>Printing out approval mail contents for testing purposes</p><pre>$msg</pre><br><br>";
?>

<?php } ?>
<a href="?cityid=<?php echo $xcityid; ?>"><?php echo $lang['BACK_TO_HOME']; ?></a>

</div>