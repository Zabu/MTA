<?php

class ObjetSoin extends Zend_Db_Table {
	protected $_name = 'objet_soin';
	protected $_primary = 'id_soin';

	function findById($id) {
		$where = $this->getAdapter()->quoteInto('id_soin =?', (string)$id);
		return $this->fetchRow($where);
	}
	
	function getModHp($id) {
		$where = $this->getAdapter()->quoteInto('id_soin =?', (string)$id);
		return $this->fetchRow($where)->hp_soin;
	}

	function getModMp($id) {
		$where = $this->getAdapter()->quoteInto('id_soin =?', (string)$id);
		return $this->fetchRow($where)->mp_soin;
	}
	
}