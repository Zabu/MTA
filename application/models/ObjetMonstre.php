<?php

class ObjetMonstre extends Zend_Db_Table {
	protected $_name = 'objetmonstre';
	protected $_primary = array('id_monstre','id_objet');
	
	function getByMonstre($id_monstre) {
		$where = $this->getAdapter()->quoteInto('id_monstre =?', (string)$id_monstre);
		return $this->fetchAll($where);
	}
	
	function findById($id){
		$where = $this->getAdapter()->quoteInto('id_objet=?',(string)$id);
		return $this->fetchRow($where);
	}
}