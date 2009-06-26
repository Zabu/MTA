<?php

class Monstre extends Zend_Db_Table {
	protected $_name = 'monstre';
	protected $_primary = 'id_monstre';

	function findById($id) {
		$where = $this->getAdapter()->quoteInto('id_monstre =?', (string)$id);
		return $this->fetchRow($where);
	}
	
}