<?php

class Utilisateur extends Zend_Db_Table {
    protected $_name = 'utilisateur';
	protected $_primary = 'id_utilisateur';
	
	public function findByMail($mail) {
		$where = $this->getAdapter()->quoteInto('mail_utilisateur =?',(string)$mail);
		return $this->fetchRow($where);
	}
	
	public function findById($id) {
		$where = $this->getAdapter()->quoteInto('id_utilisateur =?',(string)$id);
		return $this->fetchRow($where);
	}
	
	public function findByLogin($login) {
		$where = $this->getAdapter()->quoteInto('login_utilisateur =?',(string)$login);
		return $this->fetchRow($where);
	}
	
	public function isAdmin($id){
		$where = $this->getAdapter()->quoteInto('id_utilisateur IN (SELECT id_admin FROM utilisateur_admin) AND id_utilisateur = ?', $id);
		return $this->fetchRow($where);
	}
	
}