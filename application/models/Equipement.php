<?php

class Equipement extends Zend_Db_Table {
	protected $_name = 'objet_equipement';
	protected $_primary = 'id_equipement';

	function findById($id){
		$where = $this->getAdapter()->quoteInto('id_equipement=?',(string)$id);
		return $this->fetchRow($where);
	}
	
	function getAtt($id){
		$where = $this->getAdapter()->quoteInto('id_equipement=?',(string)$id);
		if($this->fetchRow($where))
			return $this->fetchRow($where)->att_equipement;
		return false;
	}
	
	function getAttS($id){
		$where = $this->getAdapter()->quoteInto('id_equipement=?',(string)$id);
		if($this->fetchRow($where))
			return $this->fetchRow($where)->attsp_equipement;
	}
	
	function getDef($id){
		$where = $this->getAdapter()->quoteInto('id_equipement=?',(string)$id);
		if($this->fetchRow($where))
			return $this->fetchRow($where)->def_equipement;
		return false;
	}
	
	function getDefS($id){
		$where = $this->getAdapter()->quoteInto('id_equipement=?',(string)$id);
		if($this->fetchRow($where))
			return $this->fetchRow($where)->defsp_equipement;
		return false;
	}
	
}