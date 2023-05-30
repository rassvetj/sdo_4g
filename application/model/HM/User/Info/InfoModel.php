<?php

class HM_User_Info_InfoModel extends HM_Model_Abstract
{
    const STATUS_FILED_FOR_EXPELLED = 'Подан на отчисление';
    const STATUS_ON_ACADEMIC_LEAVE  = 'В академическом отпуске';
    const STATUS_IN_STUDY           = 'Учится';
	
	const STUDY_FORM_FULL_TIME      = 'Очная (дневная)';
	const STUDY_FORM_EVENING        = 'Очно-заочная (вечерняя)';										
	
	const BASED_BUDGET			    = 'Бюджетная';
	const BASED_CONTRACT		    = 'Контракт';
	
	const TYPE_IS_FOREINGER			= 1;
	
	const EDU_TYPE_HIGHER_MASTER        = 'Высшее образование (магистр)';
	const EDU_TYPE_HIGHER_BACHELOR      = 'Высшее образование (бакалавр)';
	const EDU_TYPE_HIGHER_SPECIALIST    = 'Высшее образование (специалист)';
	const EDU_TYPE_POSTGRADUATE         = 'Аспирантура, ординатура, адъюнктура';
	const EDU_TYPE_SECONDARY_VOCATIONAL = 'Среднее профессиональное (повышенный уровень)';
	
	public function isAvailableSubjects()
	{
		return 
			in_array($this->status, array(self::STATUS_FILED_FOR_EXPELLED, self::STATUS_ON_ACADEMIC_LEAVE))
			? false : true;
	}
	
	public function getHumanizedStatus()
	{
		if($this->status == self::STATUS_FILED_FOR_EXPELLED){
			return _('Вы поданы на отчисление');
		}

		if($this->status == self::STATUS_ON_ACADEMIC_LEAVE){
			return _('Вы находитесь в академическом отпуске');
		}		
	}

	public function getHalfAccessStatuses()
	{
		return array(
			self::STATUS_FILED_FOR_EXPELLED,
			self::STATUS_ON_ACADEMIC_LEAVE,
		);
	}
}