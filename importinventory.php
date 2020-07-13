<?php
ini_set('max_input_time',7200); // set timeout to 2 hours
ini_set('max_execution_time',3600); // set timeout to 60 minutes
ini_set('upload_max_filesize',"20MB"); // set file size to 20MB
ini_set('post_max_size',"40MB"); // set total POST size to 40MB (all files and text)
//ini_set('session.gc_maxlifetime', 7200); // set cleaning timeout to 2 hours to try and preserve session data
ini_set('auto_detect_line_endings',TRUE);

//error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

//prevents caching
header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Cache-Control: no-store, no-cache, private, must-revalidate");
session_cache_limiter("nocache, must-revalidate");


$path = ""; // add ../ to increase levels eg $path = "../"; if this page is in a subdirectory

//require the config file
require $path . ("config.php");


/////// CONFIRMED WORKING
// BEGIN CODE handle special characters to stop malicious users
$importtype = strip_tags($_REQUEST["importtype"]);
$submitted = strip_tags($_REQUEST["submitted"]);
$_FILES['attachmentone']['name'] = strip_tags($_FILES['attachmentone']['name']);
// END CODE
//////

/////// CONFIRMED WORKING
// BEGIN CODE handle special characters to stop malicious users
$importtype = htmlspecialchars($importtype, ENT_QUOTES);
$submitted = htmlspecialchars($submitted, ENT_QUOTES);
$_FILES['attachmentone']['name'] = htmlspecialchars($_FILES['attachmentone']['name'], ENT_QUOTES);
// END CODE
//////

/////// CONFIRMED WORKING
// BEGIN CODE handle special characters to stop malicious users
$submitted = (int)$submitted;


$pagesubtitle = "Inventory Database Upload Page";

//include the page top file
include $path . "includes/pagehead.php";

?>

<br>

<div class="sgc">
<h2>Inventory Database Upload Page</h2>
</div>

<br>

