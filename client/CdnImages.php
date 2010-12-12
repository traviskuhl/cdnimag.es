<?php

class CdnImages {
	
	// key and secret 
	public static $key, $secret, $domain, $cname = false;

	// api
	public static $api = "http://api.cdnimag.es";
	
	// version
	public static $apiVersion = "v1";
	
	// client version
	const VERSION = "1.0";


	///////////////////////////////////////////////////
	/// @brief set internal values
	///
	/// @param string domain name
	/// @param string api key
	/// @param string api secret
	/// @return void
	///////////////////////////////////////////////////
	public static function setup($args) {
	
		// set our args
		foreach ( $args as $k => $v ) {	
			if ( property_exists("CdnImages", $k) ) {
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
	
		// no secret we return path
		if ( self::$secret === false ) { return $path; }
		
		// if cname is i we need to unshift to the first cmd
		if ( self::$cname == 'i.cdnimag.es' ) { $cmds = array_merge(array(self::$dmain), $cmds); }
		
		// cmdStr
		$cmdStr = implode("/",array_map(function($k=false, $v=false){ return ($k?"{$k}:{$v}":$v); }, array_keys($cmds), $cmds));
		
		// generate our url string without the sig
		$unsigned =  trim($cmdStr,'/').'/$/'.trim($path,'/');
		
		// parts
		$parts = array( self::getHost() );
			
			// add cmd
			($cmdStr=="" ? false : $parts[] = $cmdStr );
			
			$parts[] = "sig:".md5(self::$secret.$unsigned);
			$parts[] = '$';
			$parts[] = trim($path,'/');
		
		// return it
		return implode("/", $parts);
	
	}


	///////////////////////////////////////////////////
	/// @brief sign an string that may contain img tags
	///
	/// @param string path to image
	/// @return string signed url
	///////////////////////////////////////////////////
	public static function signString($str) {
	
		// get all image tasg
		if ( preg_match_all("#<img([^>]+)>#i", $str, $matches, PREG_SET_ORDER) ) {
			
			// loop through each match
			foreach ( $matches as $match ) {
				
				// tag
				$tag = $match[0];
				
				// ignore
				if ( stripos($tag, "cdn-ignore") !== false ) { continue; }
				
				// attr		
				$attr = $cmd = array();				
				
				// holders
				$src = $w = $h = $style = $close = false;
					
					// width
					if ( preg_match("#width[\=|\:]['|\"]?\s?([0-9]+)(px)?['|\"]?#i", $tag, $m) ) {
						$w = $m[1];
					}
	
					// height
					if ( preg_match("#height[\=|\:]['|\"]?\s?([0-9]+)(px)?['|\"]#i", $tag, $m) ) {
						$h = $m[1];
					}					
	
				// add size
				if ( $w > 0 AND $h > 0 ) { $cmd['size'] = "{$w}x{$h}"; }	
			
				// background-image or background
				if ( preg_match("#background(\-image)?:\s?url\(([^\)]+)\)#", $tag, $m) ) {				
				
					// tag
					$tag = str_replace($m[2], self::sign($m[2], $cmd), $tag);
					
				}			
				// src
				else if ( preg_match("#src=([^\s]+)#", $tag, $m) ) {
					$tag = str_replace($m[0], 'src="'.self::sign(trim($m[1],"\"'"), $cmd).'"', $tag);
				}								
				
				// do it 
				$str = str_replace($match[0], $tag, $str);
				
			}
		
		}
	
		// give back str
		return $str;
	
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
			throw new Exception("No image given", 404); return false;
		}
		
		// make our uri
		$uri = self::$domain."/image/".trim($dest, '/');
		
		// params
		$params = array(
			'data' => base64_encode($data)
		);
			
		// make our request
		try {
			$resp = self::request($uri, $params, "POST", (isset($args['headers'])? $args['headers'] : array() ));
		}
		catch (Exception $e) { throw new Exception($e->getMessage(), $e->getCode()); }
		
		// return the url
		return self::sign($resp['uri']);
	
	}


	///////////////////////////////////////////////////
	/// @brief get an image from the cdn
	///
	/// @return string with hostname
	///////////////////////////////////////////////////
	public static function get($src) {
	
		// coming soon
	
	}

	///////////////////////////////////////////////////
	/// @brief get an image from the cdn
	///
	/// @return string with hostname
	///////////////////////////////////////////////////
	static function request($uri, $params=array(), $method="GET", $headers=array()) {
	
		// key and secret
		if ( self::$key === false OR self::$secret === false ) {
			throw new Exception("API Key or Secret not set");
		}	
	
		// construct our url
		$url = "http://".trim(self::$api, '/')."/".self::$apiVersion."/".trim($uri,'/');
		
			// if method is get, add our params
			if ( $method == 'GET' ) {
				
				// ? to url 
				$url .= '?';
				
				foreach ( $params as $k => $v ) {
					$url .= "$k=$v&";
				}
				
			}
		
		// headers
		if ( !is_array($headers) ) { $headers = array(); }
		
		// add our key
		$headers["X-CdnImages-Key"] = self::$key;
		
		// sig
		$headers["X-CdnImages-Sig"] = md5(self::$secret . $url . serialize($params));
		
		// version
		$headers["User-Agent"] = "CdnImagesClient-".self::VERSION;
		
		// format
		$format = ( function_exists("json_decode") ? "text/javascript" : "application/php" );
		
		// accept header
		$headers["Accept"] = $format;
			
		
		// create a new cURL resource
		$ch = curl_init();
		
		// set URL and other appropriate options
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array_map(function($k, $v){ return "{$k}: {$v}"; }, array_keys($headers), $headers ) );

		// post
		if ( $method == 'POST' ) {
			curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		}
		
		// grab URL and pass it to the browser
		$response = curl_exec($ch);
		
			// check response 
			if ( curl_getinfo($ch,CURLINFO_HTTP_CODE) != 200) {
				throw new Exception("Error connecting to API", curl_getinfo($ch,CURLINFO_HTTP_CODE));
			}
			
		// close cURL resource, and free up system resources
		curl_close($ch);	
	
		// return response
		$data = ($format == 'text/javascript' ? json_decode($response, true) : unserialize($response) );	
	
		// check the stat
		if ( $data['stat'] == 0 ) {
			throw new Exception($data['message'], $data['code']);
		}
	
		// return
		return $data;
	
	}

}


?>