<?php
// turn on output buffering, to speed up php processing, and use gzip compression for sending the files !
//ob_start();
ini_set('zlib.output_compression_level', 3);
ob_start("ob_gzhandler");

error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

//prevents caching
header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
header("Cache-Control: post-check=0, pre-check=0",false);
session_cache_limiter("nocache");

session_start();

$path = ""; // add ../ to increase levels eg $path = "../"; if this page is in a subdirectory

//require the config file
require $path . ("config.php");



/////// CONFIRMED WORKING
// BEGIN CODE handle special characters to stop malicious users
$deviceid = strip_tags($_REQUEST["deviceid"]);
$devicenumber = strip_tags($_REQUEST["devicenumber"]);
$devicepackage = strip_tags($_REQUEST["devicepackage"]);
$devicetype = strip_tags($_REQUEST["devicetype"]);
$devicedescription = strip_tags($_REQUEST["devicedescription"]);
$devicequantity = strip_tags($_REQUEST["devicequantity"]);
$devicepackaging = strip_tags($_REQUEST["devicepackaging"]);
$devicebinlocation = strip_tags($_REQUEST["devicebinlocation"]);
$devicelink = strip_tags($_REQUEST["devicelink"]);
$project = strip_tags($_REQUEST["project"]);
$submitted = strip_tags($_REQUEST["submitted"]);
$edit = strip_tags($_REQUEST["edit"]);
$delete = strip_tags($_REQUEST["delete"]);
$search = strip_tags($_REQUEST["search"]);


/////// CONFIRMED WORKING
// BEGIN CODE handle special characters to stop malicious users
$deviceid = htmlspecialchars($deviceid, ENT_QUOTES);
$devicenumber = htmlspecialchars($devicenumber, ENT_QUOTES);
$devicepackage = htmlspecialchars($devicepackage, ENT_QUOTES);
$devicetype = htmlspecialchars($devicetype, ENT_QUOTES);
$devicedescription = htmlspecialchars($devicedescription, ENT_QUOTES);
$devicequantity = htmlspecialchars($devicequantity, ENT_QUOTES);
$devicepackaging = htmlspecialchars($devicepackaging, ENT_QUOTES);
$devicebinlocation = htmlspecialchars($devicebinlocation, ENT_QUOTES);
$devicelink = htmlspecialchars($devicelink, ENT_QUOTES);
$project = htmlspecialchars($project, ENT_QUOTES);
$submitted = htmlspecialchars($submitted, ENT_QUOTES);
$edit = htmlspecialchars($edit, ENT_QUOTES);
$delete = htmlspecialchars($delete, ENT_QUOTES);
$search = htmlspecialchars($search, ENT_QUOTES);
// END CODE
//////

/////// CONFIRMED WORKING
// BEGIN CODE handle special characters to stop malicious users
$deviceid = stripslashes($deviceid);
$devicenumber = stripslashes($devicenumber);
$devicepackage = stripslashes($devicepackage);
$devicetype = stripslashes($devicetype);
$devicedescription = stripslashes($devicedescription);
$devicequantity = stripslashes($devicequantity);
$devicepackaging = stripslashes($devicepackaging);
$devicebinlocation = stripslashes($devicebinlocation);
$devicelink = stripslashes($devicelink);
$project = stripslashes($project);
$submitted = stripslashes($submitted);
$edit = stripslashes($edit);
$delete = stripslashes($delete);
$search = stripslashes($search);
// END CODE
//////



/////// CONFIRMED WORKING
// BEGIN CODE handle special characters to stop malicious users
$deviceid = mysqli_real_escape_string($connection, $deviceid);
$devicenumber = mysqli_real_escape_string($connection, $devicenumber);
$devicepackage = mysqli_real_escape_string($connection, $devicepackage);
$devicetype = mysqli_real_escape_string($connection, $devicetype);
$devicedescription = mysqli_real_escape_string($connection, $devicedescription);
$devicequantity = mysqli_real_escape_string($connection, $devicequantity);
$devicepackaging = mysqli_real_escape_string($connection, $devicepackaging);
$devicebinlocation = mysqli_real_escape_string($connection, $devicebinlocation);
$devicelink = mysqli_real_escape_string($connection, $devicelink);
$project = mysqli_real_escape_string($connection, $project);
$submitted = mysqli_real_escape_string($connection, $submitted);
$edit = mysqli_real_escape_string($connection, $edit);
$delete = mysqli_real_escape_string($connection, $delete);
$search = mysqli_real_escape_string($connection, $search);
// END CODE
//////

