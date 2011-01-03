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
	$account = $domain = new \dao\account('get',array('domain', $name));
	
	// no account send home
	if ( $account->id == false ) {
		header("Location:http://cdnimag.es?error=no_acct_server"); exit;
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
	
		// cmdParts
		$cmdParts = explode("/", trim($parts[0],'/') );	
		
		// if dist is default, we need to get the first
		// param which should be their doman
		if ( $name == 'idev.cdnimag.es' OR $name == 'default.cdnimag.es' ) {
		
			// get their domain
			$d = array_shift($cmdParts);			
			
			// domain
			$domain = new \dao\account("get", array('domain', $d));
			
			// bad domain?
			if ( $domain->id === false ) {
				error("Could not find provided domain", 403);			
			}			
			
		}
	
		// spliut out each bucket
		foreach ( $cmdParts as $cmd ) {
			if ( strpos($cmd, ':') !== false ) {
				list($name, $val) = explode(":", $cmd);
				$cmds[$name] = $val;
			}
		}				
		
		// if dist is default, no buckets allowed
		if ( $domain->dist_default ) {
			
			// no buckets
			unset($cmds['bucket']);
			
			// reset account to default
			$account = new \dao\account('get',array('domain', 'default.cdnimag.es'));
			
			// and push it onto part 2
			$parts[1] = $domain->domain . "/" . trim($parts[1],'/');
			
		}		
				
		
	// figure out which bucket 
	if ( !isset($cmds['bucket']) ) {	
	
		// loop through all and find one
		foreach ( $account->buckets as $item ) {
			if ( $item->default === true ) {
				$cmds['bucket'] = ($item->alias ? $item->alias : $item->name); break;
			}
		}
	
		// none selected
		if ( !isset($cmds['bucket']) ) {
			$cmds['bucket'] = $account->buckets->item('first')->name;
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
	if ( $b->sig == true AND $domain->domain != 'demo.cdnimag.es' ) {
			
		// no sig
		if ( !isset($cmds['sig']) ) {
			error("No signature provided", 400);
		}	
		
		// no sig
		$nosig = preg_replace(array("#sig\:([a-zA-Z0-9]+)#","#//#"), array("","/"), $path);
		
		// match our sig
		if ( $cmds['sig'] != md5($domain->cred->sig.$nosig) ) {
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
	if ( !is_object($obj) OR ( is_object($obj) AND $obj->code != 200 ) ) {
	
		// check to see if we have a src command
		if ( isset($cmds['src']) ) {
		
			// url
			$url = filter_var(base64_decode($cmds['src']), FILTER_VALIDATE_URL);
		
			// need a good url
			if ( $url ) {
			
				// our command
				$cmd = "curl -sL " . escapeshellarg($url);
			
				// try to get the image
				$img = `$cmd`;
				
				// if there's an image
				if ( $img ) {
					
					// header
					$header = array("X-CdnImages-Src" => $url);

					// right content type
					switch(array_pop( explode(".", $url) )) {
						case 'png': $header['Content-Type'] = "image/png"; break;
						case 'jpg': $header['Content-Type'] = "image/jpeg"; break;
						case 'gif': $header['Content-Type'] = "image/gif"; break;
					};	
					
					// transfer it to s3 at path
					$r = $s3->putObject($img, $b->name, trim($parts[1],"/"), S3::ACL_PUBLIC_READ, $header, $header);
				
					// if good lets now get it 
					if ( $r ) {
						$obj = $s3->getObject($b->name, trim($parts[1],"/"));
					}
				
				}
				else {
					error("Could not find image.", 404);				
				}
				
			}
			else {
				error("Could not find image.", 404);			
			}
			
		}
		else {
			error("Could not find image.", 404);
		}
	}	
		
	// type
	if ( !isset($cmds['output']) ) {	
		switch($obj->headers['type']) {
			case 'image/png': $cmds['output'] = "png"; break;
			case 'image/jpeg': $cmds['output'] = "jpg"; break;
			case 'image/gif': $cmds['output'] = "gif"; break;
			default: $cmds['output'] = "png";
		};			
	}

	// new i
	$i = new Imagick();
	
	$i->readimageblob($obj->body);

	// page
	$page = $i->getImagePage();
	
	$v = p('valign', 'center', $cmds);
	$h = p('halign', 'center', $cmds);

	// size
	if ( isset($cmds['size']) ) {
		list($cmds['width'], $cmds['height']) = explode("x", $cmds['size']);
	}
	else if ( isset($cmds['percent']) ) {
        $cmds['width'] = floor($cmds['percent'] / 100 * $page['width']);
        $cmds['height'] = floor($cmds['percent'] / 100 * $page['height']);	
	}	
	else {
		$cmds['width'] = $page['width'];
		$cmds['height'] = $page['height'];
	}
	
	
	// frame
	if ( isset($cmds['frame']) ) {
	
		$width = $cmds['width'];
		$height = $cmds['height'];
	
		// ratio
		$ratio_orig = $page['width'] / $page['height'];
		
		if ( $page['width'] > $page['height'] ) {
			if ($width/$height < $ratio_orig) {
			   $width = $height*$ratio_orig;
			} else {
			   $height = $width/$ratio_orig;
			}					
		}
		else {
			if ($width/$height < $ratio_orig) {
			   $width = $height*$ratio_orig;
			} else {
			   $height = $width/$ratio_orig;
			}					
		}
		
		// scale
		$i->scaleImage($width, $height);		
		
		// page
		$page['width'] = $width;
		$page['height'] = $height;
		
		// crop
		$cmds['crop'] = true;
		
	}	

	// scale image to min or max
	if ( isset($cmds['scale']) ) {	
		$i->scaleImage($cmds['width'], $cmds['height'], ($cmds['scale']=='max' ? true : false));
	}
	else if ( isset($cmds['crop']) ) {

        $width = $W  = $cmds['width'];
        $height = $H = $cmds['height'];

        $Y = _coord($v, $page['height'], $H);
        $X = _coord($h, $page['width'], $W);

		// crop me up
		$i->cropImage($width, $height, $X, $Y);

		// set page
		$i->setImagePage($width, $height, $X, $Y);

	}
	else {
		$i->thumbnailImage($cmds['width'], $cmds['height']);
	}

	// gray
	if ( isset($cmds['gray']) ) {
		$i->setImageType(imagick::IMGTYPE_GRAYSCALE);
	}
	
	// reflection
	if ( isset($cmds['reflection']) ) {
		
		// bg
		$bg = new ImagickPixel("#000000");
		$opacity = (float)p('opacity', .3, $cmds);
	
			// try it 
			try {
				
				// bg
				$_bg = p('bg', "000000", $cmds);				
				
				// color
				$bg = new ImagickPixel("#{$_bg}");
				
			}
			catch (Exception $e) {}
	
		// re
		$r = $i->clone();
		
		// flip it
		$r->flipImage();
	
		$gradient = new Imagick();
		
		$gradient->newPseudoImage($r->getImageWidth(), $r->getImageHeight(), "gradient:transparent-" . $bg->getColorAsString() );
		
		/* Composite the gradient on the reflection */
		$r->compositeImage($gradient, imagick::COMPOSITE_OVER, 0, 0);
		
		/* Add some opacity. Requires ImageMagick 6.2.9 or later */
		$r->setImageOpacity( $opacity );
		
		/* Create an empty canvas */
		$canvas = new Imagick();
		
		/* Canvas needs to be large enough to hold the both images */
		$width = $i->getImageWidth();
		$height = ($i->getImageHeight() * 2);
		$canvas->newImage($width, $height, $bg);
		$canvas->setImageFormat("png");
		
		/* Composite the original image and the reflection on the canvas */
		$canvas->compositeImage($i, imagick::COMPOSITE_OVER, 0, 0);
		$canvas->compositeImage($r, imagick::COMPOSITE_OVER, 0, $i->getImageHeight());	
	
		// reset 
		$i = $canvas;
	
	}
	
	// rotate
	if ( isset($cmds['rotate']) ) {
		$i->rotateImage(new ImagickPixel("transparent"), (int)$cmds['rotate']);
	}

	$type = false;
	
	// output
	switch($cmds['output']) {
		case 'png': $type = IMAGETYPE_PNG; break;
		case 'jpg': $type = IMAGETYPE_JPEG; break;
		case 'gif': $type = IMAGETYPE_GIF; break;
		default: error("Only PNG, JPEG & GIF outputs are supported", 400);
	};

	// e
	$e = getexp($b->expire);

	// header
	header("Content-Type:".image_type_to_mime_type($type));

	// cache stuff
	header("Date:".dt(time()) );
	header("Expires:".dt(time()+$e));
	header("Cache-Control:max-age=".$e);		
	header("Last-Modified:".dt($obj->headers['time']));			
	header("X-Powered-By: http://cdnimag.es");
	
    // says
    $says = array(
            "It's not me. It's You",
            "Live long and prosper",
            "Doing linear scans over an assoc array is like clubbing someone to death with a loaded Uzi",
            "Men have become the tools of their tools.",
            "We all go a little mad sometimes.",
            "So I got that goin' for me, which is nice.",
            "I have one simple request. And that is to have sharks with frickin' laser beams attached to their heads!",
            "Gentlemen, you can't fight in here. This is the War Room!",
            "These go to 11.",
            "Have fun storming the castle!",
            "I saved latin.",
            "Chuck Norris resizes his images on the fly",
            "Chuck Norris resizes his images on the fly",
            "Chuck Norris resizes his images on the fly",
            "Chuck Norris resizes his images on the fly",                        
    );	
	
	// says
	header("X-CdnImages-Says: {$says[array_rand($says)]}");

	// type
	switch($type) {
		case IMAGETYPE_PNG: $i->setImageFormat("png"); break;
		case IMAGETYPE_JPEG: $i->setImageFormat("jpeg"); break;
		case IMAGETYPE_GIF: $i->setImageFormat("gif"); break;
	};	
	
	// print 
	exit($i);


function _coord($align, $param, $src) {
    if ( $align == 'left' OR $align == 'top') {
        $result = 0;
    } elseif ( $align == "left" OR $align == 'bottom') {
        $result = $param - $src;
    } else {
        $result = ($param - $src) >> 1;
    }
    return $result;
}

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