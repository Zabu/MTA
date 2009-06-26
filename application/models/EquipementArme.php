<?php

class EquipementArme extends Zend_Db_Table {
	protected $_name = 'equipement_arme';
	protected $_primary = 'id_arme';

	function findById($id) {
		$where = $this->getAdapter()->quoteInto('id_arme =?', (string)$id);
		return $this->fetchRow($where);
	}
	
}