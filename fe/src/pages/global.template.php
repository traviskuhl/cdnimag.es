<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<title>cdnimag.es</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/combo?3.2.0/build/cssfonts/fonts-min.css&3.2.0/build/cssreset/reset-min.css&3.2.0/build/cssgrids/grids-min.css">
		<link rel="stylesheet" type="text/css" href="/assets/cdn/css/global.css">
		<script type="text/javascript" src="http://yui.yahooapis.com/3.2.0/build/simpleyui/simpleyui.js"></script>
	</head>
	<body>
		<div id="doc">
			<div id="hd">
				<h1><a href="/">cdn<em>imag.es</em></a></h1>
			</div>
			<div id="bd">{$_body}</div>
			<div id="ft">
				&copy; 2010 <a href='http://the.kuhl.co'>kuhl.co</a> - 
				all rights reserved - 
				support: <a href='irc://irc.oftc.net/#cdnimages'>irc</a> or <a href="mailto:travis@kuhl.co">email</a> -				
				made by @<a href="http://twitter.com/traviskuhl">traviskuhl</a> &amp; 
				@<a href="http://twitter.com/rochers">rochers</a> 
			</div>
		</div>
		
		<?php if ( bDevMode === false ) { ?>
			<script type="text/javascript">
			var clicky = { log: function(){ return; }, goal: function(){ return; }};
			var clicky_site_id = 66366361;
			(function() {
			 var s = document.createElement('script');
			 s.type = 'text/javascript';
			 s.async = true;
			 s.src = ( document.location.protocol == 'https:' ? 'https://static.getclicky.com/js' : 'http://static.getclicky.com/js' );
			 ( document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0] ).appendChild( s );
			})();
			</script>
		<?php } ?>		
	</body>
</html>


