<?php


// MySQL connection settings
@include('dbconnect.inc.php');

@mysql_connect($db_host, $db_user, $db_pass) or die("Cannot connect to DB");
@mysql_select_db($db_name);
$site_data_que = @mysql_query("select * from clf_site_control");
$site_data = @mysql_fetch_array($site_data_que);
$feature_data_que = @mysql_query("select * from clf_feature_control");
$feature_data = @mysql_fetch_array($feature_data_que);


	// Name of the site
$site_name = $site_data[0];

// Site email address
$site_email = $site_data[1];

// The URL of the script (without trailing slashes)
$script_url = $site_data[2];

// The basename of the language file to use.
// There should be a file with this name and ".inc.php" as extension in the "lang" directory
if ($site_data[3] == '') { $site_data[3] = 'en'; }
$language = $site_data[3];

// Make your site offline - Online. Yes=Offline - No=Online 
$offline = $site_data[6]; 

$offmesg = $site_data[7] ;

// Charge On Upload Addon Code enable. Default: 1 for on, enter 0 to disable

$enable_extra_uploads = 1;

// ID of the default city. If u want to use a region as default, 
// enter the region id preceeded with a '-'. Set this to 0 and 
// the first city in the database will be taken as the default.
//$default_city = $feature_data[0];
$default_city = $_COOKIE['clf_cityid'];

// Determine how many seconds poster should wait before posting a new ad

$post_time_limit = 0;

// Automatically expire events and images after how many days. 
$expire_events_after = 45;
$expire_images_after = 90;

// Default days for which the ad will be running. You may speify 
// a different expiration time for each subcategory from the admin.
$expire_ads_after_default = 100;

// The amount of days set in advanced to email users with expiring ads.
// This format is in seconds (86400 = 1 day, 172,800 = 2 days, etc.).  Default is 7 days

$expire_ads_ahead = 86400;


// Select if you want the poster's Ad Creation Date to update when they renew their ad
// Default is 1 (enabled), Disabled is 0

$update_ad_creation = 1;


// Select if you want to receive an email whenever the expire cron is ran that provides details such as 
// how many emails were sent.
// Default is 0 (disabled), Enabled is 1

$reminder_email_master = 1;

/* Begin Version 5.0 */
// Maximum number of abuse reports after which the ads are to be suspended.
// Should always be less than 99999. Set to 0 to disable.
$max_abuse_reports = $feature_data[1];
/* End Version 5.0 */

// Wether to use SE friendly URLs
// Requries .htaccess and mod_rewrite support
$sef_urls = $feature_data[2];

/* Begin Version 5.0 */
// Word separator to use in search engine friendly URLs, if $sef_urls is enabled.
$sef_word_separator = "-";
/* End Version 5.0 */

// Wether to enable the event calendar and image sharing
$enable_calendar = $feature_data[3];
$enable_images = $feature_data[4];

// Wether to show the left sidebar on inner pages also
$show_sidebar_always = FALSE;

// Wether to show the right sidebar
$show_right_sidebar = FALSE;

// Character to use as separator in the path shown on top of the page
$path_sep = " / ";

// Wether to sort cats and subcats alphabetically
$dir_sort = FALSE;

// Number of columns in the main directory. If you change to less than 3 then the layout might not look good.
$dir_cols = $feature_data[5];

// Wether to show the ad count near the subcategory and main category
$show_cat_adcount = TRUE;
$show_subcat_adcount = FALSE;

// Wether to sort location alphabetically
$location_sort = FALSE;

// Number of columns for locations
$location_cols = $feature_data[6];
//$location_cols = "5";

// Wether to show the number of ads near regions and cities in the right sidebar
$show_region_adcount = FALSE;
$show_city_adcount = FALSE;

// Number of ads/events and images to show per page
$ads_per_page = 200;
$images_per_page = 5;

// Number of picture upload fields to show in post ad page
$pic_count = $feature_data[7];

// Maximum size of pictures (in KB)
$pic_maxsize = 500;

// MIME types of files that are to be accepted as images
$pic_filetypes = array("image/gif", "image/jpeg", "image/pjpeg", "image/png");
$image_extensions = array("gif", "jpg", "jpeg", "png");

// Maximum height and width to which pictures uploaded 
// to the images category as well as those attached to
// ads are to be resized
$images_max_width = 1600;
$images_max_height = 1200;

// Thumbnail dimensions
$tinythumb_max_width = 0;			// Thumbnail in ad list
$tinythumb_max_height = 0;
$smallthumb_max_width = 0;		// Left sidebar
$smallthumb_max_height = 0;
$thumb_max_width = 0;				// Under images
$thumb_max_height = 0;

// The quality of the JPEG file after resizing (in %)
$images_jpeg_quality = 1000;

// Symbol for currency to use for prices
$currency = $site_data[9];

// Number of custom fields. Max 10.
$xfields_count = 10;

