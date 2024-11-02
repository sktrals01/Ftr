<?php


require_once("initvars.inc.php");
require_once("config.inc.php");
require_once("pager.cls.php");
$sql = "select alert from $t_cats where catid='$xcatid'";
	$res = mysql_query($sql) or die($sql.mysql_error());
	list($alert) = mysql_fetch_array($res);

if ($alert == 1) {
	/* If no cookie */
	if (!$_COOKIE["ck_adultverified"]) {
		header("Location: $script_url/verificaiton_checkpoint.php?catid=$xcatid&cityid=$xcityid");
	}
}

// Pager
$page = $_GET['page'] ? $_GET['page'] : 1;
$offset = ($page-1) * $ads_per_page;

if ($sef_urls && !$xsearchmode)
{
	if ($xview == "events")
	{
        /* Begin Version 5.0 */
        $urlformat = buildURL('events', array($xcityid, $xdate, "{@PAGE}"));
        /* End Version 5.0 */
	}
	else
	{
	    /* Begin Version 5.0 */
	    /* Begin Version 5.1 - Uniform page links */
	    $urlformat = buildURL('ads', array($xcityid, $xcatid, $xcatname, $xsubcatid, $xsubcatname, "{@PAGE}"));
	    /* End Version 5.1 - Uniform page links */
	    /* End Version 5.0 */
	}
}
else
{
	/* Begin Version 5.0 */
	$excludes = array('page','msg');
	$urlformat = regenerateURL($excludes) . "page={@PAGE}";
	/* End Version 5.0 */
}


