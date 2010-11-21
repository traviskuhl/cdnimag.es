#!/usr/bin/php
<?php
	
	// include our class
	include("./CdnImages.php");

	// set some vars
	CdnImages::setup(array(
		"cname" => "demo.cdnimag.es"
	));

	echo "\nStarting Test...\n\n";

	echo " Signed Url:\n ";

	// return
	echo CdnImages::sign("/path/to/image.png",array('size'=>"100x100"));
	
	echo "\n\n";

?>