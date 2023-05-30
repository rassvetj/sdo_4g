<?php
class Sessions extends HM_Db_Table {

	protected $_name = 'sessions';
	protected $_primary = 'sessid';

	protected $_dependentTables = array();

	function __construct(){
	    parent::__construct();
	}

	public function getDefaultOrder()
	{
		return array('start DESC');
	}

	public function getStat()
	{
		$return = array();
		$sql = "
			SELECT
			  mid,
			  MAX(`start`) as last_login,
			  COUNT(`start`) as count_login
			FROM
			  `sessions`
			GROUP BY
			  mid
		";
		$stmt = $this	->getAdapter()
						->query($sql);
		foreach ($stmt->fetchAll() as $row) {
			$return[$row['mid']] = $row; // вопрос: может можно поизящнее?
		}
		return $return;
	}
}
?>