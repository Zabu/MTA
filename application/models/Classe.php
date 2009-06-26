<?php

class Classe extends Zend_Db_Table {
	protected $_name = 'classe';
	protected $_primary = 'id_classe';
	
	function findByNom($nom) {
		$where = $this->getAdapter()->quoteInto('nom_classe =?', (string)$nom);
		return $this->fetchRow($where);
	}
	
	function findById($id) {
		$where = $this->getAdapter()->quoteInto('id_classe =?', (string)$id);
		return $this->fetchRow($where);
	}
	
	function getNameById($id) {
		$where = $this->getAdapter()->quoteInto('id_classe =?', (string)$id);
		return $this->fetchRow($where)->nom_classe;
	}
	
	function getDescriptionById($id) {
		$where = $this->getAdapter()->quoteInto('id_classe =?', (string)$id);
		return $this->fetchRow($where)->description_classe;
	}
	
	function getUpAtt($id) {
		$where = $this->getAdapter()->quoteInto('id_classe =?', (string)$id);
		return $this->fetchRow($where)->att_classe;
	}
	
	function getUpAttS($id) {
		$where = $this->getAdapter()->quoteInto('id_classe =?', (string)$id);
		return $this->fetchRow($where)->attsp_classe;
	}
	
	function getUpDef($id) {
		$where = $this->getAdapter()->quoteInto('id_classe =?', (string)$id);
		return $this->fetchRow($where)->def_classe;
	}
	
	function getUpDefS($id) {
		$where = $this->getAdapter()->quoteInto('id_classe =?', (string)$id);
		return $this->fetchRow($where)->defsp_classe;
	}
	
	function getUpVit($id) {
		$where = $this->getAdapter()->quoteInto('id_classe =?', (string)$id);
		return $this->fetchRow($where)->vit_classe;
	}
	
	function getUpHp($id) {
		$where = $this->getAdapter()->quoteInto('id_classe =?', (string)$id);
		return $this->fetchRow($where)->hp_classe;
	}
	
	function getUpMp($id) {
		$where = $this->getAdapter()->quoteInto('id_classe =?', (string)$id);
		return $this->fetchRow($where)->mp_classe;
	}
	
}