<?php
class HM_StudentCertificate_Statement_StatementModel extends HM_Model_Abstract
{
    const TYPE_CHANGE_FIO 		= 1;
    const TYPE_ACADEM_HOLIDAY	= 2;
    const TYPE_REMAND			= 3;
    const TYPE_CHANGE_PROGRAM	= 4;
    const TYPE_DISCOUNT			= 5;
	
	
	const REMAND_USER_DESIRE 		= 1;
	const REMAND_OTHER_UNIVERSITY	= 2;
	
	
	const STATUS_NEW 		= 0;
	const STATUS_IN_WORK	= 1;
	const STATUS_READY		= 2;
	const STATUS_NOT_AGREED	= 3;
	
	
	
	 static public function getStatuses() {
		 return array(
			 self::STATUS_NEW		 => _('Новая'),
			 self::STATUS_IN_WORK	 => _('В работе'),
			 self::STATUS_READY		 => _('Готово'),
			 self::STATUS_NOT_AGREED => _('Не cогласовано'),
		 );		 
	 }
	
	
	 static public function getTypes() {
        return array(
            self::TYPE_CHANGE_FIO      	=> _('Об измененнии ФИО'),
            self::TYPE_ACADEM_HOLIDAY   => _('Об академическом отпуске'),
            self::TYPE_REMAND      		=> _('Об отчислении'),
            #self::TYPE_CHANGE_PROGRAM   => _('О переводе на другую образовательную программу'),
            #self::TYPE_DISCOUNT      	=> _('О предоставлении скидки'),            
        );
    }
	
	static public function getRemandTypes(){
		return array(
            self::REMAND_USER_DESIRE      	=> _('По инициативе обучающегося'),
            self::REMAND_OTHER_UNIVERSITY	=> _('В порядке перевода в другой ВУЗ'),
		);		
	}
	
	
	static public function getRequiredFields($type_id){
		$data = array(
			self::TYPE_CHANGE_FIO 		=> array(	'type_id', 				'LastName', 			'FirstName', 			'date_birth', 			'reason', 		'passport_series', 'passport_number',
													'passport_issued_name', 'passport_issued_date', 'passport_issued_code', 'address_registration', 'address_birth', 
												),
													
			self::TYPE_ACADEM_HOLIDAY 	=> array('type_id', 'LastName', 'FirstName', 'date_birth', 'reason', 'date_begin', 'date_end', ),
			self::TYPE_REMAND 			=> array('type_id', 'LastName', 'FirstName', 'remand_type_id', ),
			self::TYPE_CHANGE_PROGRAM 	=> array('type_id', 'LastName', 'FirstName',),
			self::TYPE_DISCOUNT 		=> array('type_id', 'LastName', 'FirstName',),
		);
		return isset($data[$type_id]) ? $data[$type_id] : array();		
	}


	/**
	 * Список полей, которые будут заполнятся при создании записи в БД
	*/
	static public function getAllFields(){
		return array(
			'LastName', 'FirstName', 'Patronymic', 'date_birth', 'reason',	
			'passport_series', 'passport_number', 'passport_issued_name', 'passport_issued_date', 'passport_issued_code', 'address_registration', 'address_birth',
			'date_begin', 'date_end',
			'remand_type_id',
		);		
	}


	
}