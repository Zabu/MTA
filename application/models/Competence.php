<?php

class Competence extends Zend_Db_Table {
	protected $_name = 'competence';
	protected $_primary = 'id_competence';
	
	function findById($id){
		$where = $this->getAdapter()->quoteInto('id_competence=?', (string)$id);
		return $this->fetchRow($where);
	}
	
	function getByClasse($id){
		$where = $this->getAdapter()->quoteInto('id_classe=?',(string)$id);
		return $this->fetchAll($where);
	}
	
	function getCompByMonstre($id_monstre){
		$where = $this->getAdapter()->quoteInto('id_competence IN (SELECT id_competence FROM competencemonstre WHERE id_monstre=? )', $id_monstre);
		return $this->fetchall($where);
	}
	
}