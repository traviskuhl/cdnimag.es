<?php

class CdnImages {
	
	// key and secret 
	public static $key, $secret, $domain, $cname = false;

	// api
	private $api = "http://cdnimag.es";
	
	// version
	private $version = "v1";


	///////////////////////////////////////////////////
	/// @brief set internal values
	///
	/// @param string domain name
	/// @param string api key
	/// @param string api secret
	/// @return void
	///////////////////////////////////////////////////
	public static function setup($args) {
		foreach ( $args as $k => $v ) {
			if ( isset(self::${$k}) ) {
				self::${$k} = $v;
			}
		}
	}


	///////////////////////////////////////////////////
	/// @brief sign an image url
	///
	/// @param string path to image
	/// @param hash image commands
	/// @return string signed url
	///////////////////////////////////////////////////
	public static function sign($path, $cmds=array()) {
		
		// generate our url string without the sig
		$unsigned = implode("/",array_map(function($k,$v){ return "{$k}:{$v}"; },array_keys($cmds),$cmds)) .'/$/'.trim($path,'/');
		
		// return it
		return self::getHost()."/sig:".md5(self::$secret.$unsigned).'/'.$unsigned;
	
	}


	///////////////////////////////////////////////////
	/// @brief get the hostname to use
	///
	/// @return string with hostname
	///////////////////////////////////////////////////
	public static function getHost() {
		if ( isset(self::$cname) AND !empty(self::$cname) ) {
			$host = self::$cname;
		}
		else {
			$host = self::$domain;
		}
		return "http://{$host}";
	}


	///////////////////////////////////////////////////
	/// @brief post an image to the cdn
	///
	/// @param mixed image source. file name or handle
	/// @param string destination path
	/// @return string with hostname
	///////////////////////////////////////////////////
	public static function post($src, $dest, $args=array()) {
		
		// data
		$data = false;
		
		// is it a file name
		if ( is_string($src) AND file_exists($src) ) {
			$data = file_get_contents($src);
		}
	
		// is it a resource
		if ( is_resource($src) AND get_resource_type($src) == 'file' ) {
			while (!feof($src)) {
			  $data .= fread($src, 8192);
			}		
			fclose($src);
		}
	
		// if we don't have a data file
		// we need to throw it away
		if ( $data === false ) {
			return new Exception("No image given", 404);
		}
		
		// make our uri
		$uri = self::$domain."/".$dest;
		
		// params
		$params = array(
			'data' => base64_encode($data)
		);
			
		// make our request
		self::request($uri, $params, "POST");
		
	
	}


	///////////////////////////////////////////////////
	/// @brief get an image from the cdn
	///
	/// @return string with hostname
	///////////////////////////////////////////////////
	public static function get($src) {
	
	
	}

	///////////////////////////////////////////////////
	/// @brief get an image from the cdn
	///
	/// @return string with hostname
	///////////////////////////////////////////////////
	static function request($uri, $params, $method="GET") {
	
	}

}


?>