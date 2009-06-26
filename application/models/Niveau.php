<?php

class Niveau extends Zend_Db_Table {
	protected $_name = 'niveau';
	protected $_primary = 'id_niveau';
	
	function findById($id) {
		$where = $this->getAdapter()->quoteInto('id_niveau =?', (string)$id);
		return $this->fetchRow($where);
	}
	 
	function getNextLvlExp($id_niveau) {
		if ($id_niveau < 100){
			$id_niveau++;
			$where = $this->getAdapter()->quoteInto('id_niveau =?', (string)$id_niveau);
			return $this->fetchRow($where)->exp_niveau;
		}
		return false;
	}

}