#!/usr/bin/php
<?php
	
	// include our class
	include("./CdnImages.php");

	// set some vars
	CdnImages::setup(array(
		"domain"	=> "test.cdnimag.es",
		"key"		=> "diYot8KJv2YE9RqZ9CtX",
		"secret"	=> "OTQbc0nLiiwmg4GmVX8d8uzsfMrYta",
		"cname"		=> "idev.cdnimag.es",
		"api"		=> "api.dev.cdnimag.es"
	));

	echo "\nStarting Test...\n\n";
/*

	// upload
	echo " Upload an image:\n ";
	
	// name
	$name = uniqid("test-photo-");

	// try to post
	try {
		echo CdnImages::post("./f3.jpg", "test/{$name}.jpg" );		
	}
	catch (Exception $e) {  echo "ERROR!! ".$e->getMessage(); }
	
	echo "\n\n";

	echo " Signed Url:\n ";

	// return
	echo CdnImages::sign("/test/{$name}.jpg",array('test.cdnimag.es', 'size'=>"100x100"));
	
	echo "\n\n";
	
*/
	// string
	echo " Sign String:\n ";
	
	$str = "
		<img src='/test/f2.jpg' width='200' height='200' />
		<img src='/test/f2.jpg' class='cdn-ignore'>
		<img style='background-image: url(/test/f2.jpg);'>
		<img style='background: url(/test/f2.jpg) no-repeat; width:250px; height: 250px'>
	";
	
	echo CdnImages::signString($str);


?>