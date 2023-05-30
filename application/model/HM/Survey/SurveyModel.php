<?php
class HM_Survey_SurveyModel extends HM_Model_Abstract
{
	const TYPE_FIELDS_PO  = 1; # Набор полей для типа Анкета студента выпускного курса ПО 
	const TYPE_FIELDS_DISABLED_PEOPLE  = 2; # Набор полей для типа Кабинет ОВЗ: анкетирование
	
	
	const TYPE_SPO  = 1;	#Анкета студента выпускного курса СПО РГСУ
	const TYPE_VPO  = 2;	#Анкета студента выпускного курса ВПО РГСУ
	const TYPE_DISABLED_PEOPLE  = 3;
	
	const TYPE_WHO_WORK	= 4; # Кем работать
	const TYPE_PROF_MOTIVE = 5; # Мотивы выбора
	const TYPE_PROF_FUTURE = 6; # ОПРЕДЕЛЕНИЕ ОПТИМАЛЬНОЙ СФЕРЫ БУДУЩЕЙ ПРОФЕССИОНАЛЬНОЙ ДЕЯТЕЛЬНОСТИ  
	const TYPE_EXPRESS_DIAG = 7; # Экспресс-диагностика способностей
	const TYPE_PROF_SELF 	= 8; # Профессионально-личностный тип
	const TYPE_VACCINATION  = 9; # вакцинация 

	
	const PMG_VIM = 'ВИМ';
	const PMG_VSM = 'ВСМ';
	const PMG_VPM = 'ВПМ';
	const PMG_VOM = 'ВОМ';
	
	const PF_PRODUCT   = 1; # Производственная сфера деятельности
	const PF_SOCIAL    = 2; # Социальная сфера деятельности 
	const PF_RESEARCH  = 3; # Исследовательская сфера деятельности
	const PF_HUMANITAR = 4; # Гуманитарная сфера деятельности
	
	# профессионаьные сферы деятельности 
	const PROF_1 = 1; # искусству
	const PROF_2 = 2; # технических способностей
	const PROF_3 = 3; # работы с людьми
	const PROF_4 = 4; # к научной деятельности
	const PROF_5 = 5; # физического труда
	const PROF_6 = 6; # предпринимательства
	
	
	# профессионаьно-личностные типы
	const PROF_SELF_1 = 1; # 
	const PROF_SELF_2 = 2; # 
	const PROF_SELF_3 = 3; # 
	const PROF_SELF_4 = 4; # 
	const PROF_SELF_5 = 5; # 
	const PROF_SELF_6 = 6; # 
	
	
	const STATUS_VACCINATION_1 = 1;
	const STATUS_VACCINATION_2 = 2;
	const STATUS_VACCINATION_3 = 3;
	const STATUS_VACCINATION_4 = 4;
	
	
	
	
	static function getProfFutureGroups(){
		return array(
			self::PF_PRODUCT   => 'Производственная',
			self::PF_SOCIAL    => 'Социальная',
			self::PF_RESEARCH  => 'Исследовательская',
			self::PF_HUMANITAR => 'Гуманитарная',
		);
	}

	static function getAllTypes(){
        return array(
					#self::TYPE_SPO				=> _('Анкета студента выпускного курса СПО'),
                    #self::TYPE_VPO 				=> _('Анкета студента выпускного курса ВПО'),
                    #self::TYPE_DISABLED_PEOPLE 	=> _('Анкета студента с ограниченными возможностями'),
                    self::TYPE_WHO_WORK 		=> _('Кем работать'),
                    self::TYPE_PROF_MOTIVE 		=> _('Мотивы выбора в профессиональном и личностном самоопределении'),
                    self::TYPE_PROF_FUTURE 		=> _('Определение оптимальной сферы будущей профессиональной деятельности'),
                    self::TYPE_EXPRESS_DIAG 	=> _('Экспресс-диагностика способностей'),
                    self::TYPE_PROF_SELF 		=> _('Определение профессионального личностного типа'),
               );
    }
	