if ($xview == "events")
{
	$where = "";

	if ($xsearch)
	{
		$searchsql = mysql_escape_string($xsearch);
        
        /* Begin Version 5.0 */
        if ($use_regex_search) {
            $where = "(a.adtitle RLIKE '[[:<:]]{$searchsql}[[:>:]]' OR a.addesc RLIKE '[[:<:]]{$searchsql}[[:>:]]')";
        } else {
            $where = "(a.adtitle LIKE '$searchsql' OR a.addesc LIKE '$searchsql')";
        }
        
        $where .= " AND a.endon >= NOW()";
        /* End Version 5.0 */
	}
	else if ($xdate)
	{
		$where = "(starton <= '$xdate' AND endon >= '$xdate')";
	}
	else
	{
		$where = "endon >= NOW()";		// Version 5.0
	}

	if($_GET['area']) $where .= "AND a.area = '$_GET[area]'";

	
	if ($xsearchmode)
	{
		$sort = "a.starton ASC";
	}
	else
	{
		$sort = "a.starton DESC";
	}


	// Get count
	$sql = "SELECT COUNT(*) AS adcount
			FROM $t_events a
				INNER JOIN $t_cities ct ON a.cityid = ct.cityid
				LEFT OUTER JOIN $t_featured feat ON a.adid = feat.adid AND feat.adtype = 'E'
			WHERE $where
				AND $visibility_condn
				AND (feat.adid IS NULL OR feat.featuredtill < NOW())
				$loc_condn";
	$tmp = mysql_query($sql) or die($sql.mysql_error());
	list($adcount) = mysql_fetch_array($tmp);

	// Get results
	$sql = "SELECT a.*, COUNT(*) AS piccount, p.picfile,
				UNIX_TIMESTAMP(a.createdon) AS timestamp, ct.cityname,
				UNIX_TIMESTAMP(a.starton) AS starton, UNIX_TIMESTAMP(a.endon) AS endon			
			FROM $t_events a
				INNER JOIN $t_cities ct ON a.cityid = ct.cityid
				LEFT OUTER JOIN $t_adpics p ON a.adid = p.adid AND p.isevent = '1'
				LEFT OUTER JOIN $t_featured feat ON a.adid = feat.adid AND feat.adtype = 'E'
			WHERE $where
				AND $visibility_condn
				AND (feat.adid IS NULL OR feat.featuredtill < NOW())
				$loc_condn
			GROUP BY a.adid
			ORDER BY $sort
			LIMIT $offset, $ads_per_page";
	$res = mysql_query($sql) or die($sql.mysql_error());

	// Get featured events
	$sql = "SELECT a.*, COUNT(*) AS piccount, p.picfile,
				UNIX_TIMESTAMP(a.createdon) AS timestamp, ct.cityname,
				UNIX_TIMESTAMP(a.starton) AS starton, UNIX_TIMESTAMP(a.endon) AS endon
			FROM $t_events a
				INNER JOIN $t_featured feat ON a.adid = feat.adid AND feat.adtype = 'E' AND feat.featuredtill >= NOW()
				INNER JOIN $t_cities ct ON a.cityid = ct.cityid
				LEFT OUTER JOIN $t_adpics p ON a.adid = p.adid AND p.isevent = '1'
			WHERE $where
				AND $visibility_condn
				$loc_condn
			GROUP BY a.adid
			ORDER BY $sort";
	$featres = mysql_query($sql) or die(mysql_error().$sql);
	
	// Vars
	$adtable = $t_events;
	$adtype = "E";
	$target_view = "showevent";
	$target_view_sef = "events";
	//$page_title = "Events";
	if ($_GET['date']) $link_extra = "&amp;date=$xdate";
	else $find_date = TRUE;

}
else
{
	// Make up the sql query
	$whereA = array();

	if ($xsearch)
	{
			    /* Begin Version 5.7 - Improved search */
	    $search_terms = separeteSearchTerms($xsearch);
	    $or_conditions = array();
	    
	    foreach($search_terms as $term)
	    {
    		$searchsql = mysql_escape_string($term);
            /* Begin Version 5.0 */
    		if ($use_regex_search) {
                $or_conditions[] .= "a.adtitle RLIKE '[[:<:]]{$searchsql}[[:>:]]' OR a.addesc RLIKE '[[:<:]]{$searchsql}[[:>:]]'";
            } else {
                $or_conditions[] = "a.adtitle LIKE '%$searchsql%' OR a.addesc LIKE '%$searchsql%'";
            }
            /* End Version 5.0 */
        }
        
        $combined_clause = "(" . implode(" OR ", $or_conditions) . ")";
        $whereA[] = $combined_clause;
        /* End Version 5.7 - Improved search */
	}

	if($_GET['area']) $whereA[] = "a.area = '$_GET[area]'";

	if ($xsubcathasprice && $_GET['pricemin'])
	{
		$whereA[] = "a.price >= $_GET[pricemin]";
	}

	if ($xsubcathasprice && $_GET['pricemax'])
	{
		$whereA[] = "a.price <= $_GET[pricemax]";
	}

	if ($xsubcatid)		$whereA[] = "a.subcatid = $xsubcatid";
	else if ($xcatid)	$whereA[] = "scat.catid = $xcatid";

	if (count($_GET['x']))
	{
		foreach ($_GET['x'] as $fldnum=>$val)
		{
			// Ensure numbers
			$fldnum += 0;
			/* Begin Version 5.1 */
			if ($val === "" || !$fldnum) continue;
			/* End Version 5.1 */

			if($xsubcatfields[$fldnum]['TYPE'] == "N" && is_array($val))
			{
				numerize($val['min']); numerize($val['max']);	// Sanitize
				if($val['min']) $whereA[] = "axf.f{$fldnum} >= $val[min]";
				if($val['max']) $whereA[] = "axf.f{$fldnum} <= $val[max]";
			}
			elseif($xsubcatfields[$fldnum]['TYPE'] == "D") 
			{
				$whereA[] = "axf.f{$fldnum} = '$val'";
			}
			else
			{
				$whereA[] = "axf.f{$fldnum} LIKE '%$val%'";
			}
		}
	}

	$where = implode(" AND ", $whereA);
	if (!$where) $where = "1";

	// Get count
	$sql = "SELECT COUNT(*) AS adcount
			FROM $t_ads a
				INNER JOIN $t_cities ct ON a.cityid = ct.cityid
				INNER JOIN $t_subcats scat ON a.subcatid = scat.subcatid
				INNER JOIN $t_cats cat ON scat.catid = cat.catid
				LEFT OUTER JOIN $t_adxfields axf ON a.adid = axf.adid
				LEFT OUTER JOIN $t_featured feat ON a.adid = feat.adid AND feat.adtype = 'A'
			WHERE $where
				AND $visibility_condn
				AND (feat.adid IS NULL OR feat.featuredtill < NOW())
				$loc_condn";
			
	$tmp = mysql_query($sql) or die(mysql_error());
	list($adcount) = mysql_fetch_array($tmp);

	// List of extra fields
	$xfieldsql = "";
	if(count($xsubcatfields)) 
	{
		for($i=1; $i<=$xfields_count; $i++)	$xfieldsql .= ", axf.f$i";
	}

	// Get results
	$sql = "SELECT a.*, UNIX_TIMESTAMP(a.createdon) AS timestamp, ct.cityname,
				COUNT(*) AS piccount, p.picfile,
				scat.subcatname, cat.catid, cat.catname $xfieldsql
			FROM $t_ads a
				INNER JOIN $t_cities ct ON a.cityid = ct.cityid
				INNER JOIN $t_subcats scat ON a.subcatid = scat.subcatid
				INNER JOIN $t_cats cat ON scat.catid = cat.catid
				LEFT OUTER JOIN $t_adxfields axf ON a.adid = axf.adid
				LEFT OUTER JOIN $t_adpics p ON a.adid = p.adid AND p.isevent = '0'
				LEFT OUTER JOIN $t_featured feat ON a.adid = feat.adid AND feat.adtype = 'A'
			WHERE $where
				AND $visibility_condn
				AND (feat.adid IS NULL OR feat.featuredtill < NOW())
				$loc_condn
			GROUP BY a.adid
			ORDER BY a.createdon DESC
			LIMIT $offset, $ads_per_page";
	$res = mysql_query($sql) or die($sql.mysql_error());

	// Get featured ads
	$sql = "SELECT a.*, UNIX_TIMESTAMP(a.createdon) AS timestamp, ct.cityname,
				COUNT(*) AS piccount, p.picfile,
				scat.subcatname, cat.catid, cat.catname $xfieldsql
			FROM $t_ads a
				INNER JOIN $t_featured feat ON a.adid = feat.adid AND feat.adtype = 'A' AND feat.featuredtill >= NOW()
				INNER JOIN $t_cities ct ON a.cityid = ct.cityid
				INNER JOIN $t_subcats scat ON a.subcatid = scat.subcatid
				INNER JOIN $t_cats cat ON scat.catid = cat.catid
				LEFT OUTER JOIN $t_adxfields axf ON a.adid = axf.adid
				LEFT OUTER JOIN $t_adpics p ON a.adid = p.adid AND p.isevent = '0'
			WHERE $where
				AND $visibility_condn
				$loc_condn
			GROUP BY a.adid
			ORDER BY feat.timestamp DESC";
	$featres = mysql_query($sql) or die(mysql_error().$sql);
	$featadcount = mysql_num_rows($featres);

	// Vars
	$adtable = $t_ads;
	$adtype = "A";
	$target_view = "showad"; 
	$target_view_sef = "posts"; 
	//$page_title = ($xsubcatname ? $xsubcatname : $xcatname);

}

