<?php

class LigneInventaire extends Zend_Db_Table {
	protected $_name = 'ligneinventaire';
	protected $_primary = array('id_avatar','id_objet');
	
	function findByIdAvatar($id) {
		$where = $this->getAdapter()->quoteInto('id_avatar=?',(string)$id);
		return $this->fetchAll($where);
	}
	
	function findItem($id_avatar, $id_objet) {
		$where[] = $this->getAdapter()->quoteInto('id_avatar=?',$id_avatar);
		$where[] = $this->getAdapter()->quoteInto('id_objet=?',$id_objet);
		return $this->fetchRow($where);
	}
	
	function getArmes($id_avatar) {
		$where = $this->getAdapter()->quoteInto('id_objet IN (SELECT id_arme FROM equipement_arme) AND id_avatar = ?', $id_avatar);
		return $this->fetchAll($where);
	}
	
	function getArmures($id_avatar) {
		$where = $this->getAdapter()->quoteInto('id_objet IN (SELECT id_armure FROM equipement_armure) AND id_avatar = ?', $id_avatar);
		return $this->fetchAll($where);
	}
	
	function getSoins($id_avatar) {
		$where = $this->getAdapter()->quoteInto('id_objet IN (SELECT id_soin FROM objet_soin) AND id_avatar = ?', $id_avatar);
		return $this->fetchAll($where);
	}
}