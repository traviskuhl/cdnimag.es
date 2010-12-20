<h2>How it works</h2>
<div class="yui3-g">
	<div class="yui3-u-1-5">
	
		<ul class="toc">
			<li><a href="#anc_1">Overview</a></li>
			<li>
				<a href="#anc_2">Request URI</a>
				<ul>
					<li><a href="#anc_3">Image Path</a></li>
					<li><a href="#anc_4">Bucket</a></li>
					<li><a href="#anc_5">Signature</a></li>
					<li><a href="#anc_6">Commands</a></li>					
				</ul>
			</li>			
			
			<li>
				<a href="#anc_7">Commands</a>
				<ul>
					<li><a href="#anc_8">Size</a></li>
					<li><a href="#anc_9">Percent</a></li>
					<li><a href="#anc_15">Frame</a></li>										
					<li><a href="#anc_10">Crop</a></li>					
					<li><a href="#anc_11">Scale</a></li>					
					<li><a href="#anc_12">Valign</a></li>					
					<li><a href="#anc_13">Halign</a></li>
					<li><a href="#anc_14">Output</a></li>
				</ul>
			</li>
			
			<li>
				<a href="#anc_16">API</a>
				<ul>
					<li><a href="#anc_17">Overview</a></li>				
					<li><a href="#anc_18">Authentication</a></li>
					<li><a href="#anc_19">Signatures</a></li>					
					<li><a href="#anc_20">Image (POST)</a></li>
				</ul>
			</li>			

			<li>
				<a href="#anc_7">Clients</a>
				<ul>
					<li><a target="_blank" href="https://github.com/traviskuhl/cdnimages-clients">Overview</a></li>				
					<li><a target="_blank" href="https://github.com/traviskuhl/cdnimages-clients/tree/master/php">PHP</a></li>
				</ul>
			</li>	
			
		</ul>
		
	</div>
	<div class="yui3-u-4-5">
		<ul class="docs">
			<li>
				<h3 id="anc_1">Overview</h3>
				<p>It's pretty simple. We setup a Cloudfront Distribution for you that points to our servers as the origin. You upload images to your S3 bucket. 
				When you request your images from Cloudfront, we pull them from S3, resize them on the fly and return them to Cloudfront. You get all the benefits of dynamically sized images and a CDN! We told you it was awesome!</p>
			</li>
			<li>
				<h3 id="anc_2">Request URI</h3>
				<p>Request URIs are composed of two main parts; 'Image Path' &amp; 'Commands'. These parts are separated by a dollar sign "$".</p>
				<code><a target="_blank" href="http://demo.cf.cdnimag.es/scale:max/size:150x120/$/teamcoco.gif">http://demo.cf.cdnimag.es/size:150x120/$/teamcoco.gif</a></code>
				<ul>
					<li>
						<h4 id="anc_3">Image Path</h4>
						<p>The relative path to the image you'd like to manipulate. The path should be relative to the bucket you're using. Remember not to include the bucket in the path.</p>
						<code>demo/teamcoco.gif</a></code>
					</li>
					<li>
						<h4 id="anc_4">Bucket</h4>
						<p>Name or Alias of the S3 bucket the 'Image Path' is relative to</p>
						<code>Example: bucket:{bucketNameOrAlias}</code>
					</li>
					<li>
						<h4 id="anc_5">Signature</h4>
						<p>MD5 hash of a concatenated string containing the Request URI (excluding the sig: command) and your cdnimag.es API Secret</p>
						<code>Example: sig:{md5Hash}</code>
						<code>Generating Signature: md5($secret.$uri);</code>
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
						<h4 id="anc_15">Frame</h4>
						<p>Scale then crop the image to the given size. [Default: false]</p>
						<code>Example: frame:true</code>
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
						<p>The vertical pixel or anchor position of the resulting image. Values: pixels, top, bottom, center. [Default: center]</p>
						<code>Example: valign:top</code>
					</li>		
					<li>
						<h4 id="anc_13">Halign</h4>
						<p>The horizantal pixel or anchor position of the resulting image. Values: top, bottom, left, right, center. [Default: center]</p>
						<code>Example: valign:top</code>						
					</li>							
					<li>
						<h4 id="anc_14">Output</h4>
						<p>The format of the resulting image. Values: jpeg, gif, png. [Default: original image format]</p>
						<code>Example: output:png</code>						
					</li>		
				</ul>				
			</li>
			<li>
				<h3 id="anc_16">API</h3>				
				<ul>
					<li>
						<h4 id="anc_17">Overview</h4>
						<p>api.cdnimag.es allows you to upload, retrieve and delete images from account</p>
						<code>URL: http://api.cdnimag.es/v1/$domain/$method/$imagePath</code>
					</li>
					<li>
						<h4 id="anc_18">Authentication</h4>
						<p>All requests to the API must include your API Key and a request signature as HTTP Headers.</p>
						<code>
							X-CdnImages-Key &amp;  X-CdnImages-Sig
						</code>
					</li>
					<li>
						<h4 id="anc_19">Signatures</h4>
						<p>To generate request signature, combine your API Key, request URL and paramater string</p>
						<code>
							md5($secret.$url.$parameters);
						</code>
					</li>
					<li>
						<h4 id="anc_20">Image (Post)</h4>
						<p>Post (upload) an image to your account</p>
						<code><b>URL:</b> http://api.cdnimag.es/$domain/image/$path</code>
						<code><b>Example:</b> http://api.cdnimag.es/demo.cdnimag.es/image/test/image/img.jpg</code>
						<code><b>HTTP Method:</b> POST</code>
						<code><b>Parameters:</b> <br> &nbsp; data = base64 encode string with image data</code>
					</li>					
				</ul>
			</li>
		</ul>
	</div>
</div>