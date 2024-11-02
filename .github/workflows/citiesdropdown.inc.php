<?php



require_once("initvars.inc.php");
require_once("config.inc.php");
$location_cols4=1;
$expand_current_region_only_THIS = FALSE;
?>
<SCRIPT LANGUAGE="JavaScript">
function formHandler(form){
var URL = document.form.site.options[document.form.site.selectedIndex].value;
window.location.href = URL;
}
</SCRIPT>
<form name="form">Select : 
<select size="1" name="site" onChange="javascript:formHandler()" style="font-family: Verdana; font-size:10px">
    <option>Location* (Click Here)</option>
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
}

$sql = "SELECT * FROM $t_countries c INNER JOIN $t_cities ct ON c.countryid = ct.countryid AND ct.enabled = '1' WHERE c.enabled = '1' GROUP BY c.countryid $sort1";
$resc = mysql_query($sql);

$country_count = mysql_num_rows($resc);
//$split_at = ($country_count%3?((int)($country_count/3))+2:($country_count/3)+1);
$percol = floor($country_count/$location_cols4);
$percolA = array();
for($i=1;$i<=$location_cols4;$i++) $percolA[$i]=$percol+($i<=$country_count%$location_cols4?1:0);

$i = 0; $j = 0;
$col = 1;
while($country = mysql_fetch_array($resc))
{
	if($sef_urls) $country_url = "{$vbasedir}-$country[countryid]-" . RemoveBadURLChars($country['countryname']) . "/";
	else $country_url = "?cityid=-$country[countryid]&lang=$xlang";

?>


	<option value="<?php echo $script_url; ?>/<?php echo $country_url; ?>" style="background-color: black; font-weight: bold; font-size: 13px; color: white;"><?php echo $country['countryname']; ?></option>


	<?php

	if($country['countryid'] == $xcountryid || !$expand_current_region_only_THIS)
	{

		$sql = "SELECT * FROM $t_cities ct WHERE countryid = $country[countryid] AND enabled = '1' $sort2";
		$resct = mysql_query($sql);

		while($city=mysql_fetch_array($resct))
		{
			if($sef_urls) $city_url = "{$vbasedir}$city[cityid]-" . RemoveBadURLChars($city['cityname']) . "/";
			else $city_url = "?cityid=$city[cityid]&lang=$xlang";

	?>

			<option value="<?php echo $script_url; ?>/<?php echo $city_url; ?>">&nbsp;&nbsp;&#8976;&nbsp;<?php echo $city['cityname']; ?></option>
			
	<?php

		}
	}

	?>


	<?php

	$i++; $j++;
}

?>

    </select>
</form>