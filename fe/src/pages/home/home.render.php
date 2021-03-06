<?php

class home extends FrontEnd {

	public function render($args) {
		
		// require
		Session::singleton()->force();	
		
		// do
		$do = p('do');
		
		// logout	
		if ( $do == 'logout' ) {
			
			// logout
			Session::singleton()->logout();
			
			// go index
			$this->go( "/" );
			
		}
		
		// a
		$a = b::_('_account');
		
			// if aws
			if ( $a->aws !== false ) {
				$args['s3'] = new S3($a->aws->key, $a->aws->secret);			
			}		
	
		// do
		$args['msg'] = $args['f'] = false;
		
		// !dist
		if ( $do == 'dist' ) {
			
			// form
			$f = p('f');
			
			// k and s
			$k = trim($f['key']);
			$s = trim($f['sec']);
			$opt = p('opt');
		
			// if yes 
			if ( $opt == '1' ) {
			
				// save our dist 
				$a->dist = array(
					'created' => time(),
					'default' => true,
				);
				
				// save
				$a->save();
			
				// go here
				$this->go( b::url('home') );			
				
			}
			else {
				
				if ( $k AND $s ) {
		
					// s3
					$s3 = new S3($k, $s);
				
					// good
					$good = false;
					
					// msg
					$args['msg'] = "We couldn't create your distribution. Try again!";					
				
					// first check to see if they have dist already
					try {
	
						// dist
						$dist = $s3->getDistribution($a->domain);
						
						// what up 
						if ( $dist->error !== false AND $dist->code == '403' ) {
							$args['msg'] = "The AWS Key &amp; Secret are invalid.";
						}
						else if ( $dist->error !== false AND $dist->code == '404' ) {
							$good = true;
						}
						else {
							$args['msg'] = "Something went wrong";
						}
						
					}
					catch ( Exception $e ) { }
					
					// what up
					if ( $good === true ) {
						
						// create our dist
						$r = $s3->createDistribution(
							"http://".$a->domain,
							true,
							array(),
							"Create by cdnimag.es at ".date("r")
						);
						
						// yes
						if ( $r !== false ) {
						
							// update our account
							$a->dist = array(
								'created' => time(),
								'verified' => false,
								'id' => $r['id']
							);
														
							// save aws
							$a->aws = array(
								'key' => $k,
								'secret' => $s
							);
						
							// save
							$a->save();
							
							// go here
							$this->go( b::url('home') );
						
						}	
					
					}
				
				}
				else {
					$args['msg'] = "You need a Key & Secret";
				}
				
				// form
				$args['form'] = $f;
				
			}
		
		}
		
		// !verify	
		if ( $do == 'verify' ) {
		
			// s3
			$s3 = new S3($a->aws->key, $a->aws->secret);
		
			// good
			$good = false;
		
			// first check to see if they have dist already
			try {

				// dist
				$dist = $s3->getDistribution($a->dist->id);
			
				// what up 
				if ( isset($dist->error) AND $dist->code == '403' ) {
					$args['msg'] = "The AWS Key &amp; Secret are invalid.";
				}
				else if ( isset($dist->error) AND $dist->code == '404' ) {
					
					// update checked
					$a->dist_check = time();
					
					// save
					$a->save();
					
				}
				else if ( isset($dist['status']) AND $dist['status'] == 'InProgress' ) {
				
					// update checked
					$a->dist_check = time();
					
					// save
					$a->save();				
				
				}
				else {

					// all good
					$a->dist_verified = true;
					$a->dist_done = time();
					
					// save
					$a->save();

				}
				
			}
			catch ( Exception $e ) {  }		
		
		}
		
		// !add
		if ( $do == 'add' ) {
		
			// form
			$f = p('f');		
		
			// add an id
			$id = uniqid();
			
			// add it 
			$b = array(
				'added' => time(),
				'sig' => (p('sig', 0, $f)=='1'?true:false),
				'default' => (p('default', 0, $f)=='1'?true:false),
				'alias' => p('alias', 0, $f),
				'expire' => p('expire', '1y', $f)
			);
			
			
			// new or existing
			if ( $f['new'] != "" ) {
			
				$f['new'] .= "." . $a->domain;
			
				// create the bucket
				$args['s3']->putBucket($f['new'], S3::ACL_PUBLIC_READ);

				// name
				$b['name'] = $f['new'];
				
				// alias
				if ( !$f['alias'] ) {
					$b['alias'] = $f['new']; 
				}
			
			}
			else if ( $f['existing'] != "" ) {
				$b['name'] = $f['existing'];
			}
			
			// good
			$good = true;
			
			// already exists
			if ( $a->buckets !== false ) {
				foreach ( $a->buckets as $item ) {
					if ( $item->name == $b['name'] OR $item->alias == $b['name'] OR ( $b['alias'] AND ( $item->alias == $b['alias'] OR $item->name == $b['alias'] ) ) ) {
						$good = false;
					}
				}
			}
			
			// what 
			if ( $good ) { 
					
				// add it 
				$a->push('buckets', $b, $id);
				
				// save
				$a->save();
				
			}
			else {
				$args['bmsg'] = "The bucket name or alias already exists";
			}
			
		
		}
	
		// if dist and dist created and no domain and cnames
		if ( $a->dist_default === false AND $a->dist_verified === true AND $a->dist_domain === false ) {
			
			// dist
			$dist = $args['s3']->getDistribution($a->dist->id);					
			
			// set
			$a->dist_domain = $dist['domain'];
			
			// cnames
			$a->dist_cnames = ($a->dist_cnames ? array_values(p('cnames', false, $dist)) : array());
		
			// save
			$a->save();
		
		}
	
		// return
		return Controller::renderPage(
			'home/home',
			$args
		);
	}

}

?>