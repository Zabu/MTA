<?php

class Case_ extends Zend_Db_Table {
	protected $_name = 'case';
	protected $_primary = 'id_case';
	
	function getLigne($id_case) {
		$where = $this->getAdapter()->quoteInto('id_case =?', (string)$id_case);
		return $this->fetchRow($where)->ligne_case;
	}
	
	function getColonne($id_case) {
		$where = $this->getAdapter()->quoteInto('id_case =?', (string)$id_case);
		return $this->fetchRow($where)->colonne_case;
	}
	
	function findByCoord($ligne, $colonne) {
		$where[] = $this->getAdapter()->quoteInto('ligne_case =?', $ligne);
		$where[] = $this->getAdapter()->quoteInto('colonne_case =?', $colonne);
		return $this->fetchRow($where);
	}
	
	function findById($id) {
		$where = $this->getAdapter()->quoteInto('id_case =?', (string)$id);
		return $this->fetchRow($where);
	}
	
}