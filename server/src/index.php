<?php

	// framework
	define("bFramework",	"/home/bolt/share/pear/bolt/framework/");
	
	// include our Bold file
	require(bFramework . "Bolt.php");
	
	// image
	require_once 'Image/Tools.php';

	// path
	$path = p('path');

	// name
	$name = $_SERVER['SERVER_NAME'];

	// try to get an account
	$account = new \dao\account('get',array('domain', $name));
	
	// no account send home
	if ( $account->id == false ) {
		header("Location:http://cdnimag.es"); exit;
	}
	
	// path
	$path = p('path');

	// break up the path
	$parts = explode("$", $path);
	
		// need two parts
		if ( count($parts) != 2 ) {
			error("Need Commands, Sig & Path", 400);
		}
	
	// parse our our cmds
	$cmds = array();
	
		foreach ( explode("/", trim($parts[0],'/') ) as $cmd ) {
			list($name, $val) = explode(":", $cmd);
			$cmds[$name] = $val;
		}	
	
	// figure out which bucket 
	if ( !isset($cmds['bucket']) ) {
	
		// loop through all and find one
		foreach ( $account->buckets as $item ) {
			if ( $item->default === true ) {
				$cmds['bucket'] = ($item->alias ? $item->alias : $item->name); break;
			}
		}
	
	}
	
	// good
	$good = false;
	$b = false;
	
	// make sure it's valid 
	foreach ( $account->buckets as $item ) {
		if ( $item->name == $cmds['bucket'] OR $item->alias == $cmds['bucket'] ) {
			$good = true; $b = $item; break;
		}
	}
	
	// if the bucket has an alias we need to be using that
	if ( $b->alias AND $cmds['bucket'] != $b->alias ) {
		$good = false;
	}
	
	// it's bad
	if ( $good === false OR $b === false ) {
		error("Could not find bucket given.", 404);
	}
	
	// do we need to check sig
	if ( $b->sig == true ) {
			
		// no sig
		if ( !isset($cmds['sig']) ) {
			error("No signature provided", 400);
		}
		
		// no sig
		$nosig = preg_replace(array("#sig\:([a-zA-Z0-9]+)#","#//#"), array("","/"), $path);
		
		// match our sig
		if ( $cmds['sig'] != md5($account->cred->secret.$nosig) ) {
			error("Invalid Signature", 403);	
		}	
		
	}
	
	// s3
	$s3 = new S3( $account->aws->key, $account->aws->secret);

	// try to get this object
	try {
		$obj = $s3->getObject($b->name, trim($parts[1],"/"));
	}
	catch ( Exception $e ) { error("Could not request image", 404); }
	
	// not found
	if ( $obj->code != 200 ) {
		error("Could not find image.", 404);
	}

	// thumb
	$t = Image_Tools::factory('thumbnail');

	// get the image
	$t->set('image', $obj->body);
		
	// loop 
	foreach ( $cmds as $key => $value ) {
		switch($key) {
			
			// crop
			case 'crop':
				if ( $value == 'true' ) { $t->set('method', IMAGE_TOOLS_THUMBNAIL_METHOD_CROP); } break;
			
			// size
			case 'size':
				list($w, $h) = explode("x", $value);
						
				$t->set('width', (int)$w);
				$t->set('height', (int)$h);	break;			
			
			// percent
			case 'percent':
				$t->set('percent', (int)$value); break;
				
			// scale
			case 'scale':
				switch($value) {
					case "min": $t->set("valign", IMAGE_TOOLS_THUMBNAIL_METHOD_SCALE_MIN); break;
					case "max": $t->set("valign", IMAGE_TOOLS_THUMBNAIL_METHOD_SCALE_MAX); break;
				};
				break;
				
			// valign
			case 'valign':
				switch($value) {
					case "top": $t->set("valign", IMAGE_TOOLS_THUMBNAIL_ALIGN_TOP); break;
					case "bottom": $t->set("valign", IMAGE_TOOLS_THUMBNAIL_ALIGN_BOTTOM); break;
					case "center": $t->set("valign", IMAGE_TOOLS_THUMBNAIL_ALIGN_CENTER); break;
					default: $t->set("valign", (int)$value); 
				};
				break;

			// halign
			case 'halign':
				switch($value) {
					case "left": $t->set("halign", IMAGE_TOOLS_THUMBNAIL_ALIGN_LEFT); break;
					case "right": $t->set("halign", IMAGE_TOOLS_THUMBNAIL_ALIGN_RIGHT); break;
					case "center": $t->set("halign", IMAGE_TOOLS_THUMBNAIL_ALIGN_CENTER); break;					
					default: $t->set("halign", (int)$value); 
				};
				break;
			
		};
	}
	
	$type = false;
	
	// output
	if ( isset($cmds['output']) ) {
		switch($cmds['output']) {
			case 'png': $type = IMAGETYPE_PNG; break;
			case 'jpg': $type = IMAGETYPE_JPEG; break;
			case 'gif': $type = IMAGETYPE_GIF; break;
			default: error("Only PNG, JPEG & GIF outputs are supported", 400);
		};
	}
	else {
		switch($obj->headers['type']) {
			case 'image/png': $type = IMAGETYPE_PNG; break;
			case 'image/jpeg': $type = IMAGETYPE_JPEG; break;
			case 'image/gif': $type = IMAGETYPE_GIF; break;
			default: error("Only PNG, JPEG & GIF outputs are supported", 400);
		};	
	}	

	// e
	$e = getexp($b->expire);

	// header
	header("Content-Type:".image_type_to_mime_type($type));

	// cache stuff
	header("Date:".dt(time()) );
	header("Expires:".dt(time()+$e));
	header("Cache-Control:max-age=".$e);		
	header("Last-Modified:".dt($obj->headers['time']));			

	// do it 
	$t->display($type);

	// done
	exit();


// eroro
function error($msg, $code=500) {
	
	// send some headers
	header("Content-Type: text/html", true, $code);
	
	// expire after 6s
	$e = time()+60;
	
	// set a short expires
	header("Date:".dt(time()));
	header("Expires:".dt($e));
	header("X-CdnImages-Error: {$msg}");

	// done
	exit("<html><head></head><body></body></html>");

}

function dt($ts) {
	return date("D, d M Y H:i:s T", $ts);
}

function getexp($exp) {
	
	// none
	if ( $exp == "" ) { $exp = "10y"; }
	
	// $e
	$e = 0;
	
	// match
	$m = array(
		"m" => 60,
		"h" => (60*60),
		"d" => (60*60*24),
		"w" => (60*60*24*7),
		"t" => (60*60*24*30),
		"y" => (60*60*24*365)
	);
		
	foreach ( explode(" ", $exp) as $p ) {
		$l = substr($p,-1);
		if ( isset($m[$l]) ) {
			$e += ( (int)$p * $m[$l] );
		}
	}

	// give back
	return $e;

}

?>