$pager = new pager($urlformat, $adcount, $ads_per_page, $page);

/* Begin Version 5.0 */
if ($xsubcatname) $page_head = $xsubcatname;
elseif ($xcatname) $page_head = $xcatname;

if ($xsearchmode) $page_head = $lang['SEARCH'] . ($page_head ? ": $page_head" : "");
/* End Version 5.0 */
	

if ($xview == "events" && !$xsearchmode)
{
	// Calendar navigation
	$prevmonth = mktime(0, 0, 0, $xdate_m-1, $xdate_d, $xdate_y);
	$nextmonth = mktime(0, 0, 0, $xdate_m+1, $xdate_d, $xdate_y);
	$prevday = $xdatestamp - 24*60*60;
	$nextday = $xdatestamp + 24*60*60;
?>
<table width="100%" class="eventnav" border="0"><tr>

<td valign="bottom">
<?php 
/* Begin Version 5.0 */
$prevday_url = buildURL("events", array($xcityid, date("Y-m-d", $prevday)));
$nextday_url = buildURL("events", array($xcityid, date("Y-m-d", $nextday)));
/* End Version 5.0 */
?>
<a href="<?php echo $prevday_url; ?>">
<?php echo $lang['EVENTS_PREVDAY']; ?></a>
</td>

<td align="center">
<b><?php echo QuickDate($xdatestamp, FALSE, FALSE); ?> </b>
</td>

<td align="right" valign="bottom">
<a href="<?php echo $nextday_url; ?>">
<?php echo $lang['EVENTS_NEXTDAY']; ?></a>
</td>

</tr></table>
<?php
}
?>

