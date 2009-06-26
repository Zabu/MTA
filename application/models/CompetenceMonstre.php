<?php

class CompetenceMonstre extends Zend_Db_Table {
	protected $_name = 'competencemonstre';
	protected $_primary = array('id_competence','id_monstre');
	
	function findById($id){
		$where = $this->getAdapter()->quoteInto('id_competence=?', (string)$id);
		return $this->fetchRow($where);
	}
	
	function getByMonstre($id){
		$where = $this->getAdapter()->quoteInto('id_monstre=?',(string)$id);
		return $this->fetchAll($where);
	}
	
	
}