<?php
// turn on output buffering, to speed up php processing, and use gzip compression for sending the files !
//ob_start();
//ini_set('zlib.output_compression_level', 3);
//ob_start("ob_gzhandler");

//error_reporting(~E_ALL & ~E_NOTICE & ~E_WARNING);

session_start();

$path = ""; // add ../ to increase levels eg $path = "../"; if this page is in a subdirectory

//require the config file
require $path . ("config.php");

// include the xls class
include_once $path . ("includes/xlsxwriter.class.php");


// convert variables
$export = $_REQUEST["export"];

/////// CONFIRMED WORKING
// BEGIN CODE handle special characters to stop malicious users
$export = strip_tags($export);
// END CODE
//////

/////// CONFIRMED WORKING
// BEGIN CODE handle special characters to stop malicious users
$export = htmlspecialchars($export, ENT_QUOTES);
// END CODE
//////


// make sure that it is trying to use a valid call
if($export == "1"){
    //build and issue the query
    $sql117 ="SELECT * FROM `$sqltable_devices_inventory`"; // make sure that the listing is in progress, to help prevent it being hacked !
    $result117 = @mysqli_query($connection,$sql117);// or die("Some Error Occured");//die(mysqli_error());

    if (!$result117){
    	$errorsql = addslashes($sql117);
    	$errormsg = addslashes(mysqli_error($connection));
    	$errorlog = mysqli_query($connection,"INSERT INTO `$sqltable_errorlog` VALUES ('$_SERVER[PHP_SELF]', '$errormsg', '$errorsql', '$timestamp', '', '$ip')");
    	die("An Error Occurred, the error has been logged for the admin to investigate.");
    }

    //get the number of rows in the result set
    $num117 = mysqli_num_rows($result117);

    if ($num117 != 0){
    	// filename for download
    	$filename = "MyPartsBin_Free_Inventory_" . gmdate($exportdatetimeformat, $timestamp) . ".xls";


    	// Send Header
    	//header("Expires: 0");
    	//header("Content-Type: application/force-download"); // BUG TEST edited to see if removing it helped
    	//header("Content-Type: application/octet-stream"); // BUG TEST edited to see if removing it helped
    	//header("Content-Type: application/download"); // BUG TEST edited to see if removing it helped
    	//header("Content-Disposition: attachment; filename=$filename");  // BUG TEST edited to see if CHANGING it helped
    	header("Content-disposition: attachment;filename=$filename"); //
    	header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"); // BUG TEST trying this content type
    	//header("Content-Type: application/vnd.ms-excel"); // BUG TEST edited to see if removing it helped
    	header("Content-Transfer-Encoding: binary");
    	//header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    	header("Cache-Control: must-revalidate");  // BUG TEST edited to see if CHANGING it helped
    	header("Pragma: public");


    	while ($sql117 = mysqli_fetch_object($result117)){
			//$deviceid					= $sql117 -> deviceid;
			$devicenumber				= $sql117 -> devicenumber;
			$devicepackage			= $sql117 -> devicepackage;
			$devicetype				= $sql117 -> devicetype;
			$devicedescription		= $sql117 -> devicedescription;
			$devicequantity			= $sql117 -> devicequantity;
			$devicepackaging			= $sql117 -> devicepackaging;
			$devicebinlocation		= $sql117 -> devicebinlocation;
			$devicelink				= $sql117 -> devicelink;
			$datetime				= $sql117 -> datetime;
			$project				= $sql117 -> project;



			$datetimeview	= gmdate($datetimeformat, $datetime); // convert timestamp into plain text date time format

			$devicenumber = htmlspecialchars_decode($devicenumber);
			$devicepackage = htmlspecialchars_decode($devicepackage);
			$devicetype = htmlspecialchars_decode($devicetype);
			$devicedescription = htmlspecialchars_decode($devicedescription);
			$devicequantity = htmlspecialchars_decode($devicequantity);
			$devicepackaging = htmlspecialchars_decode($devicepackaging);
			$devicebinlocation = htmlspecialchars_decode($devicebinlocation);
			$devicelink = htmlspecialchars_decode($devicelink);
			$project = htmlspecialchars_decode($project);


			$devicenumber = str_replace("&AMP;","&",$devicenumber);
			$devicepackage = str_replace("&AMP;","&",$devicepackage);
			$devicetype = str_replace("&AMP;","&",$devicetype);
			$devicedescription = str_replace("&AMP;","&",$devicedescription);
			$devicequantity = str_replace("&AMP;","&",$devicequantity);
			$devicepackaging = str_replace("&AMP;","&",$devicepackaging);
			$devicebinlocation = str_replace("&AMP;","&",$devicebinlocation);
			$devicelink = str_replace("&AMP;","&",$devicelink);
			$project = str_replace("&AMP;","&",$project);



			// convert special chars to normal chars for exporting
			//$adminclubname = str_replace("&#039;","'",$adminclubname);
			//$admincomments = str_replace("&#039;","'",$admincomments);

			// only show header once
			if($countrow == 0){ // || $countrow == 21 || $countrow == 41 || $countrow == 61 || $countrow == 81 || $countrow == 101 || $countrow == 121 || $countrow == 141 || $countrow == 161 || $countrow == 181 || $countrow == 201 || $countrow == 221 || $countrow == 241 || $countrow == 261 || $countrow == 281 || $countrow == 301 || $countrow == 321 || $countrow == 341 || $countrow == 361 || $countrow == 381 || $countrow == 401) {

				$header = array(
						//'Part ID'=>'string',
						'Bin'=>'string',
						'Part Number'=>'string',
						'Package'=>'string',
						'Type'=>'string',
						'Description'=>'string',
						'Quantity'=>'string',
						'Packaging'=>'string',
						'Project'=>'string',
						'Link'=>'string',
						'Last Updated'=>'string'

						// OPTIONS FOR FORMATS
						//''=>'string',
						//''=>'money',
						//''=>'datetime',
						//''=>'date',
					);

				// create data array ready for the actual data
				$data1 = array();
				$rowdata = array();
			}
			// Increment row counter
			$countrow = $countrow + 1;


			// now build the rows actual data
			$rowdata = array("$devicebinlocation","$devicenumber","$devicepackage","$devicetype","$devicedescription","$devicequantity","$devicepackaging","$project","$devicelink","$datetimeview");

			// construct the entire row with all the data
			array_push($data1,$rowdata);
		}
		// end row


		// now construct the file and send to the browser
		$writer = new XLSXWriter();
		$writer->setAuthor('MyPartsBin.com');
		$writer->writeSheet($data1,'Inventory',$header);
		//$writer->writeSheet($data2,'Sheet2'); // can build with multiple sheets !
		$writer->writeToStdOut();
		//$writer->writeToFile('example.xlsx');
		//echo $writer->writeToString();
		exit();

		// end lookup
		//////////
	}
	else{
		echo "The inventory could not be found.";
	}
}

?>