	static function getProfMotiveGroups(){
		return array(
			self::PMG_VIM => array('pm2','pm4','pm8','pm18','pm21'), # ВИМ внутренние индивидуально значимые мотивы; 
			self::PMG_VSM => array('pm3','pm6','pm12','pm13','pm15'), # ВСМ внутренние социально значимые мотивы;
			self::PMG_VPM => array('pm9','pm10','pm14','pm17','pm20'), # ВПМ внешние положительные мотивы; 
			self::PMG_VOM => array('pm1','pm5','pm7','pm11','pm16','pm19'), # ВОМ внешние отрицательные мотивы
		);
	}
	
	static function getProfMotiveList(){
		return array(
			self::PMG_VIM => 'внутренние индивидуально значимые мотивы',
			self::PMG_VSM => 'внутренние социально значимые мотивы',
			self::PMG_VPM => 'внешние положительные мотивы',
			self::PMG_VOM => 'внешние отрицательные мотивы',
		);		
	}
	
	/**
	 * соотношение ответов к сферам деятельности 
	*/
	static function getExpressDiagGroups(){
		return array(
			self::PROF_1 => array('1a','5b','8a' ,'10a','11b','17a','21a','23a','24b','28a'),
			self::PROF_2 => array('1b','3b','6a' ,'8b' ,'12a','14a','15b','25a','26a','29b'),
			self::PROF_3 => array('2a','4a','6b' ,'9a' ,'12b','16a','17b','19b','23b','28b'),
			self::PROF_4 => array('4b','7a','10b','13a','14b','18a','20a','21b','26b','30a'),
			self::PROF_5 => array('2b','5a','13b','15a','18b','20b','22a','24a','25b','27a'),
			self::PROF_6 => array('3a','7b','9b' ,'11a','16b','19a','22b','27b','29a','30b'),
		);
	}
	
	
	/**
	 * Сетка распределения ответов по группам.
	*/
	static function getProfSelfGridAnswer(){
		return array(
			self::PROF_SELF_1 => array('1a','2a','3a' ,'4a' ,'5a' ,'16a','17a','18a','19a','21a','31a','32a','33a','34a'),
			self::PROF_SELF_2 => array('1b','6a','7a' ,'8a' ,'9a' ,'16b','20a','22a','23a','24a','31b','35a','36a','37a'),
			self::PROF_SELF_3 => array('2b','6b','10a','11a','12a','17b','20b','25a','26a','27a','36b','38a','39a','41b'),
			self::PROF_SELF_4 => array('3b','7b','10b','13a','14a','18b','22b','25b','28a','29a','32b','38b','40a','42a'),
			self::PROF_SELF_5 => array('4b','8b','11b','13b','15a','23b','26b','28b','30a','33b','35b','37b','39b','40b'),
			self::PROF_SELF_6 => array('5b','9b','12b','14b','15b','19b','21b','24b','27b','29b','30b','34b','41a','42b'),
			
		);
	}
	
	
	static function getProfSelfList(){
		return array(
			self::PROF_SELF_1 => 'Реалистический',
			self::PROF_SELF_2 => 'Интеллектуальный',
			self::PROF_SELF_3 => 'Социальный',
			self::PROF_SELF_4 => 'Конвенциальный',
			self::PROF_SELF_5 => 'Предприимчивый',
			self::PROF_SELF_6 => 'Артистический',
			
		);		
	}
	
	/**
	 * возвращает краткое описание результата по типу анкеты и свмме баллов
	*/
	static function getMessage($type_id, $summ){
		$res = self::getMessageDetails($type_id, $summ);
		return $res['message'];
	}
	
	/**
	 * возвращает код результата по типу анкеты и свмме баллов
	*/
	static function getCode($type_id, $summ){
		$res = self::getMessageDetails($type_id, $summ);
		return $res['code'];
	}
		
	/**
	 * возвращает имя шаблона с полням описанием результата по типу анкеты и свмме баллов
	*/
	static function getTpl($type_id, $summ){
		$res = self::getMessageDetails($type_id, $summ);
		return $res['tpl'];
	}
	
	
	
	
	
