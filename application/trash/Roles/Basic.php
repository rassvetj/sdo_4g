<?php
class Roles_Basic implements ArrayAccess, Iterator {

    const PERMISSION_LEVEL_GUEST    = 0;
	const PERMISSION_LEVEL_STUDENT	= 1;
	const PERMISSION_LEVEL_TEACHER	= 2;
	const PERMISSION_LEVEL_DEAN		= 3;
	const PERMISSION_LEVEL_ADMIN	= 4;

	protected $_roles = array();
	private $_valid = true;
	
	function __construct()
	{
	    $this->_roles = array(
	    	1 => array(
	    		'id' 	=> 1,
	    		'level' => 1,
	    		'title' => _('Слушатели'),
	    		'class' => 'Students',
	    	),
	    	2 => array(
	    		'id' 	=> 2,
	    		'level' => 2,
	    		'title' => _('Преподаватели'),
	    		'class' => 'Teachers',
	    	),
	    	3 => array(
	    		'id' 	=> 3,
	    		'level' => 3,
	    		'title' => _('Учебная администрация'),
	    		'class' => 'Deans',
	    	),
	    	4 => array(
	    		'id' 	=> 4,
	    		'level' => 4,
	    		'title' => _('Администраторы'),
	    		'class' => 'Admins',
	    	),
	    	5 => array(
	    		'id' 	=> 5,
	    		'level' => 0,
	    		'title' => _('Претенденты'),
	    		'class' => 'Claimants',
	    	),
	    	6 => array(
	    		'id' 	=> 6,
	    		'level' => 0,
	    		'title' => _('Прошедшие обучение'),
	    		'class' => 'Graduated',
	    	)
	    );
	}
	
	public function offsetExists($key) {
	    return isset($this->_roles[$key]);
	}
	
	public function offsetGet($key) {
	    return $this->_roles[$key];
	}
	
	public function offsetSet($key, $value) {
	    $this->_roles[$key] = $value;
	}
	
	public function offsetUnset($key) {
	    unset($this->_roles[$key]);
	}
	
	public function rewind() {
	    reset($this->_roles);
	}
	
	public function current() {
	    return current($this->_roles);
	}
	
	public function key() {
	    return key($this->_roles);
	}
	
	public function next() {	    
	    $item = each($this->_roles);
	    $this->_valid = true;
	    if (!$item) $this->_valid = false;	    
	}
	
	public function valid() {
	    return $this->_valid;
	}
	
	public function toArray() {
	    $array = array();
	    foreach($this->_roles as $key => $value) {
	        $array[$key] = $value['title'];
	    }
	    return $array;
	}

	public function getAll()
	{
		return $this->_roles;
	}
}
?>