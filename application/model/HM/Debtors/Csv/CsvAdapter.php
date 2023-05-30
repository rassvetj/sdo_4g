<?php
class HM_Debtors_Csv_CsvAdapter extends HM_Adapter_Csv_Abstract
{
    // Сколько первых строк будет пропущено
    protected $_skipLines = 1;

    public function getMappingArray()
    {
        return array(
			0 =>  'mid_external',			//КодСтудента - GUID
			1 =>  'fio',
			2 =>  'session_external_id',	//КодСессии
			3 =>  'name',					# Дисциплина - название предмета или сессии ?
			4 =>  'specialty',
			5 =>  'dateBegin',
			6 =>  'dateEnd',			
			7 =>  'semester', 				//семестр. Только для информативности при импорте. В БД не заносится			
			8 =>  'time_begin_debtor', 		//ДатаНачалаДосдачи
			9 =>  'time_ended_debtor', 		//ДатаДосдачи
			10 =>  'time_begin_debtor_2',   //ДатаНачалаДосдачи2
			11 =>  'time_ended_debtor_2', 	# вторая волна продления.
			12=>  'tutor', 					# GUID тьюторов через запятую ",". Тьютор, назначенный на первую волну продления на дату time_ended_debtor. несколько
			13=>  'tutor_2', 				# GUID тьюторов через запятую ",". Тьютор, назначенный на вторую волну продления на дату time_ended_debtor_2
        );
    }
	
	public static function getColumnNameList(){
		return array(
			'mid_external' 			=> 'КодСтудента',
			'fio' 					=> 'ФИО',
			'session_external_id' 	=> 'КодСессии',
			'name' 					=> 'Дисциплина',
			'specialty' 			=> 'Специальность',
			'dateBegin' 			=> 'ДатаНачалаСессии',
			'dateEnd' 				=> 'ДатаОкончанияСессии',
			'semester' 				=> 'НомерСеместра',
			'time_begin_debtor' 	=> 'ДатаНачалаДосдачи',
			'time_ended_debtor' 	=> 'ДатаДосдачи',			
			'time_begin_debtor_2' 	=> 'ДатаНачалаДосдачи2',
			'time_ended_debtor_2' 	=> 'ДатаДосдачи2',
			'tutor'					=> 'Тьютор',
			'tutor_2' 				=> 'Тьютор2',			
		);
	}

}