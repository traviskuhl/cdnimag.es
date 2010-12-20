<?php

namespace modules;

class docs extends \FrontEnd {

	public function render($args) {
	
		// demo
		$args['d'] = "http://demo.cf.cdnimag.es";
		
			// if logged
			if ( \Session::getLogged() ) {
				
				// account
				$a = \b::_('_account');
			
				// default
				if ( $a->dist_default AND bDevMode ) {
					$args['d'] = "http://i.dev.cdnimag.es/".$a->domain;
				}
				else if ( $a->dist_default ) {
					$args['d'] = "http://i.cdnimag.es/".$a->domain;
				}
				else if ( $a->dist_verified ) {
					$args['d'] = "http://".$a->dist->domain; 
				}
				else {
					$args['d'] = "http://".$a->domain;
				}
			
			}
	
		return \Controller::renderModule(
			'docs/docs',
			$args
		);
	
	}

}

?>