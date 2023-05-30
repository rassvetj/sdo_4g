<?php
class HM_Ticket_TicketModel extends HM_Model_Abstract
{
	const PERIOD_FIRST  = 1;
	const PERIOD_SECOND	= 2;
	
	const TYPE_EDUCATION 	= 1;
	const TYPE_LIVING		= 2;
	
	const PAY_TYPE_PDF			= 1; #оплата через формирование pdf 
	const PAY_TYPE_ACQUIRING	= 2; #оплата картой
	
	
	const SERVICE_TYPE_EDUCATION 	= 'EDUCATION';	#Образование
	const SERVICE_TYPE_FINE 		= 'FINE';		#Штрафы
	const SERVICE_TYPE_HOSTEL 		= 'HOSTEL';		#Оплата общежития
	const SERVICE_TYPE_POOL 		= 'POOL';		#Бассейн
	const SERVICE_TYPE_HOTEL 		= 'HOTEL';		#Базы отдыха
	const SERVICE_TYPE_JOURNAL 		= 'JOURNAL';	#Оплата по лицензионному договору о предоставлении права использования произведения
	const SERVICE_TYPE_UTILITIES	= 'UTILITIES';	#Возмещение коммунальных услуг
	const SERVICE_TYPE_LIBRARY		= 'LIBRARY';	#Библиотека
	const SERVICE_TYPE_TIR			= 'TIR';		#Услуги тира
	const SERVICE_TYPE_SOUVENIR			= 'SOUVENIR';		#Сувенирная продукция
	const SERVICE_TYPE_CHANCERY			= 'CHANCERY';		#Канцелярская продукция
	const SERVICE_TYPE_BOOK				= 'BOOK';			#Книги
	const SERVICE_TYPE_CLOTHING			= 'CLOTHING';		#Одежда
	const SERVICE_TYPE_POLYGRAPHY		= 'POLYGRAPHY';		#Полиграфия
	const SERVICE_TYPE_POOLCERTIFICATE 	= 'POOLCERTIFICATE';#Оплата медицинской справки в бассейн
	const SERVICE_TYPE_LAUNDRY		 	= 'LAUNDRY';		# Прачечная
	const SERVICE_TYPE_DONATION		 	= 'DONATION';		# Пожертвование
	
	

	
	const HOTEL_SERVICE_MEDICAL	= 15; 	#  соответствует id из таблицы ticket_services. Делает недоступным этот пункт для некоторых пансионатов
	const HOTEL_CHAIKOVSKY = 10; 		#  соответствует id из таблицы ticket_items. Делает недоступным этот пункт для некоторых пансионатов
	
	
	const PAY_FAIL = 1; # оплата не прошла
	const PAY_SUCCSESS = 2; # платеж произведен
	
	/**
	 * список сервисов, которые активны для филиалов.
	*/
	static public function enabledFilialTicketServices(){
		return array(
			self::SERVICE_TYPE_EDUCATION, 	//Образование
			self::SERVICE_TYPE_FINE,  		//Штрафы
			self::SERVICE_TYPE_HOSTEL,		//Оплата общежития
			self::SERVICE_TYPE_LAUNDRY,		//Оплата общежития
			self::SERVICE_TYPE_DONATION,	//Пожертвование
		);
	}
	
	
	static public function getStatuses(){
		return array(
			self::PAY_FAIL 		=> _('Оплата не произведена'), 	# оплата не прошла
			self::PAY_SUCCSESS	=> _('Платеж произведен'), 		# платеж произведен			
		);
	}
	