	static function getMessageDetails($type_id, $summ){
		#
		if($type_id == self::TYPE_WHO_WORK){
			#
			if(0 <= $summ && $summ <= 12){
				return array(
					'code'    => 'WHO_WORK_1',
					'message' => 'Вам совершенно неинтересно все, что предполагает работу с документами, знаками, цифрами, текстами, бумагами.',
					'tpl'	  => '_who_work_1',
				);
			}
			#
			if(13 <= $summ && $summ <= 24){
				return array(
					'code'    => 'WHO_WORK_2',
					'message' => 'Вам не особо интересно то, что предполагает работу с документами, знаками, цифрами, текстами, бумагами.',
					'tpl'	  => '_who_work_2',
				);
			}
			#
			if(25 <= $summ && $summ <= 36){
				return array(
					'code'    => 'WHO_WORK_3',
					'message' => 'Вы показали некоторый интерес к знаковым системам.',
					'tpl'	  => '_who_work_3',
				);
			}
			#
			if(37 <= $summ && $summ <= 48){
				return array(
					'code'    => 'WHO_WORK_4',
					'message' => 'Вы показали повышенный интерес к знаковым системам.',
					'tpl'	  => '_who_work_4',
				);
			}
			#
			if(49 <= $summ && $summ <= 60){
				return array(
					'code'    => 'WHO_WORK_5',
					'message' => 'Вы показали высокий интерес к знаковым системам.',
					'tpl'	  => '_who_work_5',
				);
			}			
		}
		#
		
		#
		if($type_id == self::TYPE_PROF_MOTIVE){
			return array(					
					'tpl'	  => '_prof_motive',
				);
			
			
		}
		#
		
		#
		if($type_id == self::TYPE_PROF_FUTURE){
			$answers  = $summ;
			$max 	  = MAX($answers);
			$group_id = array_search($max, $answers); # id группы с максимальным набором баллов
			$grouped  = array_count_values($answers);
			if($grouped[$max] == 1){ # нет значений, с равным кол-м баллов, преобладает кто-то один.				
				
				if($group_id == self::PF_PRODUCT){
					return array(
						'code'    => 'pf_1',
						'message' => 'Основная сфера деятельности, где Вы можете  максимально проявить свою эффективность - производственная (типы производственников, технологов, управленцев)',
						'tpl'	  => '_pf_1',
					);
				}
				
				if($group_id == self::PF_SOCIAL){
					return array(
						'code'    => 'pf_2',
						'message' => 'Основная сфера деятельности - социальная',
						'tpl'	  => '_pf_2',
					);
				}
				
				if($group_id == self::PF_RESEARCH){
					return array(
						'code'    => 'pf_3',
						'message' => 'Основная сфера деятельности - исследовательская',
						'tpl'	  => '_pf_3',
					);
				}
				
				if($group_id == self::PF_HUMANITAR){
					return array(
						'code'    => 'pf_3',
						'message' => 'Основная сфера деятельности - гуманитарная',
						'tpl'	  => '_pf_3',
					);
				}
			}

			if($group_id == self::PF_PRODUCT && $group_id == self::PF_SOCIAL){
				return array(
					'code'    => 'pf_4',
					'message' => 'Производственная и социальная сферы деятельности',
					'tpl'	  => '_pf_4',
				);				
			}
			
			if($group_id == self::PF_PRODUCT && $group_id == self::PF_RESEARCH){
				return array(
					'code'    => 'pf_5',
					'message' => 'Производственная и исследовательская сферы деятельности',
					'tpl'	  => '_pf_5',
				);				
			}
			
			
			# Если равны 2 параметра
			# 
			if($group_id == self::PF_PRODUCT && $group_id == self::PF_HUMANITAR){
				return array(
					'code'    => 'pf_6',
					'message' => 'Производственная и гуманитарная сферы деятельности',
					'tpl'	  => '_pf_6',
				);				
			}
			
			#
			if($group_id == self::PF_SOCIAL && $group_id == self::PF_RESEARCH){
				return array(
					'code'    => 'pf_7',
					'message' => 'Социальная и исследовательсая сферы деятельности',
					'tpl'	  => '_pf_7',
				);				
			}
			
			#
			if($group_id == self::PF_SOCIAL && $group_id == self::PF_HUMANITAR){
				return array(
					'code'    => 'pf_8',
					'message' => 'Социальная и гуманитарая сферы деятельности',
					'tpl'	  => '_pf_8',
				);				
			}
			
			#
			if($group_id == self::PF_RESEARCH && $group_id == self::PF_HUMANITAR){
				return array(
					'code'    => 'pf_9',
					'message' => 'Исследовательская и гуманитарая сферы деятельности',
					'tpl'	  => '_pf_9',
				);				
			}
			
			
			# Если разница в 1 Балл
			# а) и б)  
			if($group_id == self::PF_PRODUCT && $answers[self::PF_SOCIAL] == $answers[self::PF_PRODUCT]-1){
				return array(
					'code'    => 'pf_10',
					'message' => 'Основная производственная, дополнительная - социальная',
					'tpl'	  => '_pf_10',
				);				
			}
			
			# а) и в) 
			if($group_id == self::PF_PRODUCT && $answers[self::PF_RESEARCH] == $answers[self::PF_PRODUCT]-1){
				return array(
					'code'    => 'pf_11',
					'message' => 'Основная производственная, дополнительная - исследовательская',
					'tpl'	  => '_pf_11',
				);				
			}
			
			#а) и г) 
			if($group_id == self::PF_PRODUCT && $answers[self::PF_HUMANITAR] == $answers[self::PF_PRODUCT]-1){
				return array(
					'code'    => 'pf_12',
					'message' => 'Основная производственная, дополнительная - гуманитарная',
					'tpl'	  => '_pf_12',
				);				
			}
			
			# б) и в) 
			if($group_id == self::PF_SOCIAL && $answers[self::PF_RESEARCH] == $answers[self::PF_SOCIAL]-1){
				return array(
					'code'    => 'pf_13',
					'message' => 'Основная социальная, дополнительная - исследовательская',
					'tpl'	  => '_pf_13',
				);				
			}
			
			# б) и г) 
			if($group_id == self::PF_SOCIAL && $answers[self::PF_HUMANITAR] == $answers[self::PF_SOCIAL]-1){
				return array(
					'code'    => 'pf_14',
					'message' => 'Основная социальная, дополнительная - гуманитарная',
					'tpl'	  => '_pf_14',
				);				
			}
			
			# в) и г) 
			if($group_id == self::PF_RESEARCH && $answers[self::PF_HUMANITAR] == $answers[self::PF_RESEARCH]-1){
				return array(
					'code'    => 'pf_14',
					'message' => 'Основная исследовательская, дополнительная - гуманитарная',
					'tpl'	  => '_pf_14',
				);				
			}
			
			#pr($t);
			#pr($max);
			#pr($answers);
		
		}
		#	

		#
		if($type_id == self::TYPE_EXPRESS_DIAG){
			return array(					
					'tpl'	  => '_express_diag',
				);
		}
		#

		#
		if($type_id == self::TYPE_PROF_SELF){
			return array(					
					'tpl'	  => '_prof_self',
				);
		}
		#		
		
		
		
		
	}
	
	
	static public function getVaccinationStatuses()
	{
		return array(
			self::STATUS_VACCINATION_1 => _('Я согласен пройти вакцинацию против гриппа в 2020 году'),
			#self::STATUS_VACCINATION_2 => _('Я планирую пройти вакцинацию против гриппа в ином месте'),
			#self::STATUS_VACCINATION_3 => _('Я не планирую проходить вакцинацию против гриппа'),
			self::STATUS_VACCINATION_4 => _('Я уже прошел вакцинацию в 2020 году'),
		);
	}
	
	static public function getVaccinationStatusName($id)
	{
		$list = self::getVaccinationStatuses();
		return $list[$id];
	}
	
	static public function getEmailToByType($id)
	{
		$list = array(
			self::TYPE_VACCINATION => 'HramovSV@rgsu.net',
		);
		return $list[$id];
	}
	
	static public function getEmailSubjectByType($id)
	{
		$list = array(
			self::TYPE_VACCINATION => 'Вакцинация против гриппа',
		);
		return $list[$id];
	}

}