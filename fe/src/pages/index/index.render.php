<?php

class index extends FrontEnd {

	public function render($args) {
	
		// do
		$do = p('do');	
	
		// terms
		if ( $do == 'terms' ) { 
			return Controller::renderPage(
				'index/terms',
				$args
			);
		}
		else if ( $do == 'examples' ) {
			return Controller::renderPage(
				'index/examples',
				$args
			);			
		}
	
		// starter
		$args['r'] = $args['l'] = array();
		
		// s
		$s = Session::singleton();		
	
		// loign
		if ( $do == 'login' ) {
		
			// form
			$f = p('f');	
		
			// try it
			if ( $s->login($f['email'], $f['pass']) ) {
			
				// reload
				$this->go( b::url('home') );
			
			}
			else {
				$args['l_error'] = "Invalid Login. Try Again";
			}
		
			// r
			$args['l'] = $f;		
		
		}
		
		// invites
		$codes = array(
			'hackernews' => 100,			
		);
	
		if ( $do == 'reg' ) {
		
			// form
			$f = p('f');
			
			// email
			$e = p('email', false, $f);
			$d = preg_replace("/[^a-zA-Z0-9]+/", "", p('domain', false, $f)).".cdnimag.es";
			$i = strtolower(p('invite', "", $f));
		
			// check for user and domain
			$u = new \dao\user('get',array($e));
			
			// account
			$a = new \dao\account('get',array('domain', $d));
			
			// need an invite code
			if ( !array_key_exists($i, $codes) ) {
				$args['r_error'] = "Invalid Invite Code"; $e = false;
			}
			else {
			
				// cid
				$cid = "invites_{$i}";
				
				// how many
				$used = (int)$this->cache->p_get($cid);
		
				// if used is more
				if ( $used > $codes[$i] ) {
					$args['r_error'] = "Sorry. All of the invites are used up."; $e = false;
				}
			
			}
			
			// yes
			if ( $e AND $d AND p('pass', false, $f) AND $u->id == false AND $a->id == false ) {
				
				// create the user
				$u->set(array(
					'changelog' => false,
					'tags' => false,
					'username' => false,
					'firstname' => false,
					'lastname' => false,
					'email' => $e,
					'password' => \dao\user::encrypt($f['pass']),
					'profile' => array(
						'aid' => uniqid()
					)
				));
				
				// save
				$id = $u->save();
			
				// acount
				$a->id = $u->profile_aid;
				$a->domain = $d;
				
				// key and secret
				$a->cred = array(
					'key' => sha1($this->randomString(20)),
					'sig' => $this->randomString(30),
				);
			
				// save
				$a->save();
				
				// login
				$s->login($e, $u->password, true);
			
				// cid
				$cid = "invites_{$i}";
				
				// inc
				$this->cache->increment($cid);			
			
				// reload
				$this->go( b::url('home') );				
			
			}
			else if ( !isset($args['r_error']) ) {
				$args['r_error'] = "The Email or Domain you're using are already in our system. Try Again";			
			}
		
			// r
			$args['r'] = $f;
		
		}	
		
		
		// logged in
		if ( $s->logged ) {
			$this->go( b::url('home') );
		}		
	
		return Controller::renderPage(
			'index/index',
			$args
		);
	}

}

?>