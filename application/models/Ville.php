<?php

class Ville extends Zend_Db_Table {
	protected $_name = 'ville';
	protected $_primary = 'id_ville';
	
	function findByNom($nom) {
		$where = $this->getAdapter()->quoteInto('nom_ville =?',(string)$nom);
		return $this->fetchRow($where);
	}
	
	function findById($id) {
		$where = $this->getAdapter()->quoteInto('id_ville =?',(string)$id);
		return $this->fetchRow($where);
	}
	
	function getNameById($id) {
		$where = $this->getAdapter()->quoteInto('id_ville =?',(string)$id);
		return $this->fetchRow($where)->nom_ville;
	}
	
	function findByCase($id_case) {
		$where = $this->getAdapter()->quoteInto('id_case =?',(string)$id_case);
		return $this->fetchRow($where);
	}
}