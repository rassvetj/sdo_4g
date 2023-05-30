<?php

class HM_Role_StudentModel extends HM_Role_RoleModelAbstract
{
	const ERR_ALREADY_ASSIGN = 1;
	const ERR_BEGIN_LEARNING = 2;
	const ERR_LANGUAGE       = 3;
	const ERR_IN_GRADUATED   = 4;

    public function getCourse()
    {
        
        if (isset($this->courses) && count($this->courses))
        {
            return $this->courses[0];
        }
        return false;
    }
    
    public function getUser()
    {
        if (isset($this->users) && count($this->users)) {
            return $this->users[0];
        }

        return false;
    }
	
	public static function getListErrors()
	{
		return array(
			self::ERR_ALREADY_ASSIGN => _('Студент уже назначен'),
			self::ERR_BEGIN_LEARNING => _('Не совпадает дата начала обучения'),
			self::ERR_LANGUAGE       => _('Не совпадает код языка'),
			self::ERR_IN_GRADUATED   => _('В завершенных'),
		);
	}
	
	public static function getErrorText($code)
	{
		$list = self::getListErrors();
		return $list[$code];
	}
    
}