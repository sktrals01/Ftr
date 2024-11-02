<?php

require_once("initvars.inc.php");
require_once("config.inc.php");

  if ($_GET['cookie'] == 'reset') {
    setcookie("clf_cityid", "", time()-(60*24*60*60), "/");
    header ("Location: /AllCities");
  }

?>
<html lang="<?php echo $langx['lang']; ?>">
<!-- End Version 5.7 -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
<title><?php echo $page_title; ?></title>
<base href="<?php echo $script_url; ?>/">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="keywords" content="<?php echo $meta_keywords; ?>">
<meta name="description" content="<?php echo $meta_description; ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" type="text/css" href="/assets/frontend/css/home.css">
<link rel="stylesheet" type="text/css" href="/assets/frontend/css/style.css">
<link rel="stylesheet" type="text/css" href="/assets/frontend/css/custom.css">
<!-- Script -->
<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
 
<!-- jQuery UI -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.css" />
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
 
<!-- Bootstrap Css -->
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">

<script defer src="/assets/frontend/js/home.js"></script>
</head>
<body id=home class=backpage cz-shortcut-listen=true>
<div id="mainWrapper">
<?php if ($debug) { echo "DEBUG, Cookie Value = ".$_COOKIE['clf_cityid']." (city id)<br>"; 
      if (isset($_COOKIE['clf_cityid'])) { echo "<br> Cookie City ID is Set<br>"; } else { echo "Cookie City Id is not set"; } 
      } ?> 
<div id="header">
    
      <align><img  src="/images/logo_transparentbg.png" alt="Home"></a></align>
<center><h1 style="color: #1EB032;display: inline;font-size: 20px;">
   </h1>
   
  </center>

      
      <div id="postAnAd"><a href="<?php echo $postlink; ?>">post ad</a></div>
      <div>Choose a location:</div>
    </div>
    <div style="border: 1px solid #a6b288; padding: 10px; border-radius: 6px;text-align: center; margin: 8px;"> <span style="color:#405E8F;"><span style="font-size:17px;">Backpage Alternative ¦ New 2Backpage ¦ Backpage Replacement ¦ Site Similar to Backpage ¦ Alternative to Backpage &nbsp;</span></span><p></p> 
    <center>
 <h2 style="font-weight: normal;font-size: 17px;">
     
        Backpage is the New 3Backpages.com<sup style="font-size: 11px;position: absolute;">®</sup>&nbsp;&nbsp;</h2>
       </center></div>

    
<div id="autocomplete">
<div class="column">
<div class="united-states geoBlock">
<h2 id=unitedStates>United States</h2>
<div class="inner">


            
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

$sql = "SELECT * FROM $t_countries c INNER JOIN $t_cities ct ON c.countryid = ct.countryid AND ct.enabled = '1' WHERE c.enabled = '1' GROUP BY c.countryid $sort1 LIMIT 51";
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


            
            
            
          <div class="geoUnit">
            <h3><a ="<?php echo $country_url; ?>"><?php echo $country['countryname']; ?> <?php if($show_region_adcount) echo "(".(0+$country_adcounts[$country['countryid']]).")"; ?></a></h3>
          
          <ul>


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

      
      
        <li><a href="<?php echo $city_url; ?>"><?php echo $city['cityname']; ?> <?php if($show_city_adcount) echo "(".(0+$city_adcounts[$city['cityid']]).")"; ?></a></li>
			
	<?php

		}
?> </ul> <?php
	}
?> </div> <?php


/*
	$i++; $j++;
	//if($i%$split_at == 0) echo "</td><td valign=\"top\">";
	if ($j%$percolA[$col]==0 && $i<$country_count) { echo "</td><td valign=\"top\">"; $col++; $j=0; } 
*/
}

?>


</div>
</div>
</div>
<div class="column">
<div class="canada geoBlock">
<h2 id="canada">Canada</h2>
<div class="inner">


            
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

$sql = "SELECT * FROM $t_countries c INNER JOIN $t_cities ct ON c.countryid = ct.countryid AND ct.enabled = '1' WHERE c.enabled = '1' GROUP BY c.countryid $sort1 LIMIT 51,11";
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


            
            
            
          <div class="geoUnit">
            <h3><a ="<?php echo $country_url; ?>"><?php echo $country['countryname']; ?> <?php if($show_region_adcount) echo "(".(0+$country_adcounts[$country['countryid']]).")"; ?></a></h3>
          
          <ul>


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

      
      
        <li><a href="<?php echo $city_url; ?>"><?php echo $city['cityname']; ?> <?php if($show_city_adcount) echo "(".(0+$city_adcounts[$city['cityid']]).")"; ?></a></li>
			
	<?php

		}
?> </ul> <?php
	}
?> </div> <?php


/*
	$i++; $j++;
	//if($i%$split_at == 0) echo "</td><td valign=\"top\">";
	if ($j%$percolA[$col]==0 && $i<$country_count) { echo "</td><td valign=\"top\">"; $col++; $j=0; } 
*/
}

?>


</div>
</div>  
</div> 
<div class="column">
<div class="Europe geoBlock">
<h2 id="Europe">Europe</h2>
<div class="inner">


            
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