	static public function getTypes(){
		return array(
			self::PAY_TYPE_PDF 			=> _('квитанция'), 	# оплата не прошла
			self::PAY_TYPE_ACQUIRING	=> _('карта'), 		# платеж произведен			
		);
	}
	
	
	/**
	 * список id элементов формы, которые надо показывать для определенного типа услуги.
	**/
	static public function getFormShowElements(){
		return array(
			self::SERVICE_TYPE_EDUCATION	=> array(
				'service_type_id',
				'service_education_id',
				'contract_number',
				'contract_date',
				'period_id',
				'sum1',
				'sum2',
				
				'payerLastName',				
				'payerFirstName',				
				'payerPatronymic',				
				'payerEmail',				
				'payerAddress',
				
				'forWhomPayLastName',				
				'forWhomPayFirstName',				
				'forWhomPayPatronymic',
			),
			self::SERVICE_TYPE_FINE			=> array(
				'service_type_id',
				'fine_id',
				'contract_number',
				'contract_date',
				'period_begin',
				'period_end',
				'sum1',
				'sum2',
				
				'payerLastName',				
				'payerFirstName',				
				'payerPatronymic',				
				'payerEmail',				
				'payerAddress',
				
				'forWhomPayLastName',				
				'forWhomPayFirstName',				
				'forWhomPayPatronymic',
			),
			self::SERVICE_TYPE_HOSTEL		=> array(
				'service_type_id',
				'hostel_id',
				'contract_number',
				'contract_date',
				'period_begin',
				'period_end',
				'sum1',
				'sum2',
				
				'payerLastName',				
				'payerFirstName',				
				'payerPatronymic',				
				'payerEmail',				
				'payerAddress',
				
				'forWhomPayLastName',				
				'forWhomPayFirstName',				
				'forWhomPayPatronymic',
			),
			self::SERVICE_TYPE_POOL			=> array(
				'service_type_id',
				'service_pool_id',				
				'period_begin',
				'period_end',
				'sum1',
				'sum2',
				
				'payerLastName',				
				'payerFirstName',				
				'payerPatronymic',				
				'payerEmail',				
				'payerAddress',
			),
			self::SERVICE_TYPE_HOTEL		=> array(
				'service_type_id',
				'hotel_id',
				'service_hotel_id',
				'period_begin',
				'period_end',
				'sum1',
				'sum2',
				
				'payerLastName',				
				'payerFirstName',				
				'payerPatronymic',				
				'payerEmail',				
				'payerAddress',
			),
			self::SERVICE_TYPE_JOURNAL		=> array(
				'service_type_id',
				'journal_id',
				'journal_year',
				'journal_number',
				'period_begin',
				'period_end',
				'sum1',
				'sum2',
				
				'payerLastName',				
				'payerFirstName',				
				'payerPatronymic',				
				'payerEmail',				
				'payerAddress',
			),
			self::SERVICE_TYPE_UTILITIES	=> array(
				'service_type_id',
				'contract_number',
				'contract_date',
				'period_begin',
				'period_end',
				'sum1',
				'sum2',
				
				'payerLastName',				
				'payerFirstName',				
				'payerPatronymic',				
				'payerEmail',				
				'payerAddress',
			),
			self::SERVICE_TYPE_LIBRARY		=> array(
				'service_type_id',
				'service_library_id',
				'library_card_number',
				'sum1',
				'sum2',
				
				'payerLastName',				
				'payerFirstName',				
				'payerPatronymic',				
				'payerEmail',				
				'payerAddress',
				
				'forWhomPayLastName',				
				'forWhomPayFirstName',				
				'forWhomPayPatronymic',
			),
			
			self::SERVICE_TYPE_TIR		=> array(
				'service_type_id',				
				'sum1',
				'sum2',
				
				'payerLastName',				
				'payerFirstName',				
				'payerPatronymic',				
				'payerEmail',				
				'payerAddress',				
			),
			
			self::SERVICE_TYPE_SOUVENIR		=> array(
				'service_type_id',				
				'sum1',
				'sum2',
				
				'payerLastName',				
				'payerFirstName',				
				'payerPatronymic',				
				'payerEmail',				
				'payerAddress',				
			),
			
			self::SERVICE_TYPE_CHANCERY		=> array(
				'service_type_id',				
				'sum1',
				'sum2',
				
				'payerLastName',				
				'payerFirstName',				
				'payerPatronymic',				
				'payerEmail',				
				'payerAddress',				
			),
			
			self::SERVICE_TYPE_BOOK		=> array(
				'service_type_id',				
				'sum1',
				'sum2',
				
				'payerLastName',				
				'payerFirstName',				
				'payerPatronymic',				
				'payerEmail',				
				'payerAddress',				
			),
			
			self::SERVICE_TYPE_CLOTHING		=> array(
				'service_type_id',				
				'sum1',
				'sum2',
				
				'payerLastName',				
				'payerFirstName',				
				'payerPatronymic',				
				'payerEmail',				
				'payerAddress',				
			),
			
			self::SERVICE_TYPE_POLYGRAPHY		=> array(
				'service_type_id',				
				'sum1',
				'sum2',
				
				'payerLastName',				
				'payerFirstName',				
				'payerPatronymic',				
				'payerEmail',				
				'payerAddress',				
			),
			
			self::SERVICE_TYPE_POOLCERTIFICATE		=> array(
				'service_type_id',				
				'sum1',
				'sum2',
				
				'payerLastName',				
				'payerFirstName',				
				'payerPatronymic',				
				'payerEmail',				
				'payerAddress',				
			),
			
			self::SERVICE_TYPE_LAUNDRY			=> array(
				'service_type_id',
				'service_laundry_id',				
				'period_begin',
				'period_end',
				'sum1',
				'sum2',
				
				'payerLastName',				
				'payerFirstName',				
				'payerPatronymic',				
				'payerEmail',				
				'payerAddress',
				
				'forWhomPayLastName',				
				'forWhomPayFirstName',				
				'forWhomPayPatronymic',
			),
			
			self::SERVICE_TYPE_DONATION			=> array(
				'service_type_id',				
				'sum1',
				'sum2',
				
				'payerLastName',				
				'payerFirstName',				
				'payerPatronymic',				
				'payerEmail',				
				'payerAddress',	
			),
			
			
			
		);		
	}
	