<?php 
if(!$show_sidebar_always)
{
?>

	<div id="search_top0">
	<?php include("search.inc.php"); ?>
	

<?php
}
?>
<?php
/* Begin Version 5.7 - City filter */
if ($xcountryid > 0 && !$postable_country)
{
	$sort = $location_sort ? "cityname" : "pos";
    $sql = "select * from $t_cities where countryid = $xcountryid ORDER BY {$sort}";
    $cities_res = mysql_query($sql);
    
    if (mysql_num_rows($cities_res) > 0)
    { 
        $base_link = regenerateURL(array("page", "cityid", "area"));
        $region_link = "{$base_link}cityid=-{$xcountryid}";
?>
 <div style="border: 1px solid #a6b288; padding: 5px; border-radius: 6px;text-align: center; margin: 5px;" id="superRegionNavMenu" style="margin-top:1em; font-size:13px;">
            <b><?php echo $lang['SHOWING_ADS_IN']; ?></b>
            <?php if ($xcityid < 0) { ?>
                <span class="active"><?php echo $xcountryname; ?></span>
            <?php } else { ?>
                <a href="<?php echo $region_link; ?>" class="inactive"><?php echo $xcountryname; ?></a>
            <?php } ?>
<?php
    
    
        while ($city = mysql_fetch_assoc($cities_res))
        {
            if ($city['cityid'] == $xcityid)
            {
?>
            | <span class="active"><?php echo $city['cityname']; ?></span>
<?php
            }
            else
            {
                $city_link = "{$base_link}cityid={$city['cityid']}";
?>
            | <a href="<?php echo $city_link; ?>" class="inactive"><?php echo $city['cityname']; ?></a>
<?php
            }
        }
?>
        </div><br />
<?php
    }
}
/* End Version 5.7 - City filter */
?>

<?php

