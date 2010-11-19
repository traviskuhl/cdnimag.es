<?php

namespace modules;

class docs extends \FrontEnd {

	public function render($args) {
	
	
		return \Controller::renderModule(
			'docs/docs',
			$args
		);
	
	}

}

?>