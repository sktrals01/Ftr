<?php

/* Begin Version 5.1 - Blank page after payment fix */
require_once("initvars.inc.php");
require_once("config.inc.php");
/* End Version 5.1 - Blank page after payment fix */

/*if($_POST['item_number'])
{
	$item_number_parts = explode("-", $_POST['item_number']);
	
	if(substr($item_number_parts[0], 0, 1) == "E") $view = "showevent";
	else $view = "showad";
	
	$adid = substr($item_number_parts[0], 1);
	
	$target_page = "index.php?view=$view&adid=$adid";
}*/
if ($_GET['adid'])
{
    /* Begin Version 5.0 */
    $target_page = buildURL(($_GET['adtype']=="E"?"showevent":"showad"), array($_GET['adid']), TRUE);
    /* End Version 5.0 */
}
else
{
	$target_page = "index.php";
}

header("Location: $target_page");
exit;

?>