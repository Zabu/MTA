<?php

class EquipementArmure extends Zend_Db_Table {
	protected $_name = 'equipement_armure';
	protected $_primary = 'id_armure';

	function findById($id) {
		$where = $this->getAdapter()->quoteInto('id_armure =?', (string)$id);
		return $this->fetchRow($where);
	}
	
}