if ($adcount || mysql_num_rows($featres)>0)
{

?>


<table border="0" cellspacing="0" cellpadding="0" width="100%" class="postlisting"> <!-- Version 5.0 -->
<style>
a:link {
  color: #0159f1;
  background-color: transparent;
  text-decoration: none;
}
a:visited {
  color: SlateGray;
  background-color: transparent;
  text-decoration: none;
}
a:hover {
  color: red;
  background-color: transparent;
  text-decoration: underline;
}
</style>
<?php

if($xview == "events")
{

?>
<tr class="head">
<td><?php echo $lang['EVENTLIST_EVENTTITLE']; ?></td>
<td align="center" width="15%"><?php echo $lang['EVENTLIST_STARTSON']; ?></td>
<td align="center" width="15%"><?php echo $lang['EVENTLIST_ENDSON']; ?></td>
</tr>

<?php

// Featured events
if (mysql_num_rows($featres)>0)
{

	$css_first = "_first";

	while($row=mysql_fetch_array($featres))
	{
		if ($find_date) 
		{
			$link_extra = "&date=".date("Y-m-d", $row['starton']);
			$urldate = date("Y-m-d", $row['starton']);
		}

        /* Begin Version 5.0 */
		$url = buildURL($target_view, array($xcityid, $urldate, $row['adid'], $row['adtitle']));
		/* End Version 5.0 */

?>

		<tr class="featuredad<?php echo $css_first; ?>">
			<td>
			<a href="<?php echo $url; ?>" class="posttitle">	<!-- Version 5.0 -->
			<?php 
			if($row['picfile'] && $ad_thumbnails) 
			{ 
				$imgsize = GetThumbnailSize("{$datadir[adpics]}/{$row[picfile]}", $tinythumb_max_width, $tinythumb_max_height);
			?>
				<img src="<?php echo "$datadir[adpics]/$row[picfile]"; ?>" border="0" width="<?php echo $imgsize[0]; ?>" height="<?php echo $imgsize[1]; ?>" align="left" style="border:1px solid black;margin-right:5px;"> 
			<?php 
			}
			?>
			<img src="images/featured.gif" align="absmiddle" border="0">
			
			<?php echo $row['adtitle']; ?></a>

			<?php 
			$loc = "";
			if($row['area']) $loc = $row['area'];
			if($xcityid <= 0) $loc .= ($loc ? ", " : "") . $row['cityname'];	// Version 5.0
			if($loc) echo "($loc)";
			?>			<?php if($row['picfile']) { ?><img src="images/adwithpic.gif" align="absmiddle" title="This ad has picture(s)"><?php } ?>

						
			<?php 
			if($ad_preview_chars) 
			{ 
				echo "<span class='adpreview'>";
				/* Begin Version 5.7 - Incorrect text wrap fix */

				echo generateBrief($row['addesc']);

				/* End Version 5.7 - Incorrect text wrap fix */
				echo "</span>";
			} 
			?>


			</td>
			<!-- Begin Version 5.0 -->
			<td align="center"><?php echo $langx['months_short'][date("n", $row['starton'])-1] . " " . date("j", $row['starton']) . ", " . date("y", $row['starton']); ?></td>	
			<td align="center"><?php if($row['starton'] != $row['endon']) echo $langx['months_short'][date("n", $row['endon'])-1] . " " . date("j", $row['endon']) . ", " . date("y", $row['endon']);	?>
			<!-- End Version 5.0 -->

			
			</td>

		</tr>

<?php

		$css_first = "";
	}

}

?>


<?php

	$i = 0;
	while($row=mysql_fetch_array($res))
	{
			    /* Begin Version 5.6.3 - AdBlock fix */
		$css_class = "post" . (($i%2)+1);
		/* End Version 5.6.3 - AdBlock fix */
		$i++;

	?>

		<tr class="<?php echo $css_class; ?>">

			<td>
				
				<?php

				if ($find_date) 
				{
					$link_extra = "&date=".date("Y-m-d", $row['starton']);
					$urldate = date("Y-m-d", $row['starton']);
				}

                /* Begin Version 5.0 */
                $url = buildURL($target_view, array($xcityid, $urldate, $row['adid'], $row['adtitle']));
                /* End Version 5.0 */

				?>

				<a href="<?php echo $url; ?>" class="posttitle">	<!-- Version 5.0 -->


				<?php 
				if($row['picfile'] && $ad_thumbnails) 
				{ 
					$imgsize = GetThumbnailSize("{$datadir[adpics]}/{$row[picfile]}", $tinythumb_max_width, $tinythumb_max_height);
				?>
					<img src="<?php echo "$datadir[adpics]/$row[picfile]"; ?>" border="0" width="<?php echo $imgsize[0]; ?>" height="<?php echo $imgsize[1]; ?>" align="left" style="border:1px solid black;margin-right:5px;"> 
				<?php 
				}
				?>


				<?php echo $row['adtitle']; ?></a>

				<?php 
				$loc = "";
				if($row['area']) $loc = $row['area'];
				if($xcityid <= 0) $loc .= ($loc ? ", " : "") . $row['cityname'];	// Version 5.0
				if($loc) echo "($loc)";
				?>				<?php if($row['picfile']) echo "<img src=\"images/adwithpic.gif\" align=\"absmiddle\" title=\"This ad has picture(s)\"> "; ?>

				
							
    			<?php 
    			if($ad_preview_chars) 
    			{ 
    				echo "<span class='adpreview'>";
    				/* Begin Version 5.7 - Incorrect text wrap fix */

    				echo generateBrief($row['addesc']);

    				/* End Version 5.7 - Incorrect text wrap fix */
    				echo "</span>";
    			} 
    			?>


			</td>
			<!-- Begin Version 5.0 -->
			<td align="center"><?php echo $langx['months_short'][date("n", $row['starton'])-1] . " " . date("j", $row['starton']) . ", " . date("y", $row['starton']); ?></td>
			<td align="center"><?php if($row['starton'] != $row['endon']) echo $langx['months_short'][date("n", $row['endon'])-1] . " " . date("j", $row['endon']) . ", " . date("y", $row['endon']);	?>
			<!-- End Version 5.0 --></td>

		</tr>

	<?php

	}
}
else
{

?>
<tr class="head">
<!--<td><?php echo $lang['ADLIST_ADTITLE']; ?></td>-->
<?php
$colspan = 1;
foreach ($xsubcatfields as $fldnum=>$fld)
{
	if (!$fld['SHOWINLIST']) continue;

	echo "<td";
	//if ($fld['TYPE']=="N") 
	echo " align=\"center\"";
	echo ">$fld[NAME]</td>";
	$colspan++;
}
if ($xsubcathasprice) 
{
	echo "<td align=\"right\" width=\"12%\">$xsubcatpricelabel</td>";
	$colspan++;
}
?>
</tr>

<?php

// Featured ads
if (mysql_num_rows($featres)>0)
{
	
	echo "<tr><td height=\"1\"></td></tr>";
	$css_first = "_first";

	while($row=mysql_fetch_array($featres))
	{
		
		/* Begin Version 5.0 */
		$url = buildURL($target_view, array($xcityid, $row['catid'], $row['catname'], 
		    $row['subcatid'], $row['subcatname'], $row['adid'], $row['adtitle']));
		/* End Version 5.0 */

?>

		<tr class="featuredad<?php echo $css_first; ?>">
			<td>
			<a href="<?php echo $url; ?>" class="posttitle">	<!-- Version 5.0 -->


			<?php 
			if($row['picfile'] && $ad_thumbnails) 
			{ 
				$imgsize = GetThumbnailSize("{$datadir[adpics]}/{$row[picfile]}", $tinythumb_max_width, $tinythumb_max_height);
			?>
				<img src="<?php echo "$datadir[adpics]/$row[picfile]"; ?>" border="0" width="<?php echo $imgsize[0]; ?>" height="<?php echo $imgsize[1]; ?>" align="left" style="border:1px solid black;margin-right:5px;"> 
			<?php 
			}
			?>



			<img src="images/featured.gif" align="absmiddle" style="padding-right:5px;" border="0"><?php echo $row['adtitle']; ?></a>
			<?php 
			$loc = "";
			if($row['area']) $loc = $row['area'];
			if($xcityid <= 0) $loc .= ($loc ? ", " : "") . $row['cityname'];	// Version 5.0
			if($loc) echo "<span class=\"resultsRegionLabel\" style=\"font-size:10px;\">($loc)</span>";
			?>			<?php if($row['picfile']) { ?><img src="images/adwithpic.gif" align="absmiddle" title="This ad has Picture(s)"><?php } ?>

							
			<?php 
			if($ad_preview_chars) 
			{ 
				echo "<span class='adpreview'>";
				/* Begin Version 5.7 - Incorrect text wrap fix */

				echo generateBrief($row['addesc']);

				/* End Version 5.7 - Incorrect text wrap fix */
				echo "</span>";
			} 
			?>


			</td>

			<?php

			foreach ($xsubcatfields as $fldnum=>$fld)
			{
				if (!$fld['SHOWINLIST']) continue;

				echo "<td";
				//if ($fld['TYPE']=="N")
				echo " align=\"center\"";
				echo "width=\"10%\">&nbsp;".
					((($fld['TYPE']=="N" && ($row["f$fldnum"]==-1 || $row["f$fldnum"]=="0" || $row["f$fldnum"]=="")) || ($fld['TYPE']!="N" && trim($row["f$fldnum"])==""))?"-":$row["f$fldnum"])."</td>";
			}

			if($xsubcathasprice) 
				echo "<td align=\"right\">&nbsp;".($row['price'] > 0.00?"$currency".$row['price']:"-")."</td>";
			
			?>

		</tr>

<?php

		$css_first = "";
	
	}

}

?>

<?php

	$i = $j = 0;
	$lastdate = "";
	while($row=mysql_fetch_array($res))
	{
		$date_formatted = date("Ymd", $row['timestamp']);
		if($date_formatted != $lastdate)
		{
			if ($lastdate) 
			{
				//echo "<tr><td height=\"1\"></td></tr>";
				$j = 0;
			}

			echo "<tr><td height=\"1\"></td></tr><tr><td class=\"datehead\" colspan=\"$colspan\">".QuickDate($row['timestamp'], FALSE, FALSE)."</td></tr><tr><td height=\"1\"></td></tr>";

			$lastdate = $date_formatted;
		}

		        /* Begin Version 5.6.3 - AdBlock fix */
		$css_class = "post" . (($j%2)+1);
		/* End Version 5.6.3 - AdBlock fix */
		$i++; $j++;		/* Begin Version 5.0 */
		$url = buildURL($target_view, array($xcityid, $row['catid'], $row['catname'], 
		    $row['subcatid'], $row['subcatname'], $row['adid'], $row['adtitle']));
		/* End Version 5.0 */

        /* Begin Version 5.6.3 - Show subcat link only when subcat not specified. */
        $title_extra = "";
        
        /* Begin Version 5.7 - Postable category fix */
		if(!$xsubcatid && !$postable_category)
		/* End Version 5.7 - Postable category fix */
		/* End Version 5.6.3 */
		{
			/* Begin Version 5.0 */
    		$subcatlink = buildURL("ads", array($xcityid, $row['catid'], $row['catname'], 
    		    $row['subcatid'], $row['subcatname']));
    		/* End Version 5.0 */	

			$title_extra = "&nbsp;- <a href=\"$subcatlink\" class=\"adcat\">$row[catname] $path_sep $row[subcatname]</a>";
		}


	?>

		<tr class="<?php echo $css_class; ?>">

			<td>
				
				<a href="<?php echo $url; ?>" class="posttitle">	<!-- Version 5.0 -->

				<?php 
				if($row['picfile'] && $ad_thumbnails) 
				{ 
					$imgsize = GetThumbnailSize("{$datadir[adpics]}/{$row[picfile]}", $tinythumb_max_width, $tinythumb_max_height);
				?>
					<img src="<?php echo "$datadir[adpics]/$row[picfile]"; ?>" border="0" width="<?php echo $imgsize[0]; ?>" height="<?php echo $imgsize[1]; ?>" align="left" style="border:1px solid black;margin-right:5px;"> 
				<?php 
				}
				?>


				<?php echo $row['adtitle']; ?></a>
				<?php 
				$loc = "";
				if($row['area']) $loc = $row['area'];
				if($xcityid <= 0) $loc .= ($loc ? ", " : "") . $row['cityname'];	// Version 5.0
				if($loc) echo "($loc)";
				?>

				<?php if($row['picfile']) echo "<img src=\"images/adwithpic.gif\" align=\"absmiddle\" title=\"This ad has picture(s)\"> "; ?>				<?php echo $title_extra; ?>

				
				<?php 
				if($ad_preview_chars) 
				{ 
					echo "<span class='adpreview'>";
                    /* Begin Version 5.7 - Incorrect text wrap fix */

                    echo generateBrief($row['addesc']);
                    /* End Version 5.7 - Incorrect text wrap fix */
					echo "</span>";
				} 
				?>


			</td><?php

			foreach ($xsubcatfields as $fldnum=>$fld)
			{
				if (!$fld['SHOWINLIST']) continue;

				echo "<td";
				//if ($fld['TYPE']=="N")
				echo " align=\"center\"";
				echo "width=\"10%\">&nbsp;".
					((($fld['TYPE']=="N" && ($row["f$fldnum"]==-1 || $row["f$fldnum"]=="0" || $row["f$fldnum"]=="")) || ($fld['TYPE']!="N" && trim($row["f$fldnum"])==""))?"-":$row["f$fldnum"])."</td>";
			}

			if($xsubcathasprice) 
				echo "<td align=\"right\">&nbsp;".($row['price'] > 0.00?"$currency".$row['price']:"-")."</td>";
			
			?>

		</tr>

	<?php

	}
}
?>

</table>

<?php

if ($adcount > $ads_per_page)
{

?>

<br>
<div align="right">
<table>
<tr><td><b><?php echo $lang['PAGE']; ?>: </b></td><td><?php echo $pager->outputlinks(); ?></td></tr>
</table>
</div>

<?php

}

?>


<?php

}
else
{

?>

<div class="noresults"><?php echo $lang['NO_RESULTS']; ?><br>
<a href="?view=main&cityid=<?php echo $xcityid; ?>"><?php echo $lang['BACK_TO_HOME']; ?></a>
</div>

<?php

}

?>