// HTML to append to the end of links in the ad
$link_append = " <span class=\"link_marker\">&raquo;</span> ";

// Wether to use image verification for posts
$image_verification = TRUE;

// Word with which the bad words are to be replaced.
$badword_replacement = "****";

// Number of latest ads etc. to show in the homepage. Set to 0 to disable.
$latestads_count = 1;
$latest_featured_ads_count = 20;
$upcoming_events_count = 0;
$upcoming_featured_events_count = 5;

// This much number of characters will be taken from the description
// as the title for an ad, if none given. Must be <= 100
$generated_adtitle_length = 20;

// String to be appended to generated ad titles
$generated_adtitle_append = "...";

// Show list of categories in left sidebar
$show_cats_in_sidebar = TRUE;

// Meta keywords and description
$meta_keywords = $site_data[4];
$meta_description = $site_data[5];

// If you have a forum software installed on the server, provide 
// the path to the forum directory from the script directory. If you
// dont want to use a forum, leave the variable empty.
$forum_dir = "";

// Show thumbnails with ads in the category pages 
$ad_thumbnails = FALSE;

// Show preview of ads in category pages. Specify number of characters to show. Set to 0 to disable.
$ad_preview_chars = 0;

// In the city list in the homepage, show cities for the currently selected region only.
// Helpful if you have lots of cities and regions listed.
$expand_current_region_only = FALSE;



// From address to use for mails sent using the contact form.
// Set to $site_email to make it the same as the site email set above.
$contactmail_from = $site_email;

// Maximum size of the file attachment to the mailer (in KB)
$contactmail_attach_maxsize = 250;

// Files that should be prevented from attaching
$contactmail_attach_wrongfiles = array("exe","com","bat","vbs","js","jar","scr","pif");


// Newsletter Addon
//$newsletter = 1;


// RSS feed - no of items and number of characters to show in the description field
$rss_itemcount = 20;
$rss_itemdesc_chars = 255;



/* Begin Version 5.0 */

// Whether to allow rich formatting in posts.
$enable_richtext = $feature_data[9];

// If set and rich text is enabled, allows rich text only in posts made on or after 
// this date. Considered only if $enable_richtext is set to TRUE. Useful if you had 
// modded the script to use HTML formatting. 
// Format: YYYY-mm-dd. 
//
// To disable, set to an old enough date or blank. 
// Eg: $richtext_since = "";
$richtext_since = "2018-11-26";

// Max number of spam words allowed in a post. If the number exceeds this, the 
// post will be marked as spam and would require admin approval.
$spam_word_limit = 5;

// Whether to use regular expression search while searching ads. This might take  
// some additional processing power but will return exact word matches.
$use_regex_search = FALSE;

// Quick solution to create "postable" categories.
// When set to TRUE, if a category has got only one subcategory and both has the
// same name, then the subcategory would be hidden to users and the category 
// would act as a shortcut to the subcategory, thus making it postable.
$shortcut_categories = TRUE;

// Quick solution to create "postable" regions.
// When set to TRUE, if a region has got only one city and both has the
// same name, then the city would be hidden to users and the region 
// would act as a shortcut to the city, thus making it postable.
$shortcut_regions = TRUE;

// Moderation options
$moderate_ads = FALSE;
$moderate_events = FALSE;
$moderate_images = FALSE;

/* End Version 5.0 */

/* Begin Version 5.1 - Send mail using SMTP */

// Set to true if you would like to use SMTP for sending emails instead of 
// php's mail() function.
$use_smtp = $feature_data[10];

// SMTP host and port. Default values should work on most servers.
$smtp_host = '$feature_data[11]';
$smtp_port = $feature_data[12];

// Wether to use SMTP authentication. Most servers do not need authentication.
// If set to true, also provide the SMTP username and password.
$smtp_authenticate = $feature_data[13];
$smtp_username = '$feature_data[14]';
$smtp_password = '$feature_data[15]';

/* End Version 5.1 - Send mail using SMTP */

/* Begin Version 5.7 */

// Uses stricter measures for admin login. If you are experiencing problems
// with admin login, try setting this to FALSE.
$strict_login = FALSE;

/* End Version 5.7 */

// Paid Promotions //
$enable_promotions = TRUE;

// Enable featured ads
$enable_featured_ads = TRUE;

// Enable extended ads (ads that run longer)
$enable_extended_ads = TRUE;



// Payment Gateway //

// Paypal account to receive payments.
$paypal_email = $site_data[8];

// Valid paypal currecncy code for payments. 
// All paypal transactions will take place in this currency.
$paypal_currency = "$site_data[12]";

// Symbol to show for the specified paypal currency. 
// This is what the user sees next to the prices for paid options.
$paypal_currency_symbol = $site_data[9];		



// Admin password
if ($site_data[11] == '') { $site_data[11] = 'admin'; }
$admin_pass = $site_data[11];

