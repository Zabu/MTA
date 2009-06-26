<?php

class Objet extends Zend_Db_Table {
	protected $_name = 'objet';
	protected $_primary = 'id_objet';
	
	function findById($id){
		$where = $this->getAdapter()->quoteInto('id_objet=?',(string)$id);
		return $this->fetchRow($where);
	}
	
	function findByName($nom) {
		$where = $this->getAdapter()->quoteInto('nom_objet=?',(string)$id);
		return $this->fetchRow($where);
	}
	
	function getName($id){
		$where = $this->getAdapter()->quoteInto('id_objet=?',(string)$id);
		return $this->fetchRow($where)->nom_objet;
	}
	
	function getDescription($id){
		$where = $this->getAdapter()->quoteInto('id_objet=?',(string)$id);
		return $this->fetchRow($where)->description_objet;
	}
	
	function getPrix($id){
		$where = $this->getAdapter()->quoteInto('id_objet=?',(string)$id);
		return $this->fetchRow($where)->prix_objet;
	}
	
	function getByMarchand($id_marchand){
		$where = $this->getAdapter()->quoteInto('id_objet IN (SELECT id_objet FROM pnj_marchand WHERE id_marchand =?)',(string)$id_marchand);
		return $this->fetchAll($where);
	}
	
}