$sql = "SELECT * FROM $t_countries c INNER JOIN $t_cities ct ON c.countryid = ct.countryid AND ct.enabled = '1' WHERE c.enabled = '1' GROUP BY c.countryid $sort1 LIMIT 62,40";
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


            
            
            
          <div class="geoUnit">
            <h3><a ="<?php echo $country_url; ?>"><?php echo $country['countryname']; ?> <?php if($show_region_adcount) echo "(".(0+$country_adcounts[$country['countryid']]).")"; ?></a></h3>
          
          <ul>


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

      
      
        <li><a href="<?php echo $city_url; ?>"><?php echo $city['cityname']; ?> <?php if($show_city_adcount) echo "(".(0+$city_adcounts[$city['cityid']]).")"; ?></a></li>
			
	<?php

		}
?> </ul> <?php
	}
?> </div> <?php


/*
	$i++; $j++;
	//if($i%$split_at == 0) echo "</td><td valign=\"top\">";
	if ($j%$percolA[$col]==0 && $i<$country_count) { echo "</td><td valign=\"top\">"; $col++; $j=0; } 
*/
}

?>


</div>
</div>  
</div>
<div class="column">
<div class="Australia and Oceania geoBlock">
<h2 id="Australia and Oceania">Australia and Oceania</h2>
<div class="inner">


            
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

$sql = "SELECT * FROM $t_countries c INNER JOIN $t_cities ct ON c.countryid = ct.countryid AND ct.enabled = '1' WHERE c.enabled = '1' GROUP BY c.countryid $sort1 LIMIT 102,3";
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


            
            
            
          <div class="geoUnit">
            <h3><a ="<?php echo $country_url; ?>"><?php echo $country['countryname']; ?> <?php if($show_region_adcount) echo "(".(0+$country_adcounts[$country['countryid']]).")"; ?></a></h3>
          
          <ul>


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

      
      
        <li><a href="<?php echo $city_url; ?>"><?php echo $city['cityname']; ?> <?php if($show_city_adcount) echo "(".(0+$city_adcounts[$city['cityid']]).")"; ?></a></li>
			
	<?php

		}
?> </ul> <?php
	}
?> </div> <?php


/*
	$i++; $j++;
	//if($i%$split_at == 0) echo "</td><td valign=\"top\">";
	if ($j%$percolA[$col]==0 && $i<$country_count) { echo "</td><td valign=\"top\">"; $col++; $j=0; } 
*/
}

?>


</div>
</div>  
</div>
<div class="column">
<div class="Africa geoBlock">
<h2 id="Africa">Africa</h2>
<div class="inner">


            
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

$sql = "SELECT * FROM $t_countries c INNER JOIN $t_cities ct ON c.countryid = ct.countryid AND ct.enabled = '1' WHERE c.enabled = '1' GROUP BY c.countryid $sort1 LIMIT 105,6";
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


            
            
            
          <div class="geoUnit">
            <h3><a ="<?php echo $country_url; ?>"><?php echo $country['countryname']; ?> <?php if($show_region_adcount) echo "(".(0+$country_adcounts[$country['countryid']]).")"; ?></a></h3>
          
          <ul>


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

      
      
        <li><a href="<?php echo $city_url; ?>"><?php echo $city['cityname']; ?> <?php if($show_city_adcount) echo "(".(0+$city_adcounts[$city['cityid']]).")"; ?></a></li>
			
	<?php

		}
?> </ul> <?php
	}
?> </div> <?php


/*
	$i++; $j++;
	//if($i%$split_at == 0) echo "</td><td valign=\"top\">";
	if ($j%$percolA[$col]==0 && $i<$country_count) { echo "</td><td valign=\"top\">"; $col++; $j=0; } 
*/
}

?>


</div>
</div>  
</div>
<div class="column">
<div class="Latin America and Caribbean geoBlock">
<h2 id="Latin America and Caribbean">Latin America and Caribbean</h2>
<div class="inner">


            
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

$sql = "SELECT * FROM $t_countries c INNER JOIN $t_cities ct ON c.countryid = ct.countryid AND ct.enabled = '1' WHERE c.enabled = '1' GROUP BY c.countryid $sort1 LIMIT 111,200";
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


            
            
            
          <div class="geoUnit">
            <h3><a ="<?php echo $country_url; ?>"><?php echo $country['countryname']; ?> <?php if($show_region_adcount) echo "(".(0+$country_adcounts[$country['countryid']]).")"; ?></a></h3>
          
          <ul>


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

      
      
        <li><a href="<?php echo $city_url; ?>"><?php echo $city['cityname']; ?> <?php if($show_city_adcount) echo "(".(0+$city_adcounts[$city['cityid']]).")"; ?></a></li>
			
	<?php

		}
?> </ul> <?php
	}
?> </div> <?php


/*
	$i++; $j++;
	//if($i%$split_at == 0) echo "</td><td valign=\"top\">";
	if ($j%$percolA[$col]==0 && $i<$country_count) { echo "</td><td valign=\"top\">"; $col++; $j=0; } 
*/
}

?>


</div>
</div>  
</div>
 <div class="clearfix"></div>
</div></div>
<?php include('footartext.php'); ?>
<?php include('footer.inc.php'); ?>
</script>
<br>
<script>dataLayer=[];</script>
<script>window.onload=function(){var headers=document.querySelectorAll('.geoBlock h2');for(i=0;i<headers.length;i++){var header=headers[i];header.onclick=function(e){var header=e.target;var inner=header.parentElement.querySelector('.inner');if(inner.className.match("showing")){inner.className="inner";}else{inner.className="inner showing";}}}}
</script>



<div style="clear:both;"></div>
</div>
</body>
</html>