//$admin_pass = $site_data[11];

// Admin options
$admin_adpreview_chars = 100;
$admin_ads_per_page = 1000;
$admin_images_per_page = 1000;

// Hide sidebar by default when managing posts to have more room
$admin_auto_hide_sidebar = TRUE;


// Ensure all these 3 variables are set to FALSE
$debug = FALSE;
$demo = FALSE;
$sandbox_mode = FALSE;
/* Begin Version 5.0 */
$beta = FALSE;
/* End Version 5.0 */

/****************************************/
/* BEGIN account options     */
/**************************************/

// Extra user password protection.  
// WARNING! Changing the SALT value after script is installed will break all user logins!
// It is best to change this before any user is inserted into the DB, including your own account
// on the dbupdate.php page (first DB setup page).

define('SALT', '7p39(X#i');


// Name of accounts directory (without trailing slashes)

$acc_dir = 'accounts';

// Do not edit these next two lines

define('IN_SCRIPT', true); 

include_once($acc_dir . "/acc_config.php");


/************************************/
/* END account options   */
/**********************************/

/*--------------------------------------------------+
| DON'T EDIT ANYTHING BELOW                         |
+--------------------------------------------------*/


// Table names
$tprefix			= "clf_";
$t_countries		= $tprefix . "countries";
$t_cities			= $tprefix . "cities";
$t_areas			= $tprefix . "areas";
$t_cats				= $tprefix . "cats";
$t_subcats			= $tprefix . "subcats";
$t_ads				= $tprefix . "ads";
$t_adpics			= $tprefix . "adpics";
$t_events			= $tprefix . "events";
$t_eventpics		= $tprefix . "eventpics";
$t_subcatxfields	= $tprefix . "subcatxfields";
$t_adxfields		= $tprefix . "adxfields";
$t_imgs				= $tprefix . "imgs";
$t_imgcomments		= $tprefix . "imgcomments";
$t_featured			= $tprefix . "featured";
$t_options_featured = $tprefix . "options_featured";
$t_options_extended	= $tprefix . "options_extended";
$t_promos_featured	= $tprefix . "promos_featured";
$t_promos_extended	= $tprefix . "promos_extended";
$t_payments			= $tprefix . "payments";
$t_ipns				= $tprefix . "ipns";
$t_ipblock			= $tprefix . "ipblock";
// BEGIN account mod table names
$t_users		    = $tprefix . "acc_users";
// END account mod table names
$t_privacy_terms	= $tprefix . "privacy_terms";
$t_site_control		= $tprefix . "site_control";
$t_feature_control	= $tprefix . "feature_control";
$t_user_data		= $tprefix . "user_data";


// Cookie names
$ck_admin			= "clf_admin";
$ck_lang			= "clf_lang";
$ck_cityid			= "clf_cityid";
$ck_edit_adid		= "clf_edit_adid";
$ck_edit_isevent	= "clf_edit_isevent";
$ck_edit_codemd5	= "clf_edit_codemd5";
$ck_admin_theme		= "clf_admin_theme";


// Data files
$datafile['badwords'] = "data/badwords.dat";
/* Begin Version 5.0 */
$datafile['spamfilter'] = "data/spamfilter.dat";
/* End Version 5.0 */

// Directories
$datadir['adpics'] = "adpics";
$datadir['userimgs'] = "userimgs";


// More settings
$vbasedir = "";
$custom_pages = array("terms","privacy","recommend_site");
$encryptposter_sep = ">@<";

/* Begin Version 5.7 - Incorrect text wrap fix */
$word_wrap_at = 100;
/* End Version 5.7 - Incorrect text wrap fix */

/* Begin Version 5.0 */
$spam_indicator = $max_abuse_reports >= 99999 ? $max_abuse_reports + 100 : 99999;
/* End Version 5.0 */


// Enumeration for show email option
define ('EMAIL_HIDE',		0);
define ('EMAIL_SHOW',		1);
define ('EMAIL_USEFORM',	2);


// Input sanitization must be done before loading the config.
if (!defined('INIT_DONE')) {
	die("Initialization not done");
}

if(!defined('CONFIG_LOADED'))
{
	// Constant to indicate if the config has been loaded
	define('CONFIG_LOADED', TRUE);

	// Start output buffering
	ob_start();


	// Connect to the database
    $cn = @mysql_connect($db_host, $db_user, $db_pass) or die("Cannot connect to DB");
    mysql_select_db($db_name) or die("Error accessing DB");

	// Dependancies
	require_once("{$path_escape}ipblock.inc.php");
	/* START mod-paid-categories */
	require_once("{$path_escape}paid_cats/mod_config.php");
	/* END mod-paid-categories */
	require_once("{$path_escape}common.inc.php");
	require_once("{$path_escape}calendar.cls.php");
}

?>