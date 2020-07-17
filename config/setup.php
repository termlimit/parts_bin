<?php
require dirname(__DIR__) . '/config/bootstrap.php';

use ORM\MySqliDB;

// turn on output buffering, to speed up php processing, and use gzip compression for sending the files !
ini_set('zlib.output_compression_level', 3);
ob_start('ob_gzhandler');

//error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
error_reporting(E_ALL);
// Turn off all error reporting
//error_reporting(0);

//prevents caching
header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
header("Cache-Control: post-check=0, pre-check=0",false);
session_cache_limiter("nocache");

session_start();

/**
 * Set server timezone to UTC. You can change it to another timezone of your
 * choice but using UTC makes time calculations / conversions easier.
 */
date_default_timezone_set('UTC');

/**
 * Configure the mbstring extension to use the correct encoding.
 */
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
mb_http_input('UTF-8');
mb_regex_encoding('UTF-8');

/**
 * Set the default locale. This controls how dates, number and currency is
 * formatted and sets the default language to use for translations.
 */
ini_set('intl.default_locale', 'en_US');

///////////////
// DB INFO
$server = "localhost"; // db server 127.0.0.1 may also work, if you have a dedicated server then use its local IP address.
$database_name = ""; // db name
$database_username = ""; // db username
$database_password = ""; // db password
// END DB INFO
///////////////
require __DIR__ . '/setup_local.php';


//////////////
// BEGIN TABLE CONVERSIONS
$sqltable_devices_inventory = "devices_inventory";
$sqltable_errorlog = "errorlog";
//////////////


///////////////////////////
// SITE CONFIGURATION STUFF
///////////////////////////
//site name information
$hostsitename = "Parts Bin";

////////////////////////////
// URL STUFF
///////////////////////////
// PAGES
/////////
// Main page
$mainpageurl = "index.php"; // may need to change this to be just /index.php

// devices Inventory page
$devicesinventorypageurl = "devicesinventory.php";

// Export Inventory Page.
$exportinventoryurl = "exportinventory.php?export=1";

// Import Inventory Page.
$importinventoryurl = "importinventory.php";

// SQL Errors page.
$sqlerrorlogurl = "sqlerrorlog.php";



///////////////////////
// DATE AND TIME STUFF
//////////////////////
// Time and date display format (Fri 3:00:48pm 14th Apr 2006)
$datetimeformat = "g:i:sa D jS M Y";// (THIS MUST NOT BE CHANGED !!

// File export date format
$exportdatetimeformat = "g:ia_D_jS_M_Y";// (THIS MUST NOT BE CHANGED !!

// Site time zone
$offsetzone = 12; // NZ = +12, can change this to suit your location if required.
$zone = 3600*$offsetzone;

// Get current year (for copyright notice)
$currentyear = gmdate("Y", time() + $zone);


////////////////
// BEGIN CODE NZ Daylight Savings Time (automatic)
$getmonth = gmdate("n", time() + $zone); // get current month

if ($getmonth >= 10 || $getmonth <= 3){ // if months are well within DST time, than just add DST
	$zone = $zone +3600;
}
else{ // work out whether or not DST should be added
	$currenthour = gmdate("G", time() + $zone); // get current hour of the day, in 24 hour format
	$getday = gmdate("j", time() + $zone); // get current date (day)
	$getweekday = gmdate("D", time() + $zone); // get current weekday

	$daycheck = array("Sun","Sat","Fri","Thu","Wed","Tue","Mon");
	$calcday = 30 - $getday; // work out how many days are left in month (for Sept)
	$calcday2 = 7 - $getday; // work out how many days have gone in month (for April)

	if ($getmonth == 9 && $getday >= 24){ // Find last Sunday of Sept. (beginning of DST is last Sunday in Sept)
		for ($i = 0;$i < 7; $i++){
			if ($getweekday == $daycheck[$i]){
				if (($calcday < $i) || ($calcday == 0 && $getweekday != "Sun") || ($currenthour >= 2 && $getweekday == "Sun")){
					$zone = $zone +3600;
					break;
				}
			}
		}
	}

	if ($getmonth == 4 && $getday <= 7){ // Find first Sunday of April (ending of DST is first Sunday in April)
		for ($i = 0;$i < 7; $i++){
			if ($getweekday == $daycheck[$i]){
				if (($calcday2 >= $i && $getweekday != "Sun") || ($currenthour <= 2 && $getweekday == "Sun")){
					$zone = $zone +3600;
					break;
				}
			}
		}
	}
}
/////////////

// get current time for use on all pages
$timestamp = time() + $zone;


// flag to ID that config file is loaded
$configloaded = 1;


if ($_SERVER['HTTP_X_FORWARDED_FOR']) {
	if ($_SERVER['REMOTE_ADDR'] != $_SERVER['HTTP_X_FORWARDED_FOR']){
		$ip = $_SERVER['REMOTE_ADDR'] . ", " . $_SERVER['HTTP_X_FORWARDED_FOR'];
	}
	else{
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	}

	$ip = str_replace(", unknown", "", $ip); // remove "unknown" IP from variable

	if (($ip == "") || ($ip == NULL)){
		$ip = $_SERVER['REMOTE_ADDR'];
	}
}
else {
	$ip = $_SERVER['REMOTE_ADDR'];
}
$ip = trim($ip); // remove any whitespace at beginning and end.


// BEGIN CODE db connection and check
//make the connection to the database
$mysqli = MySqliDB::connect($server, $database_username, $database_password, $database_name);
//$database = @mysqli_select_db($connection,$database_name) or die("UNABLE TO CONNECT TO DB");
///////

$ip = $mysqli->real_escape_string($ip); // block IP address SQL injection - can happen by user sent HTTP_X_FORWARDED_FOR headers.
