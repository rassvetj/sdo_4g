<?php
class HM_RecordCard_RecordCardModel extends HM_Model_Abstract
{
	/*
	const PERIOD_FIRST  = 1;
	const PERIOD_SECOND	= 2;
    
    static public function getPeriods() {
        return array(
            self::PERIOD_FIRST  => _('Первое полугодие'),
            self::PERIOD_SECOND	=> _('Второе полугодие'),            
        );
    }
	
	static public function getPeriodsPDF() {
        return array(
            self::PERIOD_FIRST  => _('1 полугодие'),
            self::PERIOD_SECOND	=> _('2 полугодие'),            
        );
    }
	*/
	
	const STATUS_STUDY  = 'Учится';
	const STATUS_EXPEL  = 'Отчислен';	
	/*
	В академическом отпуске
	Отчислен
	Поступает
	Учится
	На повторном обучении
	На каникулах
	*/
	
	
	const TYPE_GIA = 'ГИА';
	/*
		and etc
	*/
	
	const TYPE_ORDER_ACADEMIC_LEAVE 	= 'Академический отпуск';
	#const ACADEMIC_LEAVE_REASON_START 	= 'Предоставление академического отпуска';
	#const ACADEMIC_LEAVE_REASON_END 	= 'Допуск к учебному процессу';
	
	
	public static function getOrderName($name){
		$c = array(
			'Зачисление' 					=> _('о зачислении'),
			'Стипендия' 					=> _('о стипендии'),
			'Перевод' 						=> _('о переводе'),
			'Внесение изменений в приказ' 	=> _('о внесении изменений в приказ'),
			'Сессия (летняя и зимняя)' 		=> _('о сессии'),
			'Разное' 						=> _('о разном'),
			'ГИА' 							=> _('о ГИА'),
			'Изменение ФИО' 				=> _('об изменении ФИО'),
			'Отчисление' 					=> _('об отчислении'),
			'Академический отпуск' 			=> _('об академическом отпусе'),
			'Восстановление' 				=> _('о восстановлении'),
			'Оплата' 						=> _('об оплате'),			
			'Восстановление и перевод' 		=> _('о восстановлении и переводе'),
		);			
		return $c[$name];        
	}
	
	public static function getCourceName($id){
		$c = array(
			1	=> _('ПЕРВЫЙ'),
			2	=> _('ВТОРОЙ'),
			3	=> _('ТРЕТИЙ'),
			4	=> _('ЧЕТВЕРТЫЙ'),
			5	=> _('ПЯТЫЙ'),
			6	=> _('ШЕСТОЙ'),	
			7	=> _('СЕДЬМОЙ'),	
		);			
		return $c[$id];        
	}
	
	public static function getAcademicLeaveStartReasons()
	{
		return array(
			_('Предоставление академического отпуска'),
			_('В связи с призывом в ВС РФ'),
		);
	}
	
	public static function getAcademicLeaveEndReasons()
	{
		return array(
			_('Допуск к учебному процессу'),
		);
	}
	
	

    
}