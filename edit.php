<?php



ob_start();
require_once("initvars.inc.php");
require_once("config.inc.php");

$qs = "";
foreach($_GET as $k=>$v) $qs .= "$k=$v&";

require_once("userauth.inc.php");


if (!$auth)
{
	echo "<div class=\"err\">{$lang[ERROR_INVALID_EDIT_LINK]}</div>";
}
else
{
?>

<div align="right"><b>
<?php 
 
 // expiration renewal added by David Jonas (FuNEnD3R)
 $res_sql = "SELECT createdon, ip FROM ".$t_ads." WHERE UNIX_TIMESTAMP(expireson) <= '".(time()+$expire_ads_ahead)."' AND adid= '".$adid."'";
 $resc = mysql_query($res_sql);
 
 if ( mysql_fetch_array($resc) > 0 )
 {
 ?>
 <img src="images/bullet.gif" align="absmiddle"> <a href="index.php?view=edit&target=renew&cityid=<?php echo $xcityid; ?>"><?php echo $lang['RENEW_AD']; ?></a>&nbsp;
 <?php 
 }
?>
<img src="images/bullet.gif" align="absmiddle"> <a href="index.php?view=edit&cityid=<?php echo $xcityid; ?>"><?php echo $lang['EDIT_AD']; ?></a>&nbsp;
<img src="images/bullet.gif" align="absmiddle"> <a href="index.php?view=edit&target=promote&cityid=<?php echo $xcityid; ?>"><?php echo $lang['AD_PROMOTIONS']; ?></a>&nbsp;
<img src="images/bullet.gif" align="absmiddle"> <a href="?view=edit&do=signout&cityid=<?php echo $xcityid; ?>" style="color:red"><?php echo $lang['SIGNOUT']; ?></a>&nbsp;

</b><br><br></div>


<?php

	if ($_GET['target'] == "promote")
	{
		include("promote.php");
	}
	elseif ($_GET['target'] == "renew")
	{

		include("renew.php");

	}
	else
	{
?>
<?php


	if ($_POST['do'] == "post")
	{
		$data = $_POST;
		$data['area'] = $data['area']?$data['area']:$data['arealist'];
		recurse($data, 'stripslashes');

		if($data['subcatid'])
		{
			$xsubcatid = $data['subcatid'];
			list($xsubcathasprice, $xsubcatpricelabel, $xsubcatfields) = GetCustomFields($xsubcatid);
		}


		if(!$data['adtitle']) 
		{
			$data['adtitle'] = substr($data['addesc'], 0, $generated_adtitle_length) . ((strlen($data['addesc']) > $generated_adtitle_length) ? $generated_adtitle_append : "");
	
			if(strpos($data['adtitle'], "\n") > 0) $data['adtitle'] = trim(substr($data['adtitle'], 0, strpos($data['adtitle'], "\n")));

		}



        /* Begin Version 5.0 */
        $value_missing = FALSE;
		if (!$data['addesc']) {
			$err .= "&bull; $lang[ERROR_POST_FILL_ALL]";
			$value_missing = TRUE;
		}	
        /* End Version 5.0 */

		/*if(!ValidateEmail($data['email']))
			$err .= "&bull; $lang[ERROR_INVALID_EMAIL]<br>";*/
		if (!$data['agree'])
			$err .= "&bull; $lang[ERROR_POST_AGREE_TERMS]<br>";
		
		$numerr = "";
		if($data['price'] && !preg_match("/^[0-9\.]*$/", $data['price'])) 
			$numerr .= "- $xsubcatpricelabel<br>";

		if(is_array($data['x']))
		{
			foreach ($data['x'] as $fldnum=>$val)
			{
				/* Begin Version 5.0 */
    		    if (!$value_missing && $xsubcatfields[$fldnum]['REQUIRED'] && !trim($val)) 
    		    {
    		        $err = "&bull; $lang[ERROR_POST_FILL_ALL]<br>" . $err;
    		        $value_missing = TRUE;
    		    }
    			else if($xsubcatfields[$fldnum]['TYPE'] == "N" && !preg_match("/^[0-9]*$/", $val))
				{
					$fldname = $xsubcatfields[$fldnum]['NAME'];
					$numerr .= "- {$fldname}<br>";
				}
				/* End Version 5.0 */
			}
		}

		if($numerr) $err .= "&bull; $lang[ERROR_POST_MUST_BE_NUMBER]<br>$numerr";
	}

	if ($_GET['do'] == "delete" && $_GET['adid'] && $_GET['adid'] == $adid)
	{
		// Delete current pics
		$sql = "SELECT picfile FROM $t_adpics WHERE adid = $adid AND isevent = '$isevent'";
		$res = mysql_query($sql) or die($sql);
		while($row=@mysql_fetch_assoc($res)) @unlink("{$datadir[adpics]}/{$row[picfile]}");

		// Delete pics from db
		$sql = "DELETE FROM $t_adpics WHERE adid = $adid AND isevent = '$isevent'";
		mysql_query($sql);
		
		// Delete ad
		$sql = "DELETE FROM $table WHERE adid = $adid";
		mysql_query($sql);

		// Delete extra fields
		if(!$isevent)
		{
			$sql = "DELETE FROM $t_adxfields WHERE adid = $adid";
			mysql_query($sql);
		}

		// Clear cookies
		setcookie($ck_edit_adid, "", 0, "/");
		setcookie($ck_edit_codemd5, "", 0, "/");

?>
		<?php echo $lang['EDIT_AD_DELETED']; ?><br>
		<a href="?view=main&cityid=<?php echo $xcityid; ?>"><?php echo $lang['BACK_TO_HOME']; ?></a>

<?php

	}

	elseif ($_POST['do'] == "post" && !$err)
	{
		foreach ($data as $k=>$v)
		{
			if ($k == "addesc") {
				recurse($data[$k], 'htmlspecialchars');
				recurse($data[$k], 'mysql_escape_string');
			}
			else {
				recurse($data[$k], 'htmlspecialchars');
				recurse($data[$k], 'mysql_escape_string');
			}
		}

		if(is_array($data['x']))
		{
			foreach ($data['x'] as $fldnum=>$val)
			{
				if($xsubcatfields[$fldnum]['TYPE'] == "N") $data['x'][$fldnum]=0+$val;
			}
		}


		$data['price'] = 0 + str_replace(",", "", $data['price']);
		//$passsql = $data['password'] ? "password = '$data[password]'," : "";
		$data['othercontactok'] = 0 + $data['othercontactok'];

		$data['adtitle'] = FilterBadWords($data['adtitle']);
		$data['addesc'] = FilterBadWords($data['addesc']);
		$data['area'] = FilterBadWords($data['area']);

		$sql = "SET adtitle = '$data[adtitle]',
					addesc = '$data[addesc]',
					area ='$data[area]',
					#email = '$data[email]',
					showemail = '$data[showemail]',
					#$passsql
					othercontactok = '$data[othercontactok]',
					newsletter = '$data[newsletter]',
					timestamp = NOW(),";

		if($isevent)
		{
			$sql = "UPDATE $table " . $sql .
					" starton = '$data[fy]-$data[fm]-$data[fd]',
					endon = '$data[ty]-$data[tm]-$data[td]'";
		}
		else
		{
			$sql = "UPDATE $table " . $sql .
					" subcatid = $data[subcatid],
					price = $data[price]";
		}

		$sql .= " WHERE adid = $adid";

		mysql_query($sql) or die($sql.mysql_error());
		

		// Save extra fields
		if (is_array($data['x']) && count($data['x']))
		{
			$sql = "UPDATE $t_adxfields SET ";
			foreach ($data['x'] as $fldnum=>$val)
			{
				if($xsubcatfields[$fldnum]['TYPE'] == "N") 
				{
					//if($val == "") $val = -1;
					//else 
					$val = 0+$val;
				}
				$sql .= "f{$fldnum} = '$val',";
			}
			$sql = substr($sql, 0, -1) . " WHERE adid = $adid";
			mysql_query($sql) or print($sql);
		}


	?>

			<h2><?php echo $lang['EDIT_AD_SUCCESS']; ?></h2>

	<?php


		// Delete pictures	
		$delpiccnt = 0;
		if (count($data['delpic']))
		{
			foreach($data['delpic'] as $picid)
			{
				$sql = "SELECT picfile FROM $t_adpics WHERE picid = $picid";
				list($filename) = mysql_fetch_array(mysql_query($sql));
				unlink("{$path_escape}{$datadir[adpics]}/$filename");

				$sql = "DELETE FROM $t_adpics WHERE picid = $picid";
				mysql_query($sql);

				$delpiccnt++;
			}
		}

		if($delpiccnt) $msg .= "<br>$delpiccnt picture(s) deleted";


		/*// Upload pictures
		if ($data['editphotos'])
		{
			// Delete current pics
			$sql = "SELECT picfile FROM $t_adpics WHERE adid = $adid AND isevent = '$isevent'";
			$res = mysql_query($sql) or die($sql);
			while($row=@mysql_fetch_assoc($res)) @unlink("{$datadir[adpics]}/{$row[picfile]}");

			// Delete from db
			$sql = "DELETE FROM $t_adpics WHERE adid = $adid AND isevent = '$isevent'";
			mysql_query($sql);
		}*/
		
		if (count($_FILES['pic']['tmp_name']))
		{
			$ipval = ipval();
			$uploaderror = 0;
			$uploadcount = 0;
			/* Begin Version 5.0 */
			$errorMessages = array();
			/* End Version 5.0 */


			foreach ($_FILES['pic']['tmp_name'] as $k=>$tmpfile)
			{
				if ($tmpfile)
				{
				    /* Begin Version 5.0 */
					$thisfile = array("name"=>$_FILES['pic']['name'][$k],
						"tmp_name"=>$_FILES['pic']['tmp_name'][$k],
						"size"=>$_FILES['pic']['size'][$k],
						"type"=>$_FILES['pic']['type'][$k],
						"error"=>$_FILES['pic']['error'][$k]);
				    /* End Version 5.0 */
				    
					// Check size
					if ($_FILES['pic']['size'][$k] > $pic_maxsize*1000)
					{
					    /* Begin Version 5.0 */
					    $errorMessages[] = $thisfile['name'] . " - " . $lang['ERROR_UPLOAD_PIC_TOO_BIG'];
                        /* End Version 5.0 */
						$uploaderror++;
					}
				    /* Begin Version 5.0 */					
					// Check file type
					elseif (!isValidImage($thisfile))
					{
					    /* Begin Version 5.0 */
					    $errorMessages[] = $thisfile['name'] . " - " . $lang['ERROR_UPLOAD_PIC_BAD_FILETYPE'];
					    /* End Version 5.0 */
						$uploaderror++;
					}
				    /* End Version 5.0 */					
					else
					{
						$newfile = SaveUploadFile($thisfile, $datadir['adpics'], TRUE, $images_max_width, $images_max_height);

						if($newfile)
						{
							$sql = "INSERT INTO $t_adpics
									SET adid = $adid,
										isevent = '$isevent',
										picfile = '$newfile'";
							mysql_query($sql);

							if (mysql_error())
							{
							    /* Begin Version 5.0 */
								$errorMessages[] = $thisfile['name'] . " - " . $lang['ERROR_UPLOAD_PIC_INTERNAL'];
								/* End Version 5.0 */
								$uploaderror++;
							}
							else
							{
								$uploadcount++;
							}

						}
						else
						{
						    /* Begin Version 5.0 */
    						echo "<!-- {$k} - Permission error; can not copy -->";
						    $errorMessages[] = $thisfile['name'] . " - " . $lang['ERROR_UPLOAD_PIC_INTERNAL'];
    						/* End Version 5.0 */
							$uploaderror++;
						}
					}
				}
				elseif ($_FILES['pic']['name'][$k])
				{
				    /* Begin Version 5.0 */
				    echo "<!-- {$k} - Temp file not present -->";
				    /* End Version 5.0 */
					$uploaderror++;
				}
			}

			if ($uploadcount)
			{
				echo "$lang[PICTURES_UPLOADED]: $uploadcount<br>";
			}
			if($uploaderror)
			{
			    /* Begin Version 5.0 */
			    $errorMessageToShow = implode("<br>", $errorMessages);
				echo "<p class=\"err\">$lang[PICTURES_NOT_UPLOADED]: $uploaderror<br><span style=\"font-weight:normal;\">{$errorMessageToShow}</span></p>";
				/* End Version 5.0 */
			}
		}

		$sql = "SELECT verified, enabled FROM $table WHERE adid = $adid";
		list($thisad_enabled, $thisad_verified) = mysql_fetch_array(mysql_query($sql));

		if ($thisad_enabled && $thisad_verified)
		{
			$adurl = "$script_url/?view=$view&adid=$adid&cityid=$xcityid";

	?>
		
		<?php echo $lang['SEE_YOUR_POST_HERE']; ?>:<br>
		<a href="<?php echo $adurl; ?>"><?php echo $adurl; ?></a><br><br>

	<?php

		}

	?>

		<?php echo $lang['SIGNOUT_REMINDER']; ?>
		<br><br>
		<a href="?view=edit&do=signout&cityid=<?php echo $xcityid; ?>"><?php echo $lang['SIGNOUT']; ?></a> |
		<a href="?view=main"><?php echo $lang['BACK_TO_HOME']; ?></a>

	<?php

	}

	else
	{
		$data_orig = $ad;
		
		recurse($data, 'htmlspecialchars');	// Done before merging with original data as thats already saved as escaped.
		if ($_POST['do'] == "post") $data = $data + $data_orig;
		else $data = $data_orig;

		if ($isevent)
		{
			$catname = $lang['EVENTS'];

			// Split dates
			if (!$data['fy'])
			{
				preg_match("/([0-9]{4})-([0-9]{2})-([0-9]{2})/isU", $data['starton'], $m);
				list($dummy, $data['fy'], $data['fm'], $data['fd']) = $m;

				preg_match("/([0-9]{4})-([0-9]{2})-([0-9]{2})/isU", $data['endon'], $m);
				list($dummy, $data['ty'], $data['tm'], $data['td']) = $m;
			}
		
			/* Begin Version 5.0 */
				
			// Date lists
			$dlist = "";
			for($i=1; $i<=31; $i++) $dlist .= "<option value=\"$i\">$i</option>\n";
			
			$mlist = "";
			for ($i=1; $i<=12; $i++) $mlist .= "<option value=\"$i\">".$langx['months'][$i-1]."</option>\n";
			
			$ylist = "";
			$thisy = date("Y");
			$starty = $data['fy'] < $thisy ? $data['fy'] : $thisy;
			$endy = $thisy + 1;
			for ($i=$starty; $i<=$endy; $i++) $ylist .= "<option value=\"".($i)."\">".($i)."</option>";
			
			/* End Version 5.0 */
		}
		else
		{
			$xsubcatid = $subcatid = $data['subcatid'];
			$sql = "SELECT subcatname AS subcatname, hasprice, pricelabel FROM $t_subcats WHERE subcatid = $subcatid AND enabled = '1'";
			list($catname, $hasprice, $pricelabel) = mysql_fetch_array(mysql_query($sql));

			list($xsubcathasprice, $xsubcatpricelabel, $xsubcatfields) = GetCustomFields($xsubcatid);

			$sql = "SELECT * FROM $t_adxfields WHERE adid = $adid LIMIT 1";
			$row = @mysql_fetch_array(mysql_query($sql));
			$x = array();
			
			for($i=1; $i<=$xfields_count; $i++)
			{
				$x[$i] = $row["f{$i}"];
			}
			
			if (is_array($data['x'])) $data['x'] = $data['x'] + $x;
			else $data['x'] = $x;
		}

	
?>


<script language="javascript">

function insertLink(link)
{
	var editpane = document.frmPost.addesc;
	var linkcode = "[URL]" + link + "[/URL]";

	editpane.focus();
	/*if (document.selection)
	{
		document.selection.createRange().text = linkcode;
	}
	else*/
	if (editpane.selectionStart || editpane.selectionStart == '0')
	{
		var selstart = editpane.selectionStart;
		var selend = editpane.selectionEnd;
		
		editpane.value = editpane.value.substring(0, selstart) + linkcode + editpane.value.substring(selend);
		editpane.selectionStart = selstart + linkcode.length;
		editpane.selectionEnd = editpane.selectionStart;
	}
	else
	{
		editpane.value = editpane.value + linkcode;
	}

	editpane.focus();

}
</script>
	
<!-- Begin Version 5.0 -->
<script language="javascript">
function checkPostFields(form) {
	
	var msg = '';
	/* Begin Version 5.0 */
	var value_missing = false;
	/* End Version 5.0 */
	
	if (form.elements['addesc'].value == '') {
		msg += '<?php echo $lang['ERROR_POST_FILL_ALL']; ?>\n';
    	/* Begin Version 5.0 */
    	value_missing = true;
    	/* End Version 5.0 */
	}
	
	if (!form.elements['agree'].checked) {
		msg += '<?php echo $lang['ERROR_POST_AGREE_TERMS']; ?>\n';
	}
	
	/* Begin Version 5.0 */
	<?php 
	if(count($xsubcatfields)) {
		foreach($xsubcatfields as $fldnum=>$fld) {
		    if ($fld['REQUIRED']) {
	?>
	
	            if (!value_missing && !form.elements['x[<?php echo $fldnum; ?>]'].value) {
            		msg = '<?php echo $lang['ERROR_POST_FILL_ALL']; ?>\n' + msg;
            		value_missing = true;
	            }
	            
	<?php
	        }
	    }
	}
	?>
	/* End Version 5.0 */

	if (msg != '') {
		alert(msg);
		return false;
	}
}
</script>
<!-- End Version 5.0 -->

<script language="javascript">
function confirmdel()
{
	if(confirm("<?php echo $lang['EDIT_AD_CONFIRM_DELETE']; ?>"))
	{
		location.href = "?view=edit&do=delete&adid=<?php echo $adid; ?>&cityid=<?php echo $xcityid; ?>";
	}
}
</script>

<h2><?php echo $lang['EDIT_AD']; ?> "<?php echo $data['adtitle']; ?>"</h2>

<?php if($err) echo "<div class=\"err\">$err</div>"; ?>


<table><tr><td>

<form action="index.php?<?php echo $qs; ?>" method="post" name="frmPost" enctype="multipart/form-data"
	onsubmit="return checkPostFields(this);">	<!-- Version 5.0 -->


<table class="postad" cellspacing="0" cellpadding="0" border="0" width="100%">

	<tr>
		<td colspan="2">
		
			<b><?php echo $lang['POST_ADTITLE']; ?>:</b> <br><input name="adtitle" type="text" id="adtitle" size="80" maxlength="100" value="<?php echo $data['adtitle']; ?>">&nbsp;

		</td>
	</tr>

	<tr><td>&nbsp;</td></tr>

	<tr>
		<td colspan="2">

			<b><?php echo $lang['POST_LOCATION']; ?>:</b><br>


			<?php
			if($location_sort) $sort = "ORDER BY areaname";
            else $sort = "ORDER BY pos";
    
			$sql = "SELECT areaname FROM $t_areas WHERE cityid = $xcityid  $sort";
			$res = mysql_query($sql);
			if (mysql_num_rows($res))
			{
			?>

			<select name="arealist" onchange="javascript:if(this.value) { this.form.area.value=this.value; this.form.area.disabled=true; } else this.form.area.disabled=false;">

			<?php
				$other_index = 1;
				while ($row = mysql_fetch_array($res))
				{
					$other_index++;
					echo "<option value=\"$row[areaname]\"";
					if ($data['area'] == $row['areaname']) { echo " selected"; $area_inlist = TRUE; }
					echo ">$row[areaname]</option>";
				}
			?>

			<option value="" <?php if(!$area_inlist) echo "selected"; ?>>(<?php echo $lang['OTHER']; ?>)</option>
			</select>

			<?php echo $lang['OR_SPECIFY']; ?>

			<input name="area" type="text" size="40" maxlength="50" value="<?php echo $data['area']; ?>" onKeyUp="javascript:if(this.form.arealist.selectedIndex!=<?php echo $other_index; ?>) this.form.arealist.selectedIndex=<?php echo $other_index; ?>;" <?php if($area_inlist) echo "disabled"; ?>>

			<?php
			}
			else
			{
			?>

			<input name="area" type="text" size="40" maxlength="50" value="<?php echo $data['area']; ?>">

			<?php
			}
			?>
			
		</td>
	</tr>

	<tr><td colspan="2">&nbsp;</td></tr>


    <!-- Begin Version 5.0 -->
    
	<tr>
		<td valign="top" colspan="2"><b><?php echo $lang['POST_CONTENTS']; ?>:</b> <span class="marker">*</span><br>
		
        <?php 
        if(richTextAllowed($data['createdon_ts'])) { 
            $wmd_editor = array("name"=>"addesc", "content"=>$data['addesc']);
            include("{$path_escape}editor/wmd_editor.inc.php"); 
            
        } else {
        ?>
        
        <textarea name="addesc" cols="78" rows="10" id="addesc"><?php echo $data['addesc']; ?></textarea><br>
        
        <?php 
        } 
        ?>
        
		</td>
	</tr>
	
    <!-- End Version 5.0 -->
	
	<?php
	if ($hasprice)
	{
	?>
	
	<tr>
		<td><b><?php echo $pricelabel; ?>:</b></td>
		<td><?php echo $currency; ?> <input type="text" name="price" size="5" maxlength="10" value="<?php echo $data['price']; ?>"></td>
	</tr>


	<?php
	}
	?>



	<?php foreach($xsubcatfields as $fldnum=>$fld) { ?>
	<tr>
		<!-- Begin Version 5.0 -->
		<td valign="top"><b><?php echo $fld['NAME']; ?>: </b>
		<?php if ($fld['REQUIRED']) { ?>
		<span class="marker">*</span>
		<?php } ?>
		</td>
	    <!-- End Version 5.0 -->

		<td>
		
		<?php

		switch($fld['TYPE'])
		{
			case "N":

		?>

			<input name="x[<?php echo $fldnum; ?>]" type="text" size="8" value="<?php echo $data['x'][$fldnum]; ?>">

		<?php

			break;

			case "D":

		?>

			<select name="x[<?php echo $fldnum; ?>]">
			<?php
			foreach ($fld['VALUES_A'] as $v)
			{
				echo "<option value=\"$v\"";
				if ($data['x'][$fldnum] == $v) echo " selected";
				echo ">$v</option>";
			}
			?>
			</select>

		<?php

			break;
			
			default:

		?>

			<input name="x[<?php echo $fldnum; ?>]" type="text" size="30" value="<?php echo $data['x'][$fldnum]; ?>">

		<?php

			break;

		}

		?>	
		
		</td>
	</tr>

	<?php } ?>



	<?php
	if($isevent)
	{
	?>

		<tr>

			<td><b><?php echo $lang['POST_EVENT_START']; ?>:</b> <span class="marker">*</span></td>
			<td>
			
			<select name="fm">
			<?php echo $mlist; ?>
			</select>
			
			<select name="fd">
			<?php echo $dlist; ?>
			</select> , 
			
			<select name="fy">
			<?php echo $ylist; ?>
			</select>
			
			</td>
			</tr>


		<tr>
			<td><b><?php echo $lang['POST_EVENT_END']; ?>: </b><span class="marker">*</span></td>
			<td>
			
			<select name="tm">
			<?php echo $mlist; ?>
			</select>
			
			<select name="td">
			<?php echo $dlist; ?>
			</select> , 
			
			<select name="ty">
			<?php echo $ylist; ?>
			</select>
							
			</td>
		</tr>
			
		<?php
		if ($data['fm'])	// Version 5.0
		{
		?>

			<script language="javascript">

			document.frmPost.fm.options[<?php echo $data['fm']-1; ?>].selected = true;
			document.frmPost.fd.options[<?php echo $data['fd']-1; ?>].selected = true;
			/* Begin Version 5.0 */
			document.frmPost.fy.options[<?php echo $data['fy']-$starty; ?>].selected = true;
			/* End Version 5.0 */
			document.frmPost.tm.options[<?php echo $data['tm']-1; ?>].selected = true;
			document.frmPost.td.options[<?php echo $data['td']-1; ?>].selected = true;
			/* Begin Version 5.0 */
			document.frmPost.ty.options[<?php echo $data['ty']-$starty; ?>].selected = true;
            /* End Version 5.0 */

			</script>

		<?php
		}
		?>

	<?php
	}
	?>

	<tr><td colspan="2">&nbsp;</td></tr>

	<tr>
		<td valign="top" width="20%"><b><?php echo $lang['POST_YOUREMAIL']; ?>:</b> <span class="marker">*</span> </td><td>

		<?php echo $data['email']; ?>
		
		<input name="email" type="hidden" id="email" size="30" maxlength="50" value="<?php echo $data['email']; ?>">

		<table border="0" cellspacing="1" cellpadding="0">
		<tr>
			<td><input name="showemail" type="radio" value="0" <?php if($data['showemail']==EMAIL_HIDE) echo "checked"; ?>></td>
			<td><?php echo $lang['POST_EMAILOPTION_HIDE']; ?></td>
			</tr>
		<tr>
			<td><input name="showemail" type="radio" value="2" <?php if($data['showemail']==EMAIL_USEFORM) echo "checked"; ?>></td>
			<td><?php echo $lang['POST_EMAILOPTION_USEFORM']; ?></td>
			</tr>
		<tr>
			<td><input name="showemail" type="radio" value="1" <?php if($data['showemail']==EMAIL_SHOW) echo "checked"; ?>>&nbsp;</td>
			<td><?php echo $lang['POST_EMAILOPTION_SHOW']; ?></td>
			</tr>
		</table>
		</td>
	</tr>

</table>
<br>


<table class="postad" cellspacing="0" cellpadding="0" border="0" width="100%">

	<tr>
		<td><b><?php echo $lang['POST_EDIT_PICTURES']; ?>:</b><br>
		
		
		<?php /* ?>
		<span class="hint">
		<?php echo $lang['POST_EDIT_PICTURES_HINT']; ?><br>
		<?php echo $lang['POST_MAX_PIC_FILESIZE']; ?>: <?php echo $pic_maxsize; ?>KB
		</span><br>
		<?php
		for ($i=1; $i<=$pic_count; $i++)
		{	
		?>
			<input type="file" name="pic[]" size="59"><br>
			<img src="images/spacer.gif" height="2"><br>
		<?php
		}
		?>
		<?php */ ?>


<table cellspacing="2" cellpadding="0" border="0">

<?php

$sql = "SELECT * FROM $t_adpics WHERE adid = $adid AND isevent = '$isevent'";
$pics = mysql_query($sql);
$pics_present = mysql_num_rows($pics);

if($pics_present)
{
	while($pic = mysql_fetch_array($pics))
	{

?>

	<tr><td>
	<img src="<?php echo $datadir['adpics']; ?>/<?php echo $pic['picfile']; ?>" border="1">
	</td><td>
	<input type="checkbox" name="delpic[]" value="<?php echo $pic['picid']; ?>"> 
	<span class="extracaution"><?php echo $lang['BUTTON_DELETE_AD']; ?></span>
	</td></tr>

	<tr><td colspan="2">&nbsp;</td></tr>

<?php
	}
}
?>

</table>

<br> <br>

<?php
if($pics_present < $pic_count)
{
?>

<b><?php echo $lang['POST_UPLOAD_PICTURES']; ?>:</b><br>
<span class="hint"><?php echo $lang['POST_MAX_PIC_FILESIZE']; ?>: <?php echo $pic_maxsize; ?>KB</span><br>

<?php
	for($i=$pics_present+1; $i<=$pic_count; $i++)
	{
?>

		<input type="file" name="pic[]" size="69"><br>

<?php
	}
?>

<br> <br>

<?php
}
?>

<br> 


		</td>
	</tr>

</table>
<br>


<table class="postad" cellspacing="0" cellpadding="0" border="0" width="100%">

	<tr>
		<td>
		<input type="checkbox" name="othercontactok" value="1" <?php if($data['othercontactok'] == 1) echo "checked"; ?>> <?php echo $lang['POST_COMMERCIAL_CONTACT']; ?>
		</td>
	</tr>

<tr><td colspan="2">&nbsp;</td></tr>

	<tr>

		<td>

		<input type="checkbox" name="newsletter" value="1" <?php if($data['newsletter'] == 1) echo "checked"; ?>> <?php echo $lang['POST_NEWSLETTER_OPTION']; ?>

		</td>

	</tr>

</table>
<br>


<input type="checkbox" name="agree" value="1"><?php echo $lang['POST_ACCEPT_TERMS']; ?><br><br>

<?php if($isevent) { ?>
	<input name="isevent" type="hidden" id="isevent" value="1">
<?php } else { ?>
	<input name="subcatid" type="hidden" id="subcatid" value="<?php echo $data['subcatid']; ?>">
<?php } ?>

<input name="do" type="hidden" id="do" value="post">
<input name="adid" type="hidden" value="<?php echo $adid; ?>">
<input name="isevent" type="hidden" value="<?php echo $isevent; ?>">

<button type="submit"><?php echo $lang['BUTTON_UPDATE_AD']; ?></button>
<button type="button" onclick="javascript:confirmdel();"><?php echo $lang['BUTTON_DELETE_AD']; ?></button></td>


</form>

</td></tr></table>



<?php

	}

	}
}

?>