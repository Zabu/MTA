<?php

class Zone extends Zend_Db_Table {
	protected $_name = 'zone';
	protected $_primary = 'id_zone';
	
	function findById($id) {
		$where = $this->getAdapter()->quoteInto('id_zone =?',(string)$id);
		return $this->fetchRow($where);
	}

}