<?php
if ($submitted == 1){
	$filename = $_FILES['attachmentone']['name'];
	$filenamesave = "database.csv";
	$fileid = $_FILES['attachmentone']['tmp_name'];
	$fileidtype = $_FILES['attachmentone']['type'];


	$filesize = ""; // reset size variable

	// check to see if file is being used, if not then skip process
	if($filename != ""){
        // work out file type extension to check it is the right type
        $fileextension = pathinfo($filename, PATHINFO_EXTENSION);

        // check that the file is the right type
        if($fileextension == "csv" || $fileidtype == "csv"){
    		$add = $path."uploads/".$filenamesave; // the path with the file name where the file will be stored, upload is the directory name.

    		if(@move_uploaded_file ($fileid, $add)){
    			@chmod("$add",0666); // was 0766, 0777 which is less secure
    			$fileuploadedok = 1;
                // Generate list of files uploaded successfully
                echo "<span class=\"sgb\">$filename ($filenamesave) Upload Successful</span><br>";
    		}
    		else{
    			echo "<br><center><h2>File upload failed</h2><BR><BR>This is probably because the file size was too large, or you need to change the file name.<br><br>Please go back and try again, remember to refer to the maximum file size note on the upload form.</center><br><BR><BR>";
                echo "<span class=\"sgb\">$filename ($filenamesave) Upload Failed !!!!</span><br>";
                exit;
    		}

            // open csv file, and import data into mysql database, does this one row at a time
            $rowcounter = 0;
            $handle = fopen("$add", "r");
            if($handle){

				// ADD SWITCHES TO ALLOW FOR REPLACEMENT, ADDING, OR MERGING

				if($importtype == "replace"){
					// DELETE ALL of the users existing inventory
					$sql72b ="DELETE FROM `$sqltable_devices_inventory`";
					$result72b = @mysqli_query($connection,$sql72b);// or die("Some Error Occured"); //or die(mysqli_error());

					if (!$result72b){
						$errorsql = addslashes($sql72b);
						$errormsg = addslashes(mysqli_error($connection));
						$errorlog = mysqli_query($connection,"INSERT INTO `$sqltable_errorlog` VALUES ('$_SERVER[PHP_SELF]', '$errormsg', '$errorsql', '$timestamp', '', '$ip')");
						die("An Error Occurred, the error has been logged for the admin to investigate.");
					}
					else{
						echo "<br><h3>Existing Inventory Was Deleted.</h3><br>";
					}
				}



                // now read csv file and generate data for insertion into table
                while (($data = fgetcsv($handle, 2048, ",")) !== FALSE){ // ,'"'
                    $rowcounter = $rowcounter + 1; // count number of rows, so check of table can be made later to be sure they all inserted correctly

                    if($rowcounter == 1){
                        // ignore first row, so it does not import headers
                    }
                    else{
						// try to catch overflowed data by stripping off anything extra that shouldnt be there
						$columncount = 10; // sets the number of columns, anything more than this will be stripped UPDATE THIS IF COLUMNS ARE CHANGED !!!
						$arraysize = count($data);// gets the number found

						// try to strip array to correct length
						for($i = $columncount; $i < $arraysize; $i++){
							$dummy1 = array_pop($data);
						}

                        // ignore first row, so it does not import headers
                        // define the data array items to match the table column order
                        $devicebinlocation = $data[0];
                        $devicenumber = $data[1];
                        $devicepackage = $data[2];
                        $devicetype = $data[3];
                        $devicedescription = $data[4];
                        $devicequantity = $data[5];
                        $devicepackaging = $data[6];
                        $project = $data[7];
                        $devicelink = $data[8];
                        $lastupdated = $data[9];
                        //$dummy = $data[11];
                        //$dummy = $data[12];
                        //$dummy = $data[13];
                        //$sqldogheightmeasured = $data[12];
                        //$sqldogsex = $data[13];
                        //$sqldogheight2019 = $data[14];


                        $devicenumber = htmlspecialchars($devicenumber, ENT_QUOTES);
                        $devicepackage = htmlspecialchars($devicepackage, ENT_QUOTES);
                        $devicetype = htmlspecialchars($devicetype, ENT_QUOTES);
                        $devicedescription = htmlspecialchars($devicedescription, ENT_QUOTES);
                        $devicequantity = htmlspecialchars($devicequantity, ENT_QUOTES);
                        $devicepackaging = htmlspecialchars($devicepackaging, ENT_QUOTES);
                        $devicebinlocation = htmlspecialchars($devicebinlocation, ENT_QUOTES);
                        $devicelink = htmlspecialchars($devicelink, ENT_QUOTES);
                        $deviceleadtime = htmlspecialchars($deviceleadtime, ENT_QUOTES);
                        $project = htmlspecialchars($project, ENT_QUOTES);
                        $lastupdated = htmlspecialchars($lastupdated, ENT_QUOTES);

                        $devicenumber = addslashes($devicenumber);
                        $devicepackage = addslashes($devicepackage);
                        $devicetype = addslashes($devicetype);
                        $devicedescription = addslashes($devicedescription);
                        $devicequantity = addslashes($devicequantity);
                        $devicepackaging = addslashes($devicepackaging);
                        $devicebinlocation = addslashes($devicebinlocation);
                        $devicelink = addslashes($devicelink);
                        $project = addslashes($project);
                        $lastupdated = addslashes($lastupdated);


                        $devicenumber = trim($devicenumber);
                        $devicepackage = trim($devicepackage);
                        $devicetype = trim($devicetype);
                        $devicedescription = trim($devicedescription);
                        $devicequantity = trim($devicequantity);
                        $devicepackaging = trim($devicepackaging);
                        $devicebinlocation = trim($devicebinlocation);
                        $devicelink = trim($devicelink);
                        $project = trim($project);
                        $lastupdated = trim($lastupdated);

						// skip blank rows
						if($devicenumber != ""){
							// PUT CODE HERE TO ALLOW MERGING OF DATA, (LOOKUP FOR device NUMBER, PACKAGE, PACKAGING, BIN), AND ADD THEM TOGETHER AND UPDATE EXISTING INSTEAD OF INSERTING A NEW ITEM
							if($importtype == "merge" || $importtype == "mergeupdate"){
								$sql2 ="SELECT * FROM `$sqltable_devices_inventory` WHERE `devicenumber` = '$devicenumber' AND `devicepackage` = '$devicepackage' AND `devicepackaging` = '$devicepackaging' LIMIT 1"; // make sure that the listing is in progress, to help prevent it being hacked !
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
									while($sql2 = mysqli_fetch_object($result2)) {
										$thisdeviceid					= $sql2 -> deviceid;
										//$thisdevicenumber				= $sql2 -> devicenumber;
										//$thisdevicepackage			= $sql2 -> devicepackage;
										$thisdevicetype				= $sql2 -> devicetype;
										$thisdevicedescription		= $sql2 -> devicedescription;
										$thisdevicequantity			= $sql2 -> devicequantity;
										//$thisdevicepackaging			= $sql2 -> devicepackaging;
										$thisdevicebinlocation		= $sql2 -> devicebinlocation;
										$thisdevicelink				= $sql2 -> devicelink;
										//$thisdatetime				= $sql2 -> datetime;
										$thisproject				= $sql2 -> project;


										// check if existing information needs updating
										if($thisdevicetype != $devicetype){
											$devicetype = $thisdevicetype." ".$devicetype;
											$devicetype = trim($devicetype);
										}

										if($thisdevicedescription != $devicedescription){
											$devicedescription = $thisdevicedescription." ".$devicedescription;
											$devicedescription = trim($devicedescription);
										}
										if($importtype == "merge"){
											$devicequantity = $thisdevicequantity + $devicequantity; // add qty together
										}
										else{ // mergeupdate - assume its an update and replace qty in db with new one uploaded
										}

										if($thisdevicedescription != $devicedescription){
											$devicedescription = $thisdevicedescription." ".$devicedescription;
											$devicedescription = trim($devicedescription);
										}

										if($thisdevicebinlocation != $devicebinlocation){
											$devicebinlocation = $thisdevicebinlocation." ".$devicebinlocation;
											$devicebinlocation = trim($devicebinlocation);
										}

										if($thisdevicelink != $devicelink){
											$devicelink = $thisdevicelink." ".$devicelink;
											$devicelink = trim($devicelink);
										}


										if($thisproject != $project){
											$project = $thisproject." ".$project;
											$project = trim($project);
										}


										/// UPDATE DATABASE WITH NEW INFO
										$sql118 = "UPDATE `$sqltable_devices_inventory`
													SET
													`devicetype` = '$devicetype',
													`devicedescription` = '$devicedescription',
													`devicequantity` = '$devicequantity',
													`devicebinlocation` = '$devicebinlocation',
													`devicelink` = '$devicelink',
													`project` = '$project',
													`datetime` = '$timestamp'
													WHERE `deviceid` = '$thisdeviceid' LIMIT 1"; //could be a problem, using SESSION OR POST is a security issue !!

										$result118 = @mysqli_query($connection,$sql118);// or die("Some Error Occured");//die(mysqli_error());

										if (!$result118){
											$errorsql = addslashes($sql118);
											$errormsg = addslashes(mysqli_error($connection));
											$errorlog = mysqli_query($connection,"INSERT INTO `$sqltable_errorlog` VALUES ('$_SERVER[PHP_SELF]', '$errormsg', '$errorsql', '$timestamp', '', '$ip')");
											die("An Error Occurred, the error has been logged for the admin to investigate.");
										}
										else{
											echo  "Updated existing $devicenumber.<br>";
										}

									}
									$addthispart = 0; //flag to NOT add device to db for the merge as it updated it
								}
								else{ //if device not found, then add it
									$addthispart = 1; //flag to add device instead of updating it
								}
							}

							$spare = "";

							if( ($importtype != "merge" && $importtype != "mergeupdate") || $addthispart == 1){ // an add or replace
								// save row into table
								$sql45 = "INSERT INTO `$sqltable_devices_inventory` VALUES
											(
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

								$result45 = @mysqli_query($connection,$sql45);// or die("Some Error Occured");//die(mysqli_error());

								if (!$result45){
									$errorsql = addslashes($sql45);
									$errormsg = addslashes(mysqli_error($connection));
									$errorlog = mysqli_query($connection,"INSERT INTO `$sqltable_errorlog` VALUES ('$_SERVER[PHP_SELF]', '$errormsg', '$errorsql', '$timestamp', '', '$ip')");
									die("An Error Occurred, the error has been logged for the admin to investigate.");
								}
								else{
									echo "$devicenumber added.<br>";
								}
							}
						}
						// end skip blank rows

                    }
                }
                // end while loop

    	        //echo "<br><h3>Inventory database was successfully uploaded.</h3><br>";

            }
			fclose($handle);

        }
        else{
	       echo "<br><h3>Upload failed, file was not in a CSV format.</h3><br>";
        }
    }
    else{
	   echo "<br><h3>Upload failed, file was not specified, OR file size exceeded the memory limit.</h3><br>";
    }
}
else{
	echo "<div class=\"sgc\">
            <h3>This page is used to upload an Inventory File in CSV format to the site.</h3>
	    </div>

		<CENTER>
		<HR WIDTH=\"100%\"></CENTER>
		<BR>

    <div class=\"sgul sgmargin0 sgdiv sg12\">
    	There are four options for adding parts to the sites inventory list:<br>
    	<ol>
			<li>\"Replace\" which will delete the existing site inventory and replace it with the one being uploaded.</li>
			<li>\"Add\" which will add the uploaded file to the existing site inventory.</li>
			<li>\"Add/Merge\" which will try to combine the uploaded file with the existing site inventory to add the total number of parts on the site and in the file together (or it will add to site inventory if its not found), this will also append the uploaded item info to the existing one if it is different,</li>
			<li>\"Add/Merge/Update\" which will try to replace the part quantities on the site with the ones from the uploaded file (or it will add to site inventory if its not found), this will also append the uploaded item info to the existing one if it is different.</li>
    	</ol>

    	<br>
    	<br>
    	NOTE: The first row is ignored to allow for any headers in the file !
    	<br>
    	<br>
		CSV Format: Bin, Part Number, Package, Name, Description, Quantity, Packaging, Project, Link, Last Updated (This is ignored, the current time is used by the site)<br>
		To Get Started: <a href=\"".$filesdirectoryurl."Inventory_Template.csv\">Download The Inventory Template</a><br>



		<FORM ENCTYPE=\"multipart/form-data\" METHOD=\"POST\" ACTION=\"$_SERVER[PHP_SELF]?submitted=1\" name=\"form\">
    		<input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"10380902\">

    		<fieldset style=\"width:700px; padding:15px;\">
				<legend><h4>Inventory CSV file</h4></legend>

				<select class=\"sg11\" name=\"importtype\" id=\"importtype\" style=\"font-family: Tahoma\">";
					$importtypeoption = "";
					$importtypeoption = $importtypeoption . "<option value=\"replace\">Replace Existing Inventory - Replaces Everything on Site With CSV File</option>";
					$importtypeoption = $importtypeoption . "<option value=\"add\">Add To Existing Inventory - Adds Everything From CSV File</option>";
					$importtypeoption = $importtypeoption . "<option value=\"merge\">Add/Merge With Existing Inventory - Combines Part Quantity With Quantity From CSV File</option>";
					$importtypeoption = $importtypeoption . "<option value=\"mergeupdate\" selected>Add/Merge/Update Existing Inventory - Replaces Part Quantity With Quantity From CSV File</option>";

					if($importtype != ""){
						$importtypeoption = str_replace("=\"mergeupdate\" selected>","=\"mergeupdate\">",$importtypeoption);
						$importtypeoption = str_replace("=\"$importtype\">","=\"$importtype\" selected>",$importtypeoption);
					}

					echo $importtypeoption;

					echo "
				</select>
				<div class=\"sgl\" name=\"file1div\" id=\"file1div\">
					File: <input type=\"file\" name=\"attachmentone\" size=\"40\"> (<a onclick=\"clearFileInputField('file1div')\" href=\"javascript:noAction();\">Clear</a>)<BR>
				</div>
				<br>
				<CENTER>
				<INPUT name=\"submit\" type=\"submit\" value=\"Upload Inventory Database\"></CENTER>
            </fieldset>

            <br>
    		  <span class=\"sg12 sgb\">(Note, the maximum individual size of a file that can be sent is 7.5MB)</span> <BR>
    		 <BR>
    		<br>

		</FORM>
    </div>";
}
?>



<?php
//include the footer file
include $path . ("includes/pagefoot.php");
?>