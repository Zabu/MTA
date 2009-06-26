<?php

class Pnj extends Zend_Db_Table {
	protected $_name = 'pnj';
	protected $_primary = 'id_pnj';
	
	function findById($id) {
		$where = $this->getAdapter()->quoteInto('id_pnj =?',(string)$id);
		return $this->fetchRow($where);
	}
	
	function findByCase($id_case){
		$where = $this->getAdapter()->quoteInto('id_case =?',(string)$id_case);
		return $this->fetchAll($where);
	}
	
	function getNameById($id_pnj){
		$where = $this->getAdapter()->quoteInto('id_pnj =?',(string)$id_pnj);
		return $this->fetchRow($where)->nom_pnj;
	}
	
	function findMarchandByCase($id_case){
		$where = $this->getAdapter()->quoteInto('id_pnj IN (SELECT id_marchand FROM pnj_marchand) AND id_case =?',(string)$id_case);
		//return $this->fetchAll($where);
		return $this->fetchRow($where);
	}
	
	function findInstructeurByCase($id_case){
		$where = $this->getAdapter()->quoteInto('id_pnj IN (SELECT id_instructeur FROM pnj_instructeur) AND id_case=?',(string)$id_case);
		//return $this->fetchAll($where);
		return $this->fetchRow($where);
	}
}