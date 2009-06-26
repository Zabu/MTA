<?php

class ZoneMonstre extends Zend_Db_Table {
	protected $_name = 'zonemonstre';
	protected $_primary = array('id_zone','id_monstre');

	function findByIdZone($id) {
		$where = $this->getAdapter()->quoteInto('id_zone =?',(string)$id);
		return $this->fetchAll($where);
	}
	
	function findByIdMonstre($id) {
		$where = $this->getAdapter()->quoteInto('id_monstre =?',(string)$id);
		return $this->fetchAll($where);
	}
	
}