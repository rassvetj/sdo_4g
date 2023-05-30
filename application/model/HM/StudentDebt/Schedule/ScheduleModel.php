<?php
class HM_StudentDebt_Schedule_ScheduleModel extends HM_Model_Abstract
{
    const ERR_FILE_EMPTY  	= 1;
    const ERR_CLEAR_TABLE 	= 2;
    const ERR_INSERT 		= 3;
    const ERR_NOT_NEW_DATA	= 4;
    
	
	public static function getErrorText($code)
	{		
		$list = array(
			self::ERR_FILE_EMPTY  	=> _('Файл пуст или неверный формат'),
			self::ERR_CLEAR_TABLE 	=> _('Не удалось удалить старые данные'),
			self::ERR_INSERT 		=> _('Не удалось добавить новые данные'),
			self::ERR_NOT_NEW_DATA	=> _('Нет новых данных'),
		);
		return $list[$code];
	}
	
	public static function getFileColList()
	{
		return array(
			0  => 'mid_external',
			1  => 'student_fio',
			2  => 'programm',
			3  => 'semester',
			4  => 'attempt',
			5  => 'chair',
			6  => 'study_form',
			7  => 'control_form',
			8  => 'group_name',
			9  => 'discipline',
			10 => 'language',
			11 => 'teacher',
			12 => 'date_day',
			13 => 'date_time',
			14 => 'place',
			15 => 'commission_url',
		);		
	}
	
	public static function getExportFieldList()
	{
		return array(
			'mid_external'		=> _('Код студент'),			
			'student_fio'		=> _('Студент'),			
			'programm' 			=> _('Учебный план'),
			'semester' 			=> _('Семестр'),
			'attempt' 			=> _('Номер попытки'),
			'chair' 			=> _('Кафедра'),
			'study_form' 		=> _('Форма обучения'),
			'control_form' 		=> _('Вид нагрузки кафедры'),
			'group_name' 		=> _('Учебная группа'),
			'discipline'		=> _('Дисциплина'),
			'language'			=> _('Деление на языки'),
			'teacher' 			=> _('Преподаватель'),
			'date_day' 			=> _('Дата'),
			'date_time' 		=> _('Время'),
			'place' 			=> _('Корпус/Аудитория'),
			'commission_url'	=> _('Ссылка на комиссию'),
		);		
	}
	
    
}