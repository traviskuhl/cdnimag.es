<h2>image manipulation on the fly + CDN = AWESOME!</h2>

<div class="yui3-g wrap">
	<div class="yui3-u-1-2">
		<form class="signup" method="post" action="/">
			<input type="hidden" name="do" value="reg">	
			<h3>Register</h3>
			<?php
				if ( isset($r_error) ) {
					echo "<p class='error'>$r_error</p>";
				}
			?>			
			<ul>
				<li>
					<label>
						<em>Email</em>
						<input type="text" name="f[email]" value="<?php echo p('email', false, $r); ?>">
					</label>
				</li>
				<li>
					<label>
						<em>Password</em>
						<input type="password" name="f[pass]" value="<?php echo p('pass', false, $r); ?>">
					</label>
				</li>	
				<li class="domain">
					<label>
						<em>Domain</em>
						<input type="text" name="f[domain]" value="<?php echo p('domain', false, $r); ?>"><span>.cdnimag.es</span>
					</label>
				</li>		
				<li>
					<button type="submit">Register</button>
				</li>
			</ul>
		</form>
	</div>
	<div class="yui3-u-1-2">
		<form class="signin" method="post" action="/">
			<input type="hidden" name="do" value="login">	
			<h3>Sign In</h3>
			<?php
				if ( isset($l_error) ) {
					echo "<p class='error'>$l_error</p>";
				}
			?>
			<ul>
				<li>
					<label>
						<em>Email</em>
						<input type="text" name="f[email]" value="<?php echo p('email', false, $r); ?>">
					</label>
				</li>
				<li>
					<label>
						<em>Password</em>
						<input type="password" name="f[pass]" value="<?php echo p('pass', false, $r); ?>">
					</label>
				</li>		
				<li>
					<button type="submit">Sign In</button>
				</li>
			</ul>
		</form>	
	</div>
</div>

{% docs %}