<?php

require_once("initvars.inc.php");
require_once("config.inc.php");

?>
<table width="100%"><tr><td valign="top" style="white-space: nowrap;">
<?php

// Show city list

if($location_sort) 
{
	$sort1 = "ORDER BY countryname";
	$sort2 = "ORDER BY cityname";
}
else
{
	$sort1 = "ORDER BY c.pos";
	$sort2 = "ORDER BY ct.pos";
}

if ($show_region_adcount || $show_city_adcount)
{
	// First get ads per city and country
	$country_adcounts = array();
	$city_adcounts = array();
	$sql = "SELECT ct.cityid, c.countryid, COUNT(*) as adcnt
			FROM $t_ads a
				INNER JOIN $t_cities ct ON ct.cityid = a.cityid AND ($visibility_condn)
				INNER JOIN $t_countries c ON ct.countryid = c.countryid
			WHERE ct.enabled = '1' AND c.enabled = '1'
			GROUP BY ct.cityid";

	$res = mysql_query($sql) or die(mysql_error().$sql);

	while($row=mysql_fetch_array($res))
	{
		$country_adcounts[$row['countryid']] += $row['adcnt'];
		$city_adcounts[$row['cityid']] += $row['adcnt'];
	}
		/* Begin Version 5.7 - Include event count in total ad count */
	$sql = "SELECT ct.cityid, c.countryid, COUNT(*) as adcnt
				FROM $t_events a
					INNER JOIN $t_cities ct ON ct.cityid = a.cityid AND ($visibility_condn)
					INNER JOIN $t_countries c ON ct.countryid = c.countryid
				WHERE ct.enabled = '1' AND c.enabled = '1'
				GROUP BY ct.cityid";
	$res = mysql_query($sql) or die(mysql_error().$sql);
	
	while($row=mysql_fetch_array($res))
	{
		$country_adcounts[$row['countryid']] += $row['adcnt'];
		$city_adcounts[$row['cityid']] += $row['adcnt'];
	}
	/* End Version 5.7 - Include event count in total ad count */
}

$sql = "SELECT * FROM $t_countries c INNER JOIN $t_cities ct ON c.countryid = ct.countryid AND ct.enabled = '1' WHERE c.enabled = '1' GROUP BY c.countryid $sort1";
$resc = mysql_query($sql);

$country_count = mysql_num_rows($resc);
//$split_at = ($country_count%3?((int)($country_count/3))+2:($country_count/3)+1);
$percol = floor($country_count/$location_cols);
$percolA = array();
for($i=1;$i<=$location_cols;$i++) $percolA[$i]=$percol+($i<=$country_count%$location_cols?1:0);

$i = 0; $j = 0;
$col = 1;
while($country = mysql_fetch_array($resc))
{
    /* Begin Version 5.0 */
    $country_url = buildURL("main", array((0-$country['countryid']), $country['countryname']));
    /* End Version 5.0 */
?>


	<div><a href="<?php echo $country_url; ?>" class="citylist_country"><?php echo $country['countryname']; ?> <?php if($show_region_adcount) echo "(".(0+$country_adcounts[$country['countryid']]).")"; ?></a></div>


	<?php

	if($country['countryid'] == $xcountryid || !$expand_current_region_only)
	{

		$sql = "SELECT * FROM $t_cities ct WHERE countryid = $country[countryid] AND enabled = '1' $sort2";
		$resct = mysql_query($sql);
        
        /* Begin Version 5.0 */
        $citycount = mysql_num_rows($resct);
        /* End Version 5.0 */

		while($city=mysql_fetch_array($resct))
		{        
		    /* Begin Version 5.0 */
    	    if ($shortcut_regions && $citycount == 1 
    	            && $city['cityname'] == $country['countryname']) {
    	        continue;
    	    }
    	    
    	    $city_url = buildURL("main", array($city['cityid'], $city['cityname']));
    	    /* End Version 5.0 */

	?>

			<a href="<?php echo $city_url; ?>" class="citylist_city"><?php echo $city['cityname']; ?> <?php if($show_city_adcount) echo "(".(0+$city_adcounts[$city['cityid']]).")"; ?></a><br>
			
	<?php

		}
	}

	?>


	<?php

	$i++; $j++;
	//if($i%$split_at == 0) echo "</td><td valign=\"top\">";
	if ($j%$percolA[$col]==0 && $i<$country_count) { echo "</td><td valign=\"top\">"; $col++; $j=0; } 

}

?>

</td></tr></table>