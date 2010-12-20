<?php

namespace modules;

class examples extends \FrontEnd {

	public function render($args) {
		
		return \Controller::renderModule(
			'examples/examples',
			$args
		);
	
	}

}

?>