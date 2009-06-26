<?php

class Avatar extends Zend_Db_Table {
	protected $_name = 'avatar';
	protected $_primary = 'id_avatar';
	
	function findByNom($nom) {
		$where = $this->getAdapter()->quoteInto('nom_avatar =?',(string)$nom);
		return $this->fetchRow($where);
	}
	
	function findByUser($utilisateur) {
		$where = $this->getAdapter()->quoteInto('id_utilisateur =?', (string)$utilisateur);
		return $this->fetchAll($where);
	}
	
	function findById($id_avatar) {
		$where = $this->getAdapter()->quoteInto('id_avatar =?', (string)$id_avatar);
		return $this->fetchRow($where);
	}
}