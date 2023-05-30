<?php
class HM_Timetable_TimetableModel extends HM_Model_Abstract
{
	const TYPE_ODD	= 1;
	const TYPE_EVEN = 2;
	
	# Sunday - воскресенье
	# Monday - понедельник
	# воскресенье считается за начало след. недели. Поэтому возвращаемся к прошедшей неделе.
	static public function getMondayCurrent()
	{
		$dt = new DateTime();
		$dt->setTime(0, 0, 0);
		$dt->modify(('Sunday' == $dt->format('l')) ? 'Monday last week' : 'Monday this week');
		return $dt->format('Y-m-d');
	}
	
	static public function getSundayCurrent()
	{
		$dt = new DateTime();
		$dt->setTime(0, 0, 0);
		$dt->modify(('Sunday' == $dt->format('l')) ? 'Sunday last week' : 'Sunday this week');
		return $dt->format('Y-m-d');
	}
	
	static public function getMondayNextWeek()
	{
		$dt = new DateTime();
		$dt->setTime(0, 0, 0);
		$dt->modify(('Sunday' == $dt->format('l')) ? 'Monday this week' : 'Monday next week');
		return $dt->format('Y-m-d');
	}
	
	
	static public function getSundayNextWeek()
	{
		$dt = new DateTime();
		$dt->setTime(0, 0, 0);
		$dt->modify(('Sunday' == $dt->format('l')) ? 'Sunday this week' : 'Sunday next week');
		return $dt->format('Y-m-d');
	}
	
	
	static public function getListTimeBegin()
	{
		return array(
			1 => '8:30',		
			2 => '10:10',		
			3 => '12:10',		
			4 => '13:50',		
			5 => '15:30',		
			6 => '17:10',	
			7 => '18:50',		
			8 => '20:30',
		);
	}
	
	
	static public function getListTimeEnd()
	{
		return array(
			1 => '10:00',
			2 => '11:40',
			3 => '13:40',
			4 => '15:20',
			5 => '17:00',
			6 => '18:40',
			7 => '20:20',
			8 => '22:00',
		);
	}
	
	static public function getListAcademicHours()
	{
		return array(					
			1 => '1-я пара',
			2 => '2-я пара',
			3 => '3-я пара',
			4 => '4-я пара',
			5 => '5-я пара',
			6 => '6-я пара',
			7 => '7-я пара',
			8 => '8-я пара',
		);
	}
	
	static public function getListDisciplineTypes()
	{
		return array(					
			1 => 'семинар',
			2 => 'лекция',
			3 => 'практическое занятие',
			4 => 'лабораторная работа',
			5 => 'экзамен',
			6 => 'зачет',
			7 => 'диф. зачет',
		);
	}
	
	static public function getListEvenOdd()
	{
		return array(					
			self::TYPE_ODD	=> 'Нечетная неделя',
			self::TYPE_EVEN	=> 'Четная неделя',
		);
	}
	
	static public function getListWeekDays()
	{
		return array(					
			1 => 'Понедельник',
			2 => 'Вторник',
			3 => 'Среда',
			4 => 'Четверг',
			5 => 'Пятница',
			6 => 'Суббота',
		);
	}
	
	static public function getListFaculties()
	{
		return array(
			15 => 'Факультет управления',	
			18 => 'Факультет информационных технологий',		
			10 => 'Факультет социологии',
			1 => 'Гуманитарный факультет',		
			4 => 'Факультет информационных технологий и техносферной безопасности',		
			5 => 'Лингвистический факультет',
			14 => 'Факультет коммуникативного менеджмента',		
			19 => 'Факультет экологии и техносферной безопасности',		
			6 => 'Факультет подготовки кадров высшей квалификации',		
			11 => 'Юридический факультет',
			16 => 'ВЫСШАЯ ШКОЛА МУЗЫКИ имени А. ШНИТКЕ',		
			9 => 'Экономический факультет',
			7 => 'Факультет психологии',
			17 => 'Факультет физической культуры',		
			8 => 'Факультет социальной работы',
			20 => 'Колледж РГСУ',
			21 => 'Филиал РГСУ в г. Клину Московской области',		
			22 => 'Филиал РГСУ в г. Павловский Посаде Московской области',		
			23 => 'Филиал РГСУ в г. Анапе Краснодарского края',
			24 => 'Филиал РГСУ в г. Минске Республики Беларусь',	
			25 => 'Филиал РГСУ в г. Ош Киргизской Республики',	
			26 => 'Медицинский факультет',
			27 => 'Факультет подготовки научных и научно-педагогических кадров',
			28 => 'Факультет искусств',
			29 => 'Сочи',
			30 => 'Пятигорск',
			31 => 'Дедовск',
		);
	}
	
	static public function getListStudyForms()
	{
		return array(					
			1 => 'Очная',
			2 => 'Заочная',
			3 => 'Очно-заочная',
			4 => 'Заочная форма с использованием дистанционных технологий',
		);
	}
	
	
	static public function isShowMultipleLink($name)
	{
		$name    = mb_strtolower(trim($name));
		$pattern = "/^иностранный язык [0-9].*/i";
		
		if(
			   $name == 'иностранный язык'
			|| preg_match($pattern, $name)
		){
			return true;
		}
		return false;
	}
	
	
	
	
	
	
	
	
	
	
}