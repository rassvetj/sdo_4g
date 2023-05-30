<?php
class HM_Subject_Csv_CsvAdapter extends HM_Adapter_Csv_Abstract
{
    // Сколько первых строк будет пропущено
    protected $_skipLines = 1;

    public function getMappingArray()
    {
        /*
        ДисциплинаКод
        Код учебного предмета
        УчебныйПланИсточникКод
        Дисциплина
        Кафедра
        id Преподавателя
        РубежныйКонтрольВторойСеместр
        К изучению. З.Е.
        ВсегоЧасов
        АудиторныхЧасов
        СамостоятельнаяРаботаВторойСеместр
        ЛекцииВторойСеместр
        ЛабораторныеВторойСеместр
        ПрактическиеВторойСеместр
        КонтрольныеВторойСеместр
        ДатаНачала
        ДатаОкончания
		isDO - флаг, относящий сессию сессию к ФДО, ФДО_Б или всем остальным. 1\0
		begin_learning 
		group_external_id - id группы, на которую будет назначен препод в сессии
        language_code - код иностранного языка. По этому полю определяется, назначить ли студента на сессию или нет.
		module_code - идентификатор, ко которуму будут группироваться сессии при выгрузке результато вобучения.
		is_practice - пактика, 1 - да, 0 и пусто - нет
		*/
        
        return array(
            0  => 'external_id',
            1  => 'learning_subject_id_external',
            2  => 'programm_id_external',
            3  => 'name',
            4  => 'chair',
            5  => 'teacher_id_external',
            6  => 'exam_type',
            7  => 'learn',
            8  => 'hours_total',
            9  => 'classroom',
            10  => 'self_study',
            11 => 'lection',
            12 => 'lab',
            13 => 'practice',
            14 => 'exam',
            15 => 'begin',
            16 => 'end',
            17 => 'isDO',
			18 => 'begin_learning', # ДатаНачалаУчебногоПроцесса. Реализовано не тестовом. На рабочем этого поля еще нет. Но в 1С это последнее поле.
			19 => 'group_external_id',
			20 => 'language_code', #ИностранныйЯзык
			21 => 'module_code', #КодБлокаДисциплины
			22 => 'semester',
			
			23 => 'tutor_lector',
			24 => 'tutor_practic',
			25 => 'tutor_laboratory',			
			26 => 'faculty',			
			27 => 'is_practice',			
			28 => 'module_name',			
			29 => 'practice_begin',			
			30 => 'practice_end',			
        );			
    }
	
	public static function getColumnNameList(){
		return array(
			'external_id' 					=> 'ID_Сессии',
			'learning_subject_id_external' 	=> 'ДисциплинаКод',
			'programm_id_external' 			=> 'УчебныйПланИсточникКод',
			'name' 							=> 'Дисциплина',
			'chair' 						=> 'Кафедра',
			'teacher_id_external' 			=> 'idПреподавателя',
			'exam_type' 					=> 'РубежныйКонтрольВторойСеместр',
			'learn' 						=> 'ЗЕТ',
			'hours_total' 					=> 'ВсегоЧасов',
			'classroom' 					=> 'АудиторныхЧасов',
			'self_study' 					=> 'СамостоятельнаяРаботаВторойСеместр',
			'lection' 						=> 'ЛекцииВторойСеместр',
			'lab' 							=> 'ЛабораторныеВторойСеместр',
			'practice' 						=> 'ПрактическиеВторойСеместр',
			'exam' 							=> 'КонтрольныеВторойСеместр',
			'begin' 						=> 'ДатаНачала',
			'end' 							=> 'ДатаОкончания',
			'isDO' 							=> 'ДО',
			'begin_learning' 				=> 'ДатаНачалаУчебногоПроцесса',
			'group_external_id'				=> 'ID_Группы',
			'language_code' 				=> 'ИностранныйЯзык',
			'module_code' 					=> 'КодБлокаДисциплины',
			'semester' 						=> 'Семестр',
			'tutor_lector' 					=> 'id_лектор',
			'tutor_practic' 				=> 'id_семинарист',
			'tutor_laboratory' 				=> 'id_лаборант',
			'faculty' 						=> 'Факультет',
			'is_practice'					=> 'Практика',
			'module_name'					=> 'МодульНазвание',
			'practice_begin'				=> 'ПрактикаДатаНачала',
			'practice_end'					=> 'ПрактикаДатаОкончания',
		);
	}

}