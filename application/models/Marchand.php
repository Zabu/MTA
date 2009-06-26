<?php

class Marchand extends Zend_Db_Table {
	protected $_name = 'pnj_marchand';
	protected $_primary = 'id_marchand';
	
	function findById($id_marchand) {
		$where = $this->getAdapter()->quoteInto('id_marchand =?', (string)$id_marchand);
		return $this->fetchRow($where);
	}
}