/////// CONFIRMED WORKING
// BEGIN CODE handle special characters to stop malicious users
$deviceid = (int)$deviceid;
$submitted = (int)$submitted;
$edit = (int)$edit;
$delete = (int)$delete;
$search = (int)$search;
////////


// check for empty fields, where the user has used spaces, trim spaces from the left and right sides, which should empty the variable
$devicenumber = trim($devicenumber, " ");
$devicepackage = trim($devicepackage, " ");
$devicetype = trim($devicetype, " ");
$devicedescription = trim($devicedescription, " ");
$devicequantity = trim($devicequantity, " ");
$devicelink = trim($devicelink, " ");
$devicepackaging = trim($devicepackaging, " ");
$devicebinlocation = trim($devicebinlocation, " ");
$project = trim($project, " ");


// turn on flag to show page as per normal (turns off when doing a delete)
$getid = 0;
// clear message
$message = "";


// turn off enter key to prevent accidental submission
$disableenterkey = "1";

//////
// DO DELETION
if ($delete == 1 && $deviceid != ""){
	$message = $message . "<br><center><h3>Are you sure that you wish to delete the entry ?</h3><br>";
	$message = $message . "<span class=\"sg14 sgb\"><a href=\"$_SERVER[PHP_SELF]?delete=2&deviceid=$deviceid\">Yes</a> &nbsp; <a href=\"$_SERVER[PHP_SELF]?delete=0&deviceid=\">Cancel</a></span></center><br><br>";

	$getid = 1;
}
if ($delete == 2 && $deviceid != ""){
	//////////////
	//build and issue the query
	$sql133 ="SELECT * FROM `$sqltable_devices_inventory` WHERE `deviceid` = '$deviceid'"; // make sure that the image is real, and not a hack attempt.
	$result133 = @mysqli_query($connection,$sql133);// or die("Some Error Occured 5");//die(mysqli_error());

    if (!$result133){
    	$errorsql = addslashes($sql133);
    	$errormsg = addslashes(mysqli_error($connection));
    	$errorlog = mysqli_query($connection,"INSERT INTO `$sqltable_errorlog` VALUES ('$_SERVER[PHP_SELF]', '$errormsg', '$errorsql', '$timestamp', '', '$ip')");
    	die("An Error Occurred, the error has been logged for the admin to investigate.");
    }

	//get the number of rows in the result set
	$num133 = mysqli_num_rows($result133);

	if ($num133 != 0){
		$sql134a = "DELETE FROM `$sqltable_devices_inventory` WHERE `deviceid` = '$deviceid' LIMIT 1";
		$result134a = @mysqli_query($connection,$sql134a);// or die("Some Error Occured 3");//die(mysqli_error());

		if (!$result134a){
			$errorsql = addslashes($sql134a);
			$errormsg = addslashes(mysqli_error($connection));
			$errorlog = mysqli_query($connection,"INSERT INTO `$sqltable_errorlog` VALUES ('$_SERVER[PHP_SELF]', '$errormsg', '$errorsql', '$timestamp', '', '$ip')");
			die("An Error Occurred, the error has been logged for the admin to investigate.");
		}
	}
	else{
		$message = $message . "<br><br><span class=\"sgb\">That device id could not be located.</span><BR><BR>";
	}
	$delete = 0;
}
//
/////////


$pagesubtitle = "devices Inventory";

//include the page top file
include $path . "includes/pagehead.php";



