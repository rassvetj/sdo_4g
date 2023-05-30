<?php
class HM_Report_ReportModel extends HM_Model_Abstract
{
    const STATUS_PUBLISHED = 1;
    const STATUS_UNPUBLISHED = 0;
	
	const REPORT_TYPE_BALL	= 1;
	const REPORT_TYPE_PROVE = 2;
	
	const STATUS_TIME_CURRENT	= 1;
	const STATUS_TIME_PAST		= 2;
	
	const STATUS_DEBT_YES	= 1;
	const STATUS_DEBT_NO	= 2;
	
	const ACTIVITY_ALL		= 0;
	const ACTIVITY_YES		= 1;
	const ACTIVITY_NO		= 2;
	
	const STATUS_DO_YES	= 1;
	const STATUS_DO_NO	= 2;
	
	
	const TYPE_FULL_TIME = 1; # очная форма
	const TYPE_PRACTICE  = 2; # практика
	
	
	
	# не заполненно в БД поле факультета или кафедры
	const FACULTY_EMPTY = '-1';
	const CHAIR_EMPTY   = '-1';
	
	
	const DO_ALL		= '-1'; # и ДО и неДО


	
	
    static public function getStatuses()
    {
        return array(
            self::STATUS_UNPUBLISHED => _('Не опубликован'),
            self::STATUS_PUBLISHED => _('Опубликован')
        );
    }

    static function getStatus($status)
    {
        $statuses = self::getStatuses();
        return $statuses[$status];
    }
	
	static public function getTimeStatuses()
    {
        return array(
            self::STATUS_TIME_CURRENT	=> _('Текущая'),
            self::STATUS_TIME_PAST 		=> _('Прошедшая')
        );
    }
	
	static public function getDebtStatuses()
    {
        return array(
            self::STATUS_DEBT_YES	=> _('Продлена'),
            self::STATUS_DEBT_NO 	=> _('Не продлена')
        );
    }
	
	static public function getSemesters(){
        return array(
            1	=> 1,
			2	=> 2,
			3	=> 3,
			4	=> 4,
			5	=> 5,
			6	=> 6,
			7	=> 7,
			8	=> 8,
			9	=> 9,
			10	=> 10,
			11	=> 11,
			12	=> 12,            
        );
    }
	
	# аткивность стдуентов в сессии
	static public function getActivity(){
		return array(
			self::ACTIVITY_ALL => _('Все'),
			self::ACTIVITY_YES => _('Да'),
			self::ACTIVITY_NO => _('Нет'),
		);		
	}
	
	
	
	static public function getDoTypes()
    {
        return array(
            self::STATUS_DO_YES	=> _('ДО'),
            self::STATUS_DO_NO 	=> _('Не ДО'),
        );
    }
	
	
	static public function getReportTypes()
    {
        return array(
            self::TYPE_FULL_TIME	=> _('Только очной формы'),
            self::TYPE_PRACTICE		=> _('Только практики'),
        );
    }
	
	
}