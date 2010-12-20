<?php

	// invite
	if ( pp(0) AND p('invite', false, $r) === false ) {
		$r['invite'] = pp(0);
	}

?>
<h2>image manipulation on the fly + CDN = AWESOME! <a href="#awesome">see how awesome</a></h2>

<form class="invite"  action="http://kuhl.us2.list-manage.com/subscribe/post" method="post" target="_blank">
<input type="hidden" name="u" value="abd8fa686eaefe37d55a1c3af">
<input type="hidden" name="id" value="77f9ad5c84">

	<fieldset>
		<legend>Get an Invite</legend>
		<p>We're not ready for everybody just yet. Get on the list and we'll let you know</p>
		<input type="email" name="MERGE0" value="Email Address" onclick="if(this.value='Email Address'){this.value='';this.className='on';}">
		<button type="submit">Let Me Know</button>
	</fieldset>
</form>

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
						<em>Invite Code</em>
						<input type="text" name="f[invite]" value="<?php echo p('invite', false, $r); ?>" style="width: 150px;">
					</label>
				</li>					
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

{% examples %}

{% docs %}