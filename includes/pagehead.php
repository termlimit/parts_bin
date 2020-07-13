<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<HTML>
<HEAD>
<TITLE><?php echo $pagetitle; ?></TITLE>

<META HTTP-EQUIV="Pragma" CONTENT="no-cache"><meta http-equiv="Content-Language" content="en-us">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="description" content="<?php echo $description; ?> provides a FREE Parts Inventory System for Makers & Hobbyests that want to keep track of all of their electronic parts.">
<meta name="keywords" content="hobbiest,parts list,inventory system,electronic part,parts storage,electronics devices inventory,parts inventory,parts list,bom list, makers,hobbyist, electronics parts storage,component storage">
<meta name="author" content="The Defpom">
<meta name="publisher" content="MyPartsBin">
<meta name="copyright" content="MyPartsBin.com">
<meta name="robots" content="all">
<meta name="GENERATOR" CONTENT="User-Agent: Mozilla/3.01Gold (Macintosh; I; PPC)">
<meta name="rating" content="NONE">
<meta name="revisit-after" content="1 days">
<meta http-equiv="Content-Language" content="en">
<!--<meta name=viewport content="width=device-width, initial-scale=1"> commented out, as although it gives a better score on google, the display doesnt look very good on mobile devices, scaled is better-->


<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
<link rel="icon" href="/favicon.ico" type="image/x-icon">

<link rel="stylesheet" href="/css/styles.css" type="text/css">

<?php

// turn off enter key, to block accidental form submissions
if($disableenterkey == "1"){
	echo "<script type=\"text/javascript\">
	function stopRKey(evt) {
	  var evt = (evt) ? evt : ((event) ? event : null);
	  var node = (evt.target) ? evt.target : ((evt.srcElement) ? evt.srcElement : null);
	  if(evt.keyCode == 13 && (node.type==\"text\" || node.type==\"number\")){
		if(evt.preventDefault){
			evt.preventDefault();
		}
		if(evt.stopPropagation){
			evt.stopPropagation();
	  	}
	  	return false;
	  }
	}
	document.onkeypress = stopRKey;
	</script>";
}

?>

</HEAD>
<BODY>


<?php
// switch to hide the top logo and banner on pages that need to be slimmed down
if($hidelogo != "1"){
	echo "<div align=\"center\"><a href=\"$mainpageurl\"><img src=\"/images/sitebannerdual.png\" WIDTH=\"600px\" HEIGHT=\"139px\" alt=\"$hostsitename\"></a></div>";
}


echo "<hr>";

// switch to hide the top links list on pages that need to be slimmed down
if($hidetoplinks != "1"){
	echo "<div class=\"sgc sg14\">";
		echo "<a href=\"$mainpageurl\">Home</a> | ";
		echo "<a href=\"$devicesinventorypageurl\">My Parts Inventory</a> | ";
		echo "<a href=\"$exportinventoryurl\">Export Inventory</a> | ";
		echo "<a href=\"$importinventoryurl\">Import Inventory</a> | ";
		echo "<a href=\"$sqlerrorlogurl\">Error Log</a>";
	echo "<br>";
echo "</div>";

}
?>