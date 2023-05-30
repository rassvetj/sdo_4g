<?
class HM_Db_Table_Row_Users extends Zend_Db_Table_Row_Abstract
{
	public function __get($attribute)
	{
		switch ($attribute) {
			case 'name':
				$arr = array();
				if ($LastName = parent::__get('LastName')) $arr[] = $LastName;
				if ($FirstName = parent::__get('FirstName')) $arr[] = $FirstName;
				if ($Patronymic = parent::__get('Patronymic')) $arr[] = $Patronymic;
				return implode(' ', $arr);
				break;
			default:
				return parent::__get($attribute);
				break;
		}
	}

}
?>