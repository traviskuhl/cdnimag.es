<h2>How it works</h2>
<div class="yui3-g">
	<div class="yui3-u-1-4">
	
		<ul class="toc">
			<li><a href="#anc_1">Overview</a></li>
			<li>
				<a href="#anc_2">Request URI</a>
				<ul>
					<li><a href="#anc_3">Image Path</a></li>
					<li><a href="#anc_4">Bucket</a></li>
					<li><a href="#anc_5">Signature</a></li>
					<li><a href="#anc_6">Image Commands</a></li>					
				</ul>
			</li>			
			
			<li>
				<a href="#anc_7">Image Commands</a>
				<ul>
					<li><a href="#anc_8">Size</a></li>
					<li><a href="#anc_9">Percent</a></li>					
					<li><a href="#anc_10">Crop</a></li>					
					<li><a href="#anc_11">Scale</a></li>					
					<li><a href="#anc_12">Valign</a></li>					
					<li><a href="#anc_13">Halign</a></li>
					<li><a href="#anc_14">Output</a></li>
				</ul>
			</li>						
		</ul>
		
	</div>
	<div class="yui3-u-3-4">
		<ul class="docs">
			<li>
				<h3 id="anc_1">Overview</h3>
				<p>It's pretty simple. You setup your Cloudfront Distribution; hook it up to a Amazon S3 bukcet; resize your image on the fly, while enjoying the performace benifits of a CDN.</p>
			</li>
			<li>
				<h3 id="anc_2">Request URI</h3>
				<p>Request URIs are composed of two main parts; 'Image Path' &amp; 'Commands'. These parts are seperated by a dollar sign "$".</p>
				<code>Example: /size:150x120/$/v3/images/logo-v3.png</code>
				<ul>
					<li>
						<h4 id="anc_3">Image Path</h4>
						<p>The relative path to the image you'd like to manipulate. The path should be relative to the bucket you're using. Remember not to include the bucket path</p>
						<code>Example: /v3/images/logo-v3.png</code>
					</li>
					<li>
						<h4 id="anc_4">Bucket</h4>
						<p>Name or Alias of the S3 bucket the 'Image Path' is relative to</p>
						<code>Exampe: bucket:{bucketNameOrAlias}</code>
					</li>
					<li>
						<h4 id="anc_5">Signature</h4>
						<p>MD5 hash of a concatinated string containing the Request URI (exluding the sig: command) and your cdnimag.es API Secret</p>
						<code>Exampe: sig:{md5Hash}</code>
						<code>Generating Signature: md5("{$secret}{$uri}");</code>
					</li>				
					<li>
						<h4 id="anc_6">Image Commands</h4>
						<p>A list of image manipulation commands</p>
						<code>Example: size:10x10/scale:min</code>
					</li>						
				</ul>
			</li>
			<li>
				<h3 id="anc_7">Image Commands</h3>
				<ul>
					<li>
						<h4 id="anc_8">Size</h4>
						<p>Width &amp; Height of the resulting image. Can not be used with "Percent" command.</p>
						<code>Example: size:100x100</code>
					</li>
					<li>
						<h4 id="anc_9">Percent</h4>
						<p>Size of the image as a percentage of the original. Can not be used with "Size" command.</p>
						<code>Example: percent:50</code>
					</li>
					<li>
						<h4 id="anc_10">Crop</h4>
						<p>Crop the image to the given size or percent. [Default: false]</p>
						<code>Example: crop:true</code>
					</li>					
					<li>
						<h4 id="anc_11">Scale</h4>
						<p>Resample the image to the given size or percent. Values: max = Scale to max size / min = Scale min proportional size [Default: Max]</p>
						<code>Example: scale:min</code>
					</li>		
					<li>
						<h4 id="anc_12">Valign</h4>
						<p>The vertical pixel or anchor position of the resulting image. Values: pixes, top, bottom, center. [Default: center]</p>
						<code>Example: valign:top</code>
					</li>		
					<li>
						<h4 id="anc_13">Halign</h4>
						<p>The horizantal pixel or anchor position of the resulting image. Values: pixes, left, right, center. [Default: center]</p>
						<code>Example: valign:10</code>						
					</li>							
					<li>
						<h4 id="anc_14">Output</h4>
						<p>The format of the resulting image. Values: jpeg, gif, png. [Default: original image format]</p>
						<code>Example: ouput:png</code>						
					</li>		
				</ul>				
			</li>
	
	</div>
</div>