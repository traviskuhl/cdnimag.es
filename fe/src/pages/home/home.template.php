<h2>
Hello <?php echo $_account->domain; ?>
<div>
	<a href="/home?do=logout">Logout</a> | 
	API Key: <?php echo $_account->cred->key; ?> -- Secret: <?php echo $_account->cred->sig; ?>
</div>
</h2>


<div class="wrap wrap-pad">

	<?php if ( $_account->dist == false ) { ?> 			
	
		<?php /*
		<h3>How do you want this to work?</h3>
		<p>How would you like to use our service?</p>
		*/ ?>
		
		<h3>Your Amazon Webservice Account</h3>
		<p>We'll set up a custom CloudFront Origin for you.</p>					
	
		<form method="post" action="/home">
			<input type="hidden" name="do" value="dist">	
			<input type="hidden" name="opt" value="2">

			<ul class="" id="_aws">
				<li>
					<label>
						<em>Amazon Key</em>
						<input type="text" name="f[key]" value="<?php echo p('key', false, $f); ?>">
					</label>
				</li>
				<li>
					<label>
						<em>Amazon Secret</em>
						<input type="password" name="f[sec]" value="<?php echo p('sec', false, $f); ?>">
					</label>
				</li>				
			<li><button type="submit">Continue</button>	</li>				
			</ul>			
			
			<?php /*
			<ul class="dist-opts">
				<li>
					<h4><em>Simple</em> (no aws account required)</h4>
					<input type="radio" name="opt" value="1" checked="checked" onclick="Y.one('#_aws').toggleClass('hidden');">					
					<p>No need to use your Amazon AWS account. We'll do all the heavy lifting for you.</p>
				</li>
				<li>
					<h4><em>Advanced</em> (aws account required)</h4>
					<input type="radio" name="opt" value="2" onclick="Y.one('#_aws').toggleClass('hidden');">

					
					<ul class="hidden" id="_aws">
						<li>
							<label>
								<em>Amazon Key</em>
								<input type="text" name="f[key]" value="<?php echo p('key', false, $f); ?>">
							</label>
						</li>
						<li>
							<label>
								<em>Amazon Secret</em>
								<input type="password" name="f[sec]" value="<?php echo p('sec', false, $f); ?>">
							</label>
						</li>	
					</ul>					
					
				</li>
				<li><button type="submit">Continue</button>	</li>
			</ul>
			*/ ?>
		

		</form>
		
	<?php } else if ( $_account->dist_default == true ) { ?>		
		
	<?php } else if ( $_account->dist_verified == false ) { ?>
	
		<h3>Verifing your CloudFront Distribution</h3>
		<p>Your distribution is credited, but we still need to verify that the distribution has been fully propagated. This can take up to 15 minutes.</p>
		
		<form method="post" action="/home">
			<input type="hidden" name="do" value="verify">
			
			<ul>
				<li>
					<em>Current State</em>
					In Progress
				</li>
				<li>
					<em>Last Checked</em>
					<?php echo date("r", ($_account->dist_check ? $_account->dist_check : $_account->dist_created ) ); ?>
				</li>
				<li>
					<button type="submit">Verify Now</button>
				</li>
			</ul>
			
		</form>	
	
	<?php } else { ?>
	
		<h3>Distribution</h3>
		<ul class="buckets">
			<?php
	
				echo "<li><h4><a href='http://{$_account->dist->domain}'>{$_account->dist->domain}</a></h4> <em>CNAMEs: ".($_account->dist->cnames?implode(", ",$_account->dist->cnames):"none")."</em></li>";
			
			?>
		</ul>
		<br>

		<h3>Buckets</h3>
	
		<ul class="buckets">
			<?php
				
				// buk
				$buckets = array();
			
				if ( $_account->buckets !== false ) {
					foreach ( $_account->buckets as $item ) {
						echo "
							<li>
								<h4>{$item->name} ".($item->alias?"({$item->alias})":"")."</h4> <em>added ".b::ago($item->added)."</em>
							</li>
						";
						$buckets[] = $item->name;
					}
				}
				else {
					echo "<li>You have no buckets. You need to create one.</li>";
				}
			?>
		</ul>	

		<form method="post" action="/home">		
		<h3>Add a Bucket</h3>		
		<div class="yui3-g">
			<div class="yui3-u-1-3">

					<input type="hidden" name="do" value="add">
					<?php if (isset($bmsg)) { echo "<div class='error'>{$bmsg}</div>"; } ?> 
					<ul>
						<li>
							<label>
								<em>New Bucket</em>
								<input type="text" name="f[new]" value="" style="width:80px">.<?php echo $_account->domain; ?>
							</label>
						</li>					
						<li style="padding:0 10px;"> -or- </li>
						<li>
							<label>
								<em>Use Exisitng Bucket</em>
								<select name="f[existing]">
									<option></option>
									<?php
										
										// get them							
										$list = $s3->listBuckets();									
										
										// list them
										foreach ( $list as $item ) {											
											if ( !in_array($item, $buckets) ) {
												echo "<option value='{$item}'>{$item}</option>";
											}
										}
									
									?>
								</select>
							</label>
						</li>			
					</ul>
		
			</div>
			<div class="yui3-u-1-3">
				<ul>
					<li>
						<label>
							<em>Alias (optional)</em>
							<input type="text" name="f[alias]" value="">
						</label>
					</li>
					<li>
						<label>
							<em>Expire Header</em>
							<input type="text" name="f[expire]" value="10y">
							<div>formats: #m, #h, #y</div>
							<div>ex: 10y = 10 years</div>
						</label>
					</li>															
			
				</ul>			
			</div>
			<div class="yui3-u-1-3">
				<ul>
					<li>
						<em>Require Signed Requests</em>
						<label><input type="checkbox" name="f[sig]" value="1" checked="checked"> Yes. Require signed requests</label>
					</li>	
					<li>
						<em>Default Bucket</em>
						<label><input type="checkbox" name="f[default]" value="1"> Yes. Use this if none is given</label>
					</li>																
					<li>
						<button type="submit">Add</button>
					</li>				
				</ul>			
			</div>			
		</div>
		</form>				
	
	<?php } ?>

</div>

	
{% docs %}