	/**
	 * id блоков формы, которые надо скрыть. При этом все остальные будут отображены.
	*/	
	static public function getFormHidedBlocks(){
		return array(
			self::SERVICE_TYPE_EDUCATION	=> array(
				'groupBase_6',				
				'groupBase_7',				
			),
			self::SERVICE_TYPE_FINE			=> array(
				'groupBase_5',
			),
			self::SERVICE_TYPE_HOSTEL		=> array(
				'groupBase_5',
			),
			self::SERVICE_TYPE_POOL			=> array(
				'groupBase_3',
				'groupBase_4',
				'groupBase_5',
				'groupForWhomPay',
			),
			self::SERVICE_TYPE_HOTEL		=> array(
				'groupBase_4',
				'groupBase_5',
				'groupForWhomPay',
			),
			self::SERVICE_TYPE_JOURNAL		=> array(
				'groupBase_5',
				'groupForWhomPay',				
			),
			self::SERVICE_TYPE_UTILITIES	=> array(
				'groupBase_2',
				'groupBase_5',
				'groupForWhomPay',					
			),
			self::SERVICE_TYPE_LIBRARY		=> array(
				'groupBase_4',
				'groupBase_5',
				'groupBase_6',				
				'groupBase_7',
			),
			self::SERVICE_TYPE_TIR		=> array(
				'groupBase_2',
				'groupBase_3',
				'groupBase_4',
				'groupBase_5',
				'groupBase_6',				
				'groupBase_7',
				'groupForWhomPay',
			),
			self::SERVICE_TYPE_SOUVENIR		=> array(
				'groupBase_2',
				'groupBase_3',
				'groupBase_4',
				'groupBase_5',
				'groupBase_6',				
				'groupBase_7',
				'groupForWhomPay',
			),
			self::SERVICE_TYPE_CHANCERY		=> array(
				'groupBase_2',
				'groupBase_3',
				'groupBase_4',
				'groupBase_5',
				'groupBase_6',				
				'groupBase_7',
				'groupForWhomPay',
			),
			self::SERVICE_TYPE_BOOK		=> array(
				'groupBase_2',
				'groupBase_3',
				'groupBase_4',
				'groupBase_5',
				'groupBase_6',				
				'groupBase_7',
				'groupForWhomPay',
			),
			self::SERVICE_TYPE_CLOTHING		=> array(
				'groupBase_2',
				'groupBase_3',
				'groupBase_4',
				'groupBase_5',
				'groupBase_6',				
				'groupBase_7',
				'groupForWhomPay',
			),
			self::SERVICE_TYPE_POLYGRAPHY		=> array(
				'groupBase_2',
				'groupBase_3',
				'groupBase_4',
				'groupBase_5',
				'groupBase_6',				
				'groupBase_7',
				'groupForWhomPay',
			),
			self::SERVICE_TYPE_POOLCERTIFICATE		=> array(
				'groupBase_2',
				'groupBase_3',
				'groupBase_4',
				'groupBase_5',
				'groupBase_6',				
				'groupBase_7',
				'groupForWhomPay',
			),
			self::SERVICE_TYPE_LAUNDRY			=> array(
				'groupBase_3',
				'groupBase_4',
				'groupBase_5',
				#'groupForWhomPay',
			),
			
			self::SERVICE_TYPE_DONATION			=> array(
				'groupBase_2',
				'groupBase_3',
				'groupBase_4',
				'groupBase_5',
				'groupBase_6',				
				'groupBase_7',
				'groupForWhomPay',
			),
			
			
			
			
		);
	}
	
	
	
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
	
