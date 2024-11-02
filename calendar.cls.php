<?php







class calendar
{
	function display($urlformat, $weekday_start=0, $weekdays="", $months="", $year=0, $month=0, $specialdates="", $cellwidth=20)
	{
		// Get year and month
		if($year && $month)
		{
			$day = date("d");
			$t = mktime(0, 0, 0, $month, $day, $year);
			if($year == date("Y") && $month == date("m")) $highlighttoday = TRUE;
		}
		else
		{
			$year = date("Y");
			$month = date("m");
			$day = date("d");
			$t = time();
			$highlighttoday = TRUE;
		}

		// Save year and month
		//setcookie("_xzcal_m", $month);
		//setcookie("_xzcal_y", $year);

		// Get weekday and month names
		if(!$weekdays)
		{
			$weekdays = array("Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday");
		}

		if(!$months)
		{
			$months = array("January","February","March","April","May","June","July","August","September","October","November","December");
		}

		$weekdays_small = array();
		foreach($weekdays as $k=>$wd)
		{
			$weekdays_small[$k] = substr($wd, 0, 1);
			if(ord($weekdays_small[$k]) >= 128) $weekdays_small[$k] .= substr($wd, 1, 1);
		}


		// Calendar title
		$caltitle = $months[$month-1] . " " . $year;

		// Prev and next month links
		if ($month == 1)
		{
			$prev_month = 12;
			$prev_month_year = $year-1;
		}
		else
		{
			$prev_month = $month-1;
			$prev_month_year = $year;
		}

		if ($month == 12)
		{
			$next_month = 1;
			$next_month_year = $year+1;
		}
		else
		{
			$next_month = $month+1;
			$next_month_year = $year;
		}


		$month_link = "?";
		$qsA = $_GET; unset($qsA['_xzcal_m'], $qsA['_xzcal_y']);
		foreach($qsA as $k=>$v) $month_link .= "$k=$v&";
		$prev_month_link = $month_link . "_xzcal_m=$prev_month&_xzcal_y=$prev_month_year";
		$next_month_link = $month_link . "_xzcal_m=$next_month&_xzcal_y=$next_month_year";


		$cal = <<< EOB
		<table cellspacing="1" border="0" cellpadding="0" class="calendar">
		<tr>
		<td class="cal_header_month"><a href="$prev_month_link">&laquo;</a></td>
		<td colspan="5" class="cal_header_month">$caltitle</td>
		<td class="cal_header_month"><a href="$next_month_link">&raquo;</a></td>
		</tr>
		<tr>

EOB;

		// Weekdays
		$j = $weekday_start;
		for ($i=0; $i<7; $i++)
		{
			$wds = $weekdays_small[$j];

			$cal .= <<< EOB
			<td class="cal_header_week" width="$cellwidth">$wds</td>

EOB;
			$j++;
			if ($j==7) $j=0;
		}

		$cal .= <<< EOB
		</tr>
EOB;


		// Days
		$firstday_weekday = date("w", mktime(0, 0, 0, $month, 1, $year));

		$empty_cells = ($firstday_weekday >= $weekday_start) ?
						($firstday_weekday - $weekday_start) :
						7 - $weekday_start;

		if ($empty_cells) $cal .= "<tr>";
		for ($i=0; $i<$empty_cells; $i++)
			$cal .= "<td>&nbsp;</td>";

		$lastday = date("t", $t);
		//$datepre = date("Y-m-", $t);
		$today = date("j", $t);

		for ($d=1; $d<=$lastday; $d++, $i++)
		{
			$url = $urlformat;
			$url = str_replace("{@T}", mktime(0, 0, 0, $month, $d, $year), $url);
			$url = str_replace("{@D}", str_pad($d, 2, "0", STR_PAD_LEFT), $url);
			$url = str_replace("{@M}", $month, $url);
			$url = str_replace("{@Y}", $year, $url);
			$url = str_replace("{@W}", $i, $url);

			if ($i%7==0) $cal .= "<tr>";
			$cal .= "<td";
			if ($d == $today && $highlighttoday) $cal .= " id=\"today\"";
			if (isset($specialdates[$d])) $cal .= " class=\"content_date\"";
			$cal .= "><a href=\"$url\">$d</a></td>";
			if ($i%7==6) $cal .= "</tr>";
		}

		if ($i%7 != 0)
		{
			for(; $i%7!=0; $i++) $cal .= "<td>&nbsp;</td>";
		}

		$cal .= "</tr></table>";

		echo $cal;

	}
}

?>