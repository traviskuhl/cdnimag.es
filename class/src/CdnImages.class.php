<?php


class CdnImages extends Bolt {
     
    // start
    public static function start() {
    	
    	// logged
    	if ( Session::getLogged() ) {
    	
    		$u = Session::getUser();
    	
	    		// register
	    		Controller::registerGlobal("_user", $u);
    		
    		// account
    		$a = new \dao\account('get',array('id', $u->profile->aid));
    		
	    		// register
	    		Controller::registerGlobal("_account", $a); 
	    		
	    		// config
	    		b::__('_account', $a);
    	
    	}
    	
    }
    
}

?>