// action lookup from the link
if($edit == 1 && $deviceid != "0"){ // if its the beginning of an edit, look up the entry to fill in the form
	$sql117 ="SELECT * FROM `$sqltable_devices_inventory` WHERE `deviceid` = '$deviceid' LIMIT 1"; // make sure that the listing is in progress, to help prevent it being hacked !
	$result117 = @mysqli_query($connection,$sql117);// or die("Some Error Occured");//die(mysqli_error());

	if (!$result117){
		$errorsql = addslashes($sql117);
		$errormsg = addslashes(mysqli_error($connection));
		$errorlog = mysqli_query($connection,"INSERT INTO `$sqltable_errorlog` VALUES ('$_SERVER[PHP_SELF]', '$errormsg', '$errorsql', '$timestamp', '', '$ip')");
		die("An Error Occurred, the error has been logged for the admin to investigate.");
	}

	//get the number of rows in the result set
	$num117 = mysqli_num_rows($result117);

	if($num117 != 0){
		while($sql117 = mysqli_fetch_object($result117)) {
		   $devicenumber				= $sql117 -> devicenumber;
		   $devicepackage				= $sql117 -> devicepackage;
		   $devicetype				= $sql117 -> devicetype;
		   $devicedescription			= $sql117 -> devicedescription;
		   $devicequantity			= $sql117 -> devicequantity;
		   $devicepackaging			= $sql117 -> devicepackaging;
		   $devicebinlocation			= $sql117 -> devicebinlocation;
		   $devicelink				= $sql117 -> devicelink;
		   $project					= $sql117 -> project;
		}
		$edit = 2;
	}
}


if($submitted == 1 && $devicenumber != "" && $search == 0){ // only action the submitted form data from the form itself
	if($edit == 2 && $deviceid != 0){ //if its an edit submited from the form then update it
		/// UPDATE DATABASE WITH NEW INFO
		$sql118 = "UPDATE `$sqltable_devices_inventory`
					SET
					`devicenumber` = '$devicenumber',
					`devicepackage` = '$devicepackage',
					`devicetype` = '$devicetype',
					`devicedescription` = '$devicedescription',
					`devicequantity` = '$devicequantity',
					`devicepackaging` = '$devicepackaging',
					`devicebinlocation` = '$devicebinlocation',
					`devicelink` = '$devicelink',
					`project` = '$project',
					`datetime` = '$timestamp'
					WHERE `deviceid` = '$deviceid' LIMIT 1"; //could be a problem, using SESSION OR POST is a security issue !!

		$result118 = @mysqli_query($connection,$sql118);// or die("Some Error Occured");//die(mysqli_error());

		if (!$result118){
			$errorsql = addslashes($sql118);
			$errormsg = addslashes(mysqli_error($connection));
			$errorlog = mysqli_query($connection,"INSERT INTO `$sqltable_errorlog` VALUES ('$_SERVER[PHP_SELF]', '$errormsg', '$errorsql', '$timestamp', '', '$ip')");
			die("An Error Occurred, the error has been logged for the admin to investigate.");
		}
		else{
			$message = "Sucessfully Updated $devicenumber In Inventory";
		}
	}
	else{ // if its a new entry then add it
		$spare = "";
		// add entry into the database
		$sql119 = "INSERT INTO `$sqltable_devices_inventory` VALUES (
						'0',
						'$devicenumber',
						'$devicepackage',
						'$devicetype',
						'$devicedescription',
						'$devicequantity',
						'$devicepackaging',
						'$devicebinlocation',
						'$devicelink',
						'$project',
						'$timestamp',
						'$spare'
						)";


		$result119 = @mysqli_query($connection,$sql119);// or die("Some Error Occured");//die(mysqli_error());

		if (!$result119){
			$errorsql = addslashes($sql119);
			$errormsg = addslashes(mysqli_error($connection));
			$errorlog = mysqli_query($connection,"INSERT INTO `$sqltable_errorlog` VALUES ('$_SERVER[PHP_SELF]', '$errormsg', '$errorsql', '$timestamp', '', '$ip')");
			die("An Error Occurred, the error has been logged for the admin to investigate. CODE 6");
		}
		else{
			$message = "Sucessfully Added $devicenumber To Inventory";
		}
		// END CODE
		////////
	}

	// reset form values if it was submitted so it doesnt show them again
	$edit = 0;
	$deviceid = 0;
	$devicenumber = "";
	$devicepackage = "";
	$devicetype = "";
	$devicedescription = "";
	$devicequantity = "";
	$devicepackaging = "";
	$devicebinlocation = "";
	$devicelink = "";
	$project = "";
}
else if($submitted == 1 && $devicenumber == "" && $search == 0){
	$message = "You need to specify a device number to add a device";
}

?>

<br>
<h2 class="sgc"><php echo "$pagesubtitle" ?></h2>
<br>

<div class="sgul sg14 sgmargin0 sgdiv">

    <?php

    // show/hide search button and Add or Update text on submit button
    if($edit == 0){
    	$submitbuttontext = "Add";
    	$searchheadertext = "/ Search";
    	$searchbutton = "OR <input type=\"submit\" value=\"Search\" formaction=\"$_SERVER[PHP_SELF]?search=1\" style=\"font-family: Tahoma\">";
    }
    else{
    	$submitbuttontext = "Update";
    }

    $devicetypeselect = "";
	$devicetypeselect = $devicetypeselect . "<option value=\"\" selected>Select One....</option>";
	$devicetypeselect = $devicetypeselect . "<optgroup label=\"PASSIVES\">";
		$devicetypeselect = $devicetypeselect . "<option value=\"Capacitor\">Capacitor</option>";
		$devicetypeselect = $devicetypeselect . "<option value=\"Crystal\">Crystal</option>";
		$devicetypeselect = $devicetypeselect . "<option value=\"Ferrite\">Ferrite</option>";
		$devicetypeselect = $devicetypeselect . "<option value=\"Fuse\">Fuse</option>";
		$devicetypeselect = $devicetypeselect . "<option value=\"Inductor\">Inductor</option>";
		$devicetypeselect = $devicetypeselect . "<option value=\"Oscillator\">Oscillator</option>";
		$devicetypeselect = $devicetypeselect . "<option value=\"Resistor\">Resistor</option>";
		$devicetypeselect = $devicetypeselect . "<option value=\"Passive-Other\">Passive-Other</option>";
	$devicetypeselect = $devicetypeselect . "</optgroup>";
		$devicetypeselect = $devicetypeselect . "<optgroup label=\"SEMICONDUCTORS\">";
		$devicetypeselect = $devicetypeselect . "<option value=\"Amplifier\">Amplifier</option>";
		$devicetypeselect = $devicetypeselect . "<option value=\"Diode\">Diode</option>";
		$devicetypeselect = $devicetypeselect . "<option value=\"FET\">FET</option>";
		$devicetypeselect = $devicetypeselect . "<option value=\"IC\">IC</option>";
		$devicetypeselect = $devicetypeselect . "<option value=\"Microcontroller\">Microcontroller</option>";
		$devicetypeselect = $devicetypeselect . "<option value=\"MOSFET\">MOSFET</option>";
		$devicetypeselect = $devicetypeselect . "<option value=\"Op-Amp\">Op-Amp</option>";
		$devicetypeselect = $devicetypeselect . "<option value=\"Transistor-NPN\">Transistor-NPN</option>";
		$devicetypeselect = $devicetypeselect . "<option value=\"Transistor-PNP\">Transistor-PNP</option>";
		$devicetypeselect = $devicetypeselect . "<option value=\"Voltage Regulator\">Voltage Regulator</option>";
		$devicetypeselect = $devicetypeselect . "<option value=\"Semiconductors-Other\">Semiconductors-Other</option>";
	$devicetypeselect = $devicetypeselect . "</optgroup>";
	$devicetypeselect = $devicetypeselect . "<optgroup label=\"OPTOELECTRONICS\">";
		$devicetypeselect = $devicetypeselect . "<option value=\"Display\">Display</option>";
		$devicetypeselect = $devicetypeselect . "<option value=\"LED-Individual\">LED-Individual</option>";
		$devicetypeselect = $devicetypeselect . "<option value=\"LED-Module\">LED-Module</option>";
		$devicetypeselect = $devicetypeselect . "<option value=\"Optocoupler\">Optocoupler</option>";
		$devicetypeselect = $devicetypeselect . "<option value=\"Photoresistor\">Photoresistor</option>";
		$devicetypeselect = $devicetypeselect . "<option value=\"Optoelectronics-Other\">Optoelectronics-Other</option>";
	$devicetypeselect = $devicetypeselect . "</optgroup>";
	$devicetypeselect = $devicetypeselect . "<optgroup label=\"HARDWARE\">";
		$devicetypeselect = $devicetypeselect . "<option value=\"Assembly\">Assembly</option>";
		$devicetypeselect = $devicetypeselect . "<option value=\"Cable\">Cable</option>";
		$devicetypeselect = $devicetypeselect . "<option value=\"Fastener\">Fastener</option>";
		$devicetypeselect = $devicetypeselect . "<option value=\"Knob\">Knob</option>";
		$devicetypeselect = $devicetypeselect . "<option value=\"Module\">Module</option>";
		$devicetypeselect = $devicetypeselect . "<option value=\"PCB\">PCB</option>";
		$devicetypeselect = $devicetypeselect . "<option value=\"Hardware-Other\">Hardware-Other</option>";
	$devicetypeselect = $devicetypeselect . "</optgroup>";
	$devicetypeselect = $devicetypeselect . "<optgroup label=\"ELECTROMECHANICAL\">";
		$devicetypeselect = $devicetypeselect . "<option value=\"Encoder-Rotary\">Encoder-Rotary</option>";
		$devicetypeselect = $devicetypeselect . "<option value=\"Encoder-Linear\">Encoder-Linear</option>";
		$devicetypeselect = $devicetypeselect . "<option value=\"Encoder-Other\">Encoder-Other</option>";
		$devicetypeselect = $devicetypeselect . "<option value=\"Plug\">Plug</option>";
		$devicetypeselect = $devicetypeselect . "<option value=\"Relay-SPST\">Relay-SPST</option>";
		$devicetypeselect = $devicetypeselect . "<option value=\"Relay-DPST\">Relay-DPST</option>";
		$devicetypeselect = $devicetypeselect . "<option value=\"Relay-SPDT\">Relay-SPDT</option>";
		$devicetypeselect = $devicetypeselect . "<option value=\"Relay-DPDT\">Relay-DPDT</option>";
		$devicetypeselect = $devicetypeselect . "<option value=\"Relay-Reed\">Relay-Reed</option>";
		$devicetypeselect = $devicetypeselect . "<option value=\"Relay-Latching\">Relay-Latching</option>";
		$devicetypeselect = $devicetypeselect . "<option value=\"Relay-Other\">Relay-Other</option>";
		$devicetypeselect = $devicetypeselect . "<option value=\"Sensor\">Sensor</option>";
		$devicetypeselect = $devicetypeselect . "<option value=\"Socket\">Socket</option>";
		$devicetypeselect = $devicetypeselect . "<option value=\"Switch-Press\">Switch-Press</option>";
		$devicetypeselect = $devicetypeselect . "<option value=\"Switch-Reed\">Switch-Reed</option>";
		$devicetypeselect = $devicetypeselect . "<option value=\"Switch-Rotary\">Switch-Rotary</option>";
		$devicetypeselect = $devicetypeselect . "<option value=\"Switch-Toggle\">Switch-Toggle</option>";
		$devicetypeselect = $devicetypeselect . "<option value=\"Switch-Other\">Switch-Other</option>";
		$devicetypeselect = $devicetypeselect . "<option value=\"Electromechanical-Other\">Electromechanical-Other</option>";
	$devicetypeselect = $devicetypeselect . "</optgroup>";

	// select previously selected item if required on page submission and form re-display
	if ($devicetype != ""){
		$devicetypeselect = str_replace("value=\"Loose\" selected>","value=\"Loose\">",$devicetypeselect);
		$devicetypeselect = str_replace("value=\"$devicetype\">","value=\"$devicetype\" selected>",$devicetypeselect);
	}

    $devicepackagingselect = "";
	$devicepackagingselect = $devicepackagingselect . "<option value=\"\" selected>Select One....</option>";
	$devicepackagingselect = $devicepackagingselect . "<option value=\"Bagged\">Bagged</option>";
	$devicepackagingselect = $devicepackagingselect . "<option value=\"Loose\">Loose</option>";
	$devicepackagingselect = $devicepackagingselect . "<option value=\"Reel\">Reel</option>";
	$devicepackagingselect = $devicepackagingselect . "<option value=\"Tape\">Tape</option>";
	$devicepackagingselect = $devicepackagingselect . "<option value=\"Tray\">Tray</option>";
	$devicepackagingselect = $devicepackagingselect . "<option value=\"Mixed\">Mixed</option>";

	// select previously selected item if required on page submission and form re-display
	if ($devicepackaging != ""){
		$devicepackagingselect = str_replace("value=\"Loose\" selected>","value=\"Loose\">",$devicepackagingselect);
		$devicepackagingselect = str_replace("value=\"$devicepackaging\">","value=\"$devicepackaging\" selected>",$devicepackagingselect);
	}

    echo "<br>";
	echo "This site is under construction and new features will be added as we progress.";
	echo "<br>";
	echo "<br>";
	echo "Import: <a href=\"$importinventoryurl\">Import inventory from a CSV</a>";
	echo "<br>";
	echo "Export: <a href=\"$exportinventoryurl\">Export full inventory to an XLS</a>";
	echo "<br>";
	echo "<br>";

		echo "<div class=\"sggreen sg14 sgb\">$message</div>";

		echo "<form method=\"POST\" action=\"$_SERVER[PHP_SELF]\" name=\"form\" id=\"form\">";
			echo "<input type=\"hidden\" name=\"submitinhibit\" id=\"submitinhibit\" VALUE=\"submitok\">";
			echo "<input type=\"hidden\" name=\"submitted\" id=\"submitted\" VALUE=\"1\">";
			echo "<input type=\"hidden\" name=\"edit\" id=\"edit\" VALUE=\"$edit\">";
			echo "<input type=\"hidden\" name=\"deviceid\" id=\"deviceid\" VALUE=\"$deviceid\">";

			echo "<table width=\"100%\" class=\"bgcolour6 sg13\" border=\"1\">";
				echo "<tr>";
					echo "<td width=\"5%\" align=\"left\">Bin</td>";
					echo "<td width=\"10%\" align=\"left\">Part Number</td>";
					echo "<td width=\"5%\" align=\"left\">Package</td>";
					echo "<td width=\"7%\" align=\"left\">Type</td>";
					echo "<td width=\"15%\" align=\"left\">Description</td>";
					echo "<td width=\"5%\" align=\"left\">Quantity</td>";
					echo "<td width=\"5%\" align=\"left\">Packaging</td>";
					echo "<td width=\"10%\" align=\"left\">Project</td>";
					echo "<td width=\"15%\" align=\"left\">Link</td>";
					echo "<td width=\"15%\" align=\"left\">$submitbuttontext $searchheadertext device</td>";
				echo "</tr>";

				echo "<tr>";
					echo "<td align=\"left\" class=\"sg13\"><input name=\"devicebinlocation\" id=\"devicebinlocation\" class=\"autowidth\" maxlength=\"30\" value=\"$devicebinlocation\" title=\"Enter the devices bin location.\"></td>";
					echo "<td align=\"left\" class=\"sg13\"><input name=\"devicenumber\" id=\"devicenumber\" class=\"autowidth\" maxlength=\"110\" value=\"$devicenumber\" title=\"Enter the official device number.\"></td>";
					echo "<td align=\"left\" class=\"sg13\"><input name=\"devicepackage\" id=\"devicepackage\" class=\"autowidth\" maxlength=\"30\" value=\"$devicepackage\" title=\"Enter the devices package type.\"></td>";
					echo "<td align=\"left\" class=\"sg13\"><select name=\"devicetype\" id=\"devicetype\" title=\"Enter the devices generic type.\">$devicetypeselect</select></td>";
					echo "<td align=\"left\" class=\"sg13\"><input name=\"devicedescription\" id=\"devicedescription\" class=\"autowidth\" maxlength=\"230\" value=\"$devicedescription\" title=\"Enter the devices description.\"></td>";
					echo "<td align=\"left\" class=\"sg13\"><input name=\"devicequantity\" id=\"devicequantity\" class=\"autowidth\" maxlength=\"8\" value=\"$devicequantity\" title=\"Enter the devices quantity.\"></td>";
					echo "<td align=\"left\" class=\"sg13\"><select name=\"devicepackaging\" id=\"devicepackaging\" title=\"Enter the devices packaging type.\">$devicepackagingselect</select></td>";
					echo "<td align=\"left\" class=\"sg13\"><input name=\"project\" id=\"project\" class=\"autowidth\" maxlength=\"60\" value=\"$project\" title=\"Enter the devices associated project.\"></td>";
					echo "<td align=\"left\" class=\"sg13\"><input name=\"devicelink\" id=\"devicelink\" class=\"autowidth\" maxlength=\"230\" value=\"$devicelink\" title=\"Enter the devices supplier or datasheet links.\"></td>";
					echo "<td align=\"left\" class=\"sg13\"><input type=\"submit\" name=\"submit\" id=\"submit\" class=\"submitbutton\" VALUE=\"$submitbuttontext\" style=\"font-family: Tahoma\"> $searchbutton</td>";
				echo "</tr>";
			echo "</table>";
		echo "</form>";

		echo "<br>";

		// DISPLAY ONLY THE ENTRY FOR THE MATCHING ID
		if($getid == 1){
			$sqlgetid = "AND `deviceid` = '$deviceid'";
		}

		// handle searches
		if($search == 1){
			if($deviceid != ""){
				$sqlsearch = $sqlsearch . "AND `deviceid` LIKE '%$deviceid%'";
			}
			if($devicebinlocation != ""){
				$sqlsearch = $sqlsearch . "AND `devicebinlocation` LIKE '%$devicebinlocation%'";
			}
			if($devicenumber != ""){
				$sqlsearch = $sqlsearch . "AND `devicenumber` LIKE '%$devicenumber%'";
			}
			if($devicepackage != ""){
				$sqlsearch = $sqlsearch . "AND `devicepackage` LIKE '%$devicepackage%'";
			}
			if($devicetype != ""){
				$sqlsearch = $sqlsearch . "AND `devicetype` LIKE '%$devicetype%'";
			}
			if($devicedescription != ""){
				$sqlsearch = $sqlsearch . "AND `devicedescription` LIKE '%$devicedescription%'";
			}
			if($devicequantity != ""){
				$sqlsearch = $sqlsearch . "AND `devicequantity` LIKE '%$devicequantity%'";
			}
			if($devicepackaging != ""){
				$sqlsearch = $sqlsearch . "AND `devicepackaging` LIKE '%$devicepackaging%'";
			}
			if($project != ""){
				$sqlsearch = $sqlsearch . "AND `project` LIKE '%$project%'";
			}
			if($devicelink != ""){
				$sqlsearch = $sqlsearch . "AND `devicelink` LIKE '%$devicelink%'";
			}
			echo "<h3><a href=\"$_SERVER[PHP_SELF]\">Clear Search Display</a><h3><br>";
		}

		echo "<br>";

		$sql2 ="SELECT * FROM `$sqltable_devices_inventory` WHERE `devicenumber` != '' $sqlgetid $sqlsearch"; // make sure that the listing is in progress, to help prevent it being hacked !
		$result2 = mysqli_query($connection,$sql2);// or die("Some Error Occured");//die(mysqli_error());

		if (!$result2){
			$errorsql = addslashes($sql2);
			$errormsg = addslashes(mysqli_error($connection));
			$errorlog = mysqli_query($connection,"INSERT INTO `$sqltable_errorlog` VALUES ('$_SERVER[PHP_SELF]', '$errormsg', '$errorsql', '$timestamp', '', '$ip')");
			die("An Error Occurred, the error has been logged for the admin to investigate.");
		}

		//get the number of rows in the result set
		$num2 = mysqli_num_rows($result2);

		if($num2 != 0){
			echo "<table width=\"100%\" class=\"bgcolour6 sg13\" border=\"1\">";
				echo "<tr>";
					echo "<td width=\"5%\" align=\"left\">Bin</td>";
					echo "<td width=\"10%\" align=\"left\">Part Number</td>";
					echo "<td width=\"5%\" align=\"left\">Package</td>";
					echo "<td width=\"7%\" align=\"left\">Type</td>";
					echo "<td width=\"15%\" align=\"left\">Description</td>";
					echo "<td width=\"5%\" align=\"left\">Quantity</td>";
					echo "<td width=\"5%\" align=\"left\">Packaging</td>";
					echo "<td width=\"10%\" align=\"left\">Project</td>";
					echo "<td width=\"15%\" align=\"left\">Link</td>";
					echo "<td width=\"15%\" align=\"left\">Last Updated</td>";
				echo "</tr>";

			while($sql2 = mysqli_fetch_object($result2)) {
				$deviceid					= $sql2 -> deviceid;
				$devicenumber				= $sql2 -> devicenumber;
				$devicepackage			= $sql2 -> devicepackage;
				$devicetype				= $sql2 -> devicetype;
				$devicedescription		= $sql2 -> devicedescription;
				$devicequantity			= $sql2 -> devicequantity;
				$devicepackaging			= $sql2 -> devicepackaging;
				$devicebinlocation		= $sql2 -> devicebinlocation;
				$devicelink				= $sql2 -> devicelink;
				$datetime				= $sql2 -> datetime;
				$project				= $sql2 -> project;

				$datetimeview = date("g:ia D, d M Y", $datetime); // must NOT BE gmdate
				$devicelinklist = "";
				$devicelinklistfull = "";

				// add code here to add headers every 50 lines etc.
				if($rowbgcolour == "sgbgcolour1"){
					$rowbgcolour = "sgbgcolour3";
				}
				else{
					$rowbgcolour = "sgbgcolour1";
				}

				// attempt to parse URLs and turn them into links
				$devicelinkarray = explode(" ",$devicelink);
				foreach($devicelinkarray as $key => $value){
					$devicelinkarray[$key] = str_replace(",","",$devicelinkarray[$key]); // remove commas, in case someone puts them in
					if(preg_match("/http/",$devicelinkarray[$key])){
						$devicelinkarray[$key] = str_replace("http","<a href=\"http",$devicelinkarray[$key]);
						$devicelinklist = $devicelinkarray[$key]."\" target=\"_blank\">Link</a><br>";
					}
					else{
						$devicelinklist = $devicelinkarray[$key];
					}
					$devicelinklistfull = $devicelinklistfull." ".$devicelinklist;
				}


				echo "<tr class=\"$rowbgcolour\">";
					echo "<td align=\"left\" class=\"sg12\">$devicebinlocation</td>";
					echo "<td align=\"left\" class=\"sg12\">$devicenumber<br><a href=\"$_SERVER[PHP_SELF]?edit=1&deviceid=$deviceid\">Edit</a> <a href=\"$_SERVER[PHP_SELF]?delete=1&deviceid=$deviceid\">Delete</a></td>";
					echo "<td align=\"left\" class=\"sg12\">$devicepackage</td>";
					echo "<td align=\"left\" class=\"sg12\">$devicetype</td>";
					echo "<td align=\"left\" class=\"sg12\">$devicedescription</td>";
					echo "<td align=\"left\" class=\"sg12\">$devicequantity</td>";
					echo "<td align=\"left\" class=\"sg12\">$devicepackaging</td>";
					echo "<td align=\"left\" class=\"sg12\">$project</td>";
					echo "<td align=\"left\" class=\"sg12\">$devicelinklistfull</td>";
					echo "<td align=\"left\" class=\"sg10\">$datetimeview</td>";
				echo "</tr>";
			}
			echo "</table>";
		}
		// END CODE
		///////



    ?>
</div>

<br>
<br>
<br>



<?php
//include the footer file
include $path . "includes/pagefoot.php";
?>

