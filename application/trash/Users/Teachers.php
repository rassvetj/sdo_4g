<?php
class Users_Teachers extends Users {

	protected $_name = 'People';
	protected $_primary = 'MID';

	protected $_dependentTables = array();


	function fetchAll($select)
	{
	    $select = clone $select;
	    $select->from('People')
	           ->distinct()
	           ->join('Teachers', 'Teachers.MID = People.MID', array())
	           ->join('Courses', 'Courses.CID = Teachers.CID', array())
	           ->group('People.MID')
	           ->setIntegrityCheck(false);

	    return parent::fetchAll($select);
	}
}
?>