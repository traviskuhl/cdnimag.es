<?php

class api extends FrontEnd {

	public static $format = "text/javascript";

	public function render($args) {
			
		// get our headers
		$key = p("HTTP_X_CDNIMAGES_KEY", false, $_SERVER);
		$sig = p("HTTP_X_CDNIMAGES_SIG", false, $_SERVER);
		
		// ua
		$ua = p("HTTP_USER_AGENT", false, $_SERVER);
		
		// accept
		self::$format = p("HTTP_ACCEPT", false, $_SERVER);
	
		// method
		$method = strtolower( p("REQUEST_METHOD", false, $_SERVER) );
	
		// get some other stuff too
		$domain = p('domain');
		$act = p('act');
		$image = p('image');
	
		// what func should we call
		$func = "{$method}_{$act}";
		
		// does it exit
		if ( !method_exists($this, $func) ) {
			$this->_error("Requested Method does not exist", 400);
		}
	
		// lets get an account
		$account = new \dao\account("get",array("domain", $domain));
		
			// no account
			if ( $account->id == 'false' ) {
				$this->_error("The requested domain does not exist", 404);
			}
			
			// bad key
			if ( $account->cred->key != $key ) {
				$this->_error("Invalid API Key given", 401);
			}
	
			// check that their sig is correct
			if ( $sig != md5($account->cred->sig . SELF . serialize($_POST)) ) {
				$this->_error("Invalid request signature", 401);
			}
	
		// call our func
		call_user_func(array($this, $func), $account, $image);
	
	}
	
	// post_image
	public function post_image($account, $image) {
		
		// oimage
		$oimage = $image;
		
		// data
		$data = base64_decode( p('data', false, $_POST) );
		
		// content types
		$ctypes = array( 'jpg' => 'image/jpeg', 'gif' => 'image/gif', 'png' => 'image/png' );
	
		// file type
		$ft = strtolower(array_pop(explode('.', $image)));		
		
		// make sure they have a data param
		if ( !array_key_exists($ft, $ctypes) ) {
			$this->_error("Image file name does not contain an extension", 400);
		}

		if ( $data === false ) {
			$this->_error("No image data provided", 400);
		}
	
		// make sure what was given is really and image
		if ( ($img = imagecreatefromstring( $data ) ) === false ) {
			$this->_error("Data provided is not an accepted image format.", 400);
		}
		
		// figure out if their account uses the default dist
		if ( $account->dist_default ) {
			
			// append their domain to the folder path
			$image = $account->domain . "/" . trim($image, '/');
			
			// what account
			$d = ( HTTP_HOST === 'api.cdnimag.es' ? 'default.cdnimag.es' : 'idev.cdnimag.es' );
			
			// override with our own account
			$account = new \dao\account('get',array('domain', $d));
		
		}
			
		// create our s3 connection
		$s3 = new S3( $account->aws->key, $account->aws->secret);
		
		// bucket
		$bucket = false;
		
			// loop and find
			foreach ( $account->buckets as $item ) {
				if ( ( p('bucket', false, $_POST) === false AND $item->default === true ) OR ( p('bucket', false, $_POST) == $item->name ) ) {
					$bucket = $item; break;
				}
			}
			
			// if no bucket
			if ( $bucket === false AND $account->buckets ) {
				$bucket = $account->buckets->item('first');
			}
	
			// no bucket
			if ( $bucket === false ) {
				$this->_error("No bucket selected", 400);
			}		
	
		// headers
		$hdr = array("Content-Type" => $ctypes[$ft]);
	
		// lets give it a try
		try {
			$resp = $s3->putObject($data, $bucket->name, $image, S3::ACL_PUBLIC_READ, $hdr, $hdr);
		}
		catch ( Exception $e ) { $this->_error($e->getMessage(), $e->getCode()); }
	
		// tell them we're all good
		$this->_response(array(
			'stat' => 1,
			'code' => 200,
			'uri' => $oimage
		));
	
	}


	private function _error($message, $code=500) {
		
		// header
		header("Content-type:".self::$format, false, $code);
		
		// return
		$this->_response(array(
			'stat' => 0,
			'message' => $message,
			'code' => $code
		));
	
	}

	private function _response($resp) {
		exit( self::$format == 'application/php' ? serialize($resp) : json_encode($resp) );
	}

}


?>