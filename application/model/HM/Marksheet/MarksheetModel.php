<?php
class HM_Marksheet_MarksheetModel extends HM_Model_Abstract
{
     const TYPE_IN 		= 1; # Индивидуальное направление
     const TYPE_GROUP 	= 2; # Ведомость на группу
     const TYPE_SUBJECT = 3; # Ведомость на сессию
	 
	 
	 const M_HAS_NEW_ACTION_STUDENT    = 1;
	 const M_NOT_ALL_STUDENTS_HAS_MARK = 2;
	
	static public function getTypes()
    {
        return array(
            self::TYPE_IN      	=> _('ИН'),
            self::TYPE_GROUP    => _('На группу'),
            self::TYPE_SUBJECT	=> _('На сессию'),
            
        );
    }
	
	
	static public function getMesages()
    {
        return array(
            self::M_HAS_NEW_ACTION_STUDENT  	=> _('В занятиях есть сообщения студентов, на которые Вы не ответили'),
            self::M_NOT_ALL_STUDENTS_HAS_MARK	=> _('Не всем студентам выставлена оценка'),
        );
    }
	
	static public function getManagers()
	{
		return array(
			68065, # Бодров М.С
			68021, # Золотова Ю.Е.
		);
	}
	
	
}