<?php

namespace Dao;


class account extends Mongo {

	protected function getStruct() {
		return array(
			'id' => array(),
			'domain' => array(),
			'buckets' => array(),
			'aws' => array(),
			'dist' => array()
		);
	}

	public function get($by, $val) {		

		// id
		if ( $by == 'id' ) { $val = (string)$val; }
	
		// lets get it 
		$q = array( $by => $val );	
	
		// do ti 
		$row = $this->row('accounts', $q);
	
			// no row
			if ( !$row ) { return false; }
		
		$this->set($row);		
	
	}

	public function set($row) {
	
		// paretn
		parent::set($row);	
			
	}
	
	public function save() {
	
		// data
		$data = $this->normalize();
	
		// id
		$id = $data['_id'];
		
		// unset
		unset($data['_id']);
		
		// save it 
		try {
			$this->update('accounts', array('_id' => (string)$id), array('$set' => $data), array('upsert'=>true, 'safe'=>true));
		}
		catch (MongoCursorException $e) {
			return false;
		}
	
		// save id 
		$this->id = $id;	
	
		// return id
		return $id;
	
	}

}

?>