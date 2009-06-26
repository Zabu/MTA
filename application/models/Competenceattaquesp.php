<?php

class CompetenceAttaqueSp extends Zend_Db_Table {
	protected $_name = 'competence_attaquesp';
	protected $_primary = 'id_attaquesp';
	
	function getCompAttCoef($id){
		$where = $this->getAdapter()->quoteInto('id_attaquesp =?', (string)$id);
		return $this->fetchRow($where)->coefficient_attaquesp;
	}
	
}