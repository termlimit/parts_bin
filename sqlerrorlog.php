<?php
require __DIR__ . '/config/setup.php';

/////// CONFIRMED WORKING
// BEGIN CODE handle special characters to stop malicious users
$deleteentry = strip_tags($_REQUEST["deleteentry"]);
$deleteerror = strip_tags($_REQUEST["deleteerror"]);
// END CODE
//////

/////// CONFIRMED WORKING
// BEGIN CODE handle special characters to stop malicious users
$deleteentry = htmlspecialchars($deleteentry, ENT_QUOTES);
$deleteerror = htmlspecialchars($deleteerror, ENT_QUOTES);
// END CODE
//////

/////// CONFIRMED WORKING
// BEGIN CODE handle special characters to stop malicious users
$deleteentry = (int)$deleteentry;
// END CODE
//////


if ($deleteerror == 1){
	//make query to the database
	$sql1213 ="SELECT * FROM $sqltable_errorlog WHERE `datetime` = '$deleteentry'";
	$result1213 = @mysqli_query($connection,$sql1213); // or die(mysqli_error());

	if (!$result1213){
		$errorsql = addslashes($sql1213);
		$errormsg = addslashes(mysqli_error($connection));
		$errorlog = mysqli_query($connection,"INSERT INTO `$sqltable_errorlog` VALUES ('$_SERVER[PHP_SELF]', '$errormsg', '$errorsql', '$timestamp', '$_SESSION[firstname] $_SESSION[lastname]', '$ip')");
		die("An Error Occurred, the error has been logged for the admin to investigate.");
	}

	//get the number of rows in the result set
	$num1213 = mysqli_num_rows($result1213);

	//print a message or redirect elsewhere,based on result
	if ($num1213 != 0){
		$sql1214 = "DELETE FROM $sqltable_errorlog WHERE `datetime` = '$deleteentry' LIMIT 1";
		$result1214 = @mysqli_query($connection,$sql1214); // or die(mysqli_error());

		if (!$result1214){
			$errorsql = addslashes($sql1214);
			$errormsg = addslashes(mysqli_error($connection));
			$errorlog = mysqli_query($connection,"INSERT INTO `$sqltable_errorlog` VALUES ('$_SERVER[PHP_SELF]', '$errormsg', '$errorsql', '$timestamp', '$_SESSION[firstname] $_SESSION[lastname]', '$ip')");
			die("An Error Occurred, the error has been logged for the admin to investigate.");
		}

		echo "<p>The error entry has been deleted</p>";
	}else{

	echo "<p>Entry could not be found for deletion.</p>";
	}
	$deleteerror = 0;
}


$pagesubtitle = "SQL Error Log";

//include the page top file
include $path . "includes/pagehead.php";
?>

<br>

<div class="sgc">
	<h2>MySQL Error log</h2>
</div>

<BR>

<div class="sgul sgc sg14 sgmargin0 sgdiv">

<?php


/////////////////
//BEGIN CODE get image table display
$sql217 ="SELECT * FROM `$sqltable_errorlog`";
$result217 = @mysqli_query($connection,$sql217); // or die("Some Error Occured");//die(mysqli_error());

if (!$result217){
	$errorsql = addslashes($sql217);
	$errormsg = addslashes(mysqli_error($connection));
	$errorlog = mysqli_query($connection,"INSERT INTO `$sqltable_errorlog` VALUES ('$_SERVER[PHP_SELF]', '$errormsg', '$errorsql', '$timestamp', '$_SESSION[firstname] $_SESSION[lastname]', '$ip')");
	die("An Error Occurred, the error has been logged for the admin to investigate.");
}

//get the number of rows in the result set
$num217 = mysqli_num_rows($result217);

if ($num217 != 0) {
	$bgcolor = "row1colour"; // set initial background colour

	echo "<table border=\"0\" width=\"95%\" align=\"center\" class=\"bgcolour6\">";
	echo "<tr class=\"bgcolour6\">";
	echo "<td align=\"center\" width=\"10%\">Page Name</td>";
	echo "<td align=\"center\" width=\"20%\">SQL Error</td>";
	echo "<td align=\"center\" width=\"39%\">SQL Query</td>";
	echo "<td align=\"center\" width=\"8%\">IP Address</td>";
	echo "<td align=\"center\" width=\"15%\">Date Time</td>";
	echo "</tr>";

	while ($sql217 = mysqli_fetch_object($result217)) {
		if($bgcolor=="row1colour"){
			$bgcolor="row2colour";
		}else{
			$bgcolor="row1colour";
		}
		$sqlerrorpagename			= $sql217 -> pagename;
		$sqlerrorerror 				= $sql217 -> errormessage;
		$sqlerrorerrorsql 			= $sql217 -> errorsql;
		$sqlerrordatetime 			= $sql217 -> datetime;
		$sqlerrorip 				= $sql217 -> ip;

		$sqlerrordatetimeview	= gmdate($datetimeformat, $sqlerrordatetime); // confirmed correct

		echo "<tr class=\"$bgcolor\">";
		echo "<td align=\"center\"><span class=\"sg10\">$sqlerrorpagename<br><a href=\"$_SERVER[PHP_SELF]?deleteentry=$sqlerrordatetime&deleteerror=1\">Delete this entry</a></span></td>";
		echo "<td align=\"center\"><span class=\"sg10\">$sqlerrorerror</span></td>";
		echo "<td align=\"center\"><span class=\"sg10\">$sqlerrorerrorsql</span></td>";
		echo "<td align=\"left\"><span class=\"sg10\">$sqlerrorip</span></td>";
		echo "<td align=\"center\"><span class=\"sg10\">$sqlerrordatetimeview</span></td>";
		echo "</tr>";
	}
	echo "</table><BR><BR>";
}
else{
	echo "<br>There are no errors logged in the database.<BR><BR>";
}
// END CODE
///////////////



?>

</div>
<BR>

<?php
//include the footer file
include $path . "includes/pagefoot.php";
?>