	/**
	 * для эквайринга
	*/
	static public function getPeriodsValue() {
        return array(
            self::PERIOD_FIRST  => _('01.09-31.01'),
            self::PERIOD_SECOND	=> _('01.02-30.06'),            
        );
    }
	
	
	
	static public function getTypeContract() {
        return array(
            self::TYPE_EDUCATION 	=> _('Образование'),
            self::TYPE_LIVING		=> _('Проживание'),            
        );
    }
	
	
	/**
	 * список полей формы для каждого типа сервиса, обязательных для заполнения
	*/
	static public function getFormRequiredElements(){
		return array(
			self::SERVICE_TYPE_EDUCATION	=> array(
				'filial_id',				
				'service_type_id',				
				'service_education_id',				
				'contract_number',				
				'contract_date',				
				'period_id',				
				'sum1',				
				'payerLastName',				
				'payerFirstName',				
				'payerPatronymic',				
				'payerEmail',				
				'payerAddress',				
				'forWhomPayLastName',				
				'forWhomPayFirstName',				
				'forWhomPayPatronymic',				
			),
			self::SERVICE_TYPE_FINE			=> array(
				'filial_id',				
				'service_type_id',			
				'fine_id',			
				'contract_number',			
				'contract_date',			
				'period_begin',			
				'period_end',			
				'sum1',

				'payerLastName',				
				'payerFirstName',				
				'payerPatronymic',				
				'payerEmail',				
				'payerAddress',				
				'forWhomPayLastName',				
				'forWhomPayFirstName',				
				'forWhomPayPatronymic',				
			),
			self::SERVICE_TYPE_HOSTEL		=> array(
				'filial_id',				
				'service_type_id',			
				'hostel_id',			
				'contract_number',			
				'contract_date',			
				
				'period_begin',			
				'period_end',			
				'sum1',

				'payerLastName',				
				'payerFirstName',				
				'payerPatronymic',				
				'payerEmail',				
				'payerAddress',				
				'forWhomPayLastName',				
				'forWhomPayFirstName',				
				'forWhomPayPatronymic',				
			),
			self::SERVICE_TYPE_POOL			=> array(
				'filial_id',				
				'service_type_id',			
				'service_pool_id',			
				
				'period_begin',			
				'period_end',			
				'sum1',

				'payerLastName',				
				'payerFirstName',				
				'payerPatronymic',				
				'payerEmail',				
				'payerAddress',											
			),
			self::SERVICE_TYPE_HOTEL		=> array(
				'filial_id',				
				'service_type_id',
				
				'hotel_id',
				'service_hotel_id',
				
				'period_begin',			
				'period_end',			
				'sum1',

				'payerLastName',				
				'payerFirstName',				
				'payerPatronymic',				
				'payerEmail',				
				'payerAddress',		
			),
			self::SERVICE_TYPE_JOURNAL		=> array(
				'filial_id',				
				'service_type_id',			
				'journal_id',			
				'journal_year',			
				'journal_number',			
				
				
				'period_begin',			
				'period_end',			
				'sum1',

				'payerLastName',				
				'payerFirstName',				
				'payerPatronymic',				
				'payerEmail',				
				'payerAddress',	
			),
			self::SERVICE_TYPE_UTILITIES	=> array(
				'filial_id',				
				'service_type_id',			
				'contract_number',			
				'contract_date',			
							
				'period_begin',			
				'period_end',			
				'sum1',

				'payerLastName',				
				'payerFirstName',				
				'payerPatronymic',				
				'payerEmail',				
				'payerAddress',	
			),
			self::SERVICE_TYPE_LIBRARY		=> array(
				'filial_id',				
				'service_type_id',			
				'service_library_id',			
				'library_card_number',			
				
				'sum1',

				'payerLastName',				
				'payerFirstName',				
				'payerPatronymic',				
				'payerEmail',				
				'payerAddress',				
				'forWhomPayLastName',				
				'forWhomPayFirstName',				
				'forWhomPayPatronymic',	
			),
			self::SERVICE_TYPE_TIR		=> array(
				'filial_id',				
				'service_type_id',			
				'sum1',

				'payerLastName',				
				'payerFirstName',				
				'payerPatronymic',				
				'payerEmail',				
				'payerAddress',
			),
			self::SERVICE_TYPE_SOUVENIR		=> array(
				'filial_id',				
				'service_type_id',			
				'sum1',

				'payerLastName',				
				'payerFirstName',				
				'payerPatronymic',				
				'payerEmail',				
				'payerAddress',
			),
			self::SERVICE_TYPE_CHANCERY		=> array(
				'filial_id',				
				'service_type_id',			
				'sum1',

				'payerLastName',				
				'payerFirstName',				
				'payerPatronymic',				
				'payerEmail',				
				'payerAddress',
			),
			self::SERVICE_TYPE_BOOK		=> array(
				'filial_id',				
				'service_type_id',			
				'sum1',

				'payerLastName',				
				'payerFirstName',				
				'payerPatronymic',				
				'payerEmail',				
				'payerAddress',
			),
			self::SERVICE_TYPE_CLOTHING		=> array(
				'filial_id',				
				'service_type_id',			
				'sum1',

				'payerLastName',				
				'payerFirstName',				
				'payerPatronymic',				
				'payerEmail',				
				'payerAddress',
			),
			self::SERVICE_TYPE_POLYGRAPHY		=> array(
				'filial_id',				
				'service_type_id',			
				'sum1',

				'payerLastName',				
				'payerFirstName',				
				'payerPatronymic',				
				'payerEmail',				
				'payerAddress',
			),
			self::SERVICE_TYPE_POOLCERTIFICATE		=> array(
				'filial_id',				
				'service_type_id',			
				'sum1',

				'payerLastName',				
				'payerFirstName',				
				'payerPatronymic',				
				'payerEmail',				
				'payerAddress',
			),	
			self::SERVICE_TYPE_LAUNDRY			=> array(
				'filial_id',				
				'service_type_id',			
				'service_laundry_id',			
				
				'period_begin',			
				'period_end',			
				'sum1',

				'payerLastName',				
				'payerFirstName',				
				'payerPatronymic',				
				'payerEmail',				
				'payerAddress',		

				'forWhomPayLastName',				
				'forWhomPayFirstName',				
				'forWhomPayPatronymic',				
			),
			
			self::SERVICE_TYPE_DONATION			=> array(
				'filial_id',				
				'service_type_id',			
				'sum1',

				'payerLastName',				
				'payerFirstName',				
				'payerPatronymic',				
				'payerEmail',				
				'payerAddress',
			),
			
			
			
		);
	}
	
    
}