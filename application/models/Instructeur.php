<?php

class Instructeur extends Zend_Db_Table {
	protected $_name = 'pnj_instructeur';
	protected $_primary = 'id_instructeur';
	
	function findById($id_instructeur) {
		$where = $this->getAdapter()->quoteInto('id_instructeur =?', (string)$id_instructeur);
		return $this->fetchRow($where);
	}
	
	function findByCase($id_case) {
		$where = $this->getAdapter()->quoteInto('id_case =?', (string)$id_case);
		return $this->fetchRow($where);
	}
	
}