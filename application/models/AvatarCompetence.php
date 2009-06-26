<?php

class AvatarCompetence extends Zend_Db_Table {
	protected $_name = 'avatarcompetence';
	protected $_primary = array('id_avatar','id_competence');
	
	function findByIdAvatar($id) {
		$where = $this->getAdapter()->quoteInto('id_avatar=?', (string)$id);
		return $this->fetchAll($where);
	}

	function findCompetence($id_avatar, $id_competence) {
		$where[] = $this->getAdapter()->quoteInto('id_avatar=?',$id_avatar);
		$where[] = $this->getAdapter()->quoteInto('id_competence=?',$id_competence);
		return $this->fetchRow($where);
	}
	
}