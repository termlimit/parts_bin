<?php
// turn on output buffering, to speed up php processing
//ob_start();
//ini_set('zlib.output_compression_level', 3);
//ob_start("ob_gzhandler");

//error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);


//prevents caching
header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
header("Cache-Control: post-check=0, pre-check=0",false);

session_start();

$path = ""; // add ../ to increase levels eg $path = "../"; if this page is in a subdirectory

//require the config file
require $path . ("config.php");

$pagesubtitle = "Welcome";

//include the page top file
include $path . "includes/pagehead.php";
?>

<br>
<h2 class="sgc">Welcome to <?php echo $hostsitename; ?></h2>
<br>

<div class="sgul sg14 sgmargin0 sgdiv">

	MyPartsBin is a Parts Inventory System designed to be simple and easy to use, with Hobbyists and Makers in mind.<br>
	<br>
	You can add your parts to a database to allow you to keep track of the items you have in stock, with various classifications and you can even add links to where to buy them or see datasheets etc.<br>
	There is a super fast and easy to use search function to make it easy to find your parts, or find out what parts are stored in which location.<br>
	<br>

	You are able to run on your own local webserver without needing an internet connection / website account.<br>
	This open source version has the ability to backup to the MyPartsBin site (if you choose) in case something horrible happens to your local webserver or computer.<br>
	<br>


    <?php

	echo "<ul class=\"sgul2 sgb\">";
		    echo "<br>";
            echo "<li><img src=\"/images/bullet.jpg\" class=\"sgimgalign\" WIDTH=\"30\" HEIGHT=\"20\" alt=\"item\"> <a href=\"$devicesinventorypageurl\">My Parts Inventory</a></li>";
            echo "<li><img src=\"/images/bullet.jpg\" class=\"sgimgalign\" WIDTH=\"30\" HEIGHT=\"20\" alt=\"item\"> <a href=\"$exportinventoryurl\">Export Inventory</a></li>";
            echo "<li><img src=\"/images/bullet.jpg\" class=\"sgimgalign\" WIDTH=\"30\" HEIGHT=\"20\" alt=\"item\"> <a href=\"$importinventoryurl\">Import Inventory</a></li>";
		    echo "<br>";


	echo "</ul>";
	echo "<br>";
	echo "<h3>Brought to you by Termlimit, visit <a href=\"https://github.com/termlimit/" rel=\"nofollow\">GitHub</a> OR <a href=\"https://www.thingiverse.com/termlimit\" rel=\"nofollow\">Thingiverse</a></h3>";
    ?>
	<br>
</div>

<br>
<br>
<br>


<?php
//include the footer file
include $path . "includes/pagefoot.php";
?>