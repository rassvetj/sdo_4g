<?php
class HM_StudentCertificate_StudentCertificateModel extends HM_Model_Abstract
{
	const SATUS_NEW      = 0;
    const SATUS_ACCEPTED = 1;  
	const SATUS_READY 	 = 2;	
	
	const TYPE_STUDY_COGNIZANCE	= 1;
	const TYPE_STUDY			= 2;
	const TYPE_GRANT 			= 3;
	const TYPE_RECORD_BOOK		= 4;
	const TYPE_DOC_EDU			= 5;
	const TYPE_VALIDATION		= 6;
	const TYPE_SOLDIER			= 7;
	const TYPE_LICENSE			= 8;
	const TYPE_OUT_OF_ORDER		= 9;
	const TYPE_SNILS			= 10;
	const TYPE_PHOTO			= 11;
	const TYPE_PASSPORT			= 12;
	const TYPE_GIA				= 13;
	const TYPE_TICKET			= 14;
	const TYPE_RECORD_BOOK_TRUE_COPY	= 15;
	
	const TYPE_GRANT_STATE_SOCIAL				= 16;
	const TYPE_GRANT_STATE_SOCIAL_INCREASED		= 17;			
	const TYPE_GRANT_STATE_ACADEMIC_INCREASED	= 18;
	const TYPE_MATERIAL_HELP					= 19;
	const TYPE_MILITARY_DOC						= 20;
	const TYPE_TRANSFER_CLAIM					= 21;
	const TYPE_ACADEMIC_LEAVE					= 22;
	const TYPE_TRANSFER					        = 23;
	const TYPE_EXPULSION				        = 24;
	
	const TRANSFER_TYPE_TO_SPECIALYTY                  = 1;
	const TRANSFER_TYPE_TO_STUDY_FORM                  = 2;
	const TRANSFER_TYPE_TO_PROGRAM                     = 3;
	const TRANSFER_TYPE_TO_EXTRAMURAL_WITH_DISTANCE    = 4;
	const TRANSFER_TYPE_FROM_EXTRAMURAL_WITH_DISTANCE  = 5;	
	const TRANSFER_TYPE_CHANGE_STUDY_FORM              = 6;
	const TRANSFER_TYPE_CHANGE_SPECIALITY              = 7;
	const TRANSFER_TYPE_CHANGE_FILIAL                  = 8;
	
	const EDUCATION_TYPE_SECONDARY_GENERAL    = 1; # среднего общего образования
	const EDUCATION_TYPE_SECONDARY_VOCATIONAL = 2; # среднего профессионального образования
	const EDUCATION_TYPE_HIGHER               = 3; # высшего образования
	
	const PROGRAM_SECONDARY_VOCATIONAL_EDUCATION = 1;
	const PROGRAM_BACHELOR                       = 2;
	const PROGRAM_MAGISTRACY                     = 3;
	const PROGRAM_SPECIALTY                      = 4;
	
	
	const STUDY_FORM_FULL_TIME                = 1;
	const STUDY_FORM_PART_TIME                = 2;
	const STUDY_FORM_EXTRAMURAL               = 3;
	const STUDY_FORM_EXTRAMURAL_WITH_DISTANCE = 4;
	
	const BASIS_LEARNING_BUDGET   = 1;
	const BASIS_LEARNING_CONTRACT = 2;
	
	const ACADEMIC_LEAVE_TYPE_FAMILY    = 1;
	const ACADEMIC_LEAVE_TYPE_MEDICAL   = 2;
	const ACADEMIC_LEAVE_TYPE_ARMY      = 3;
	const ACADEMIC_LEAVE_TYPE_PREGNANCY = 4;
	const ACADEMIC_LEAVE_TYPE_BABY_CARE = 5;
	
	const EXPULSION_TYPE_AT_WILL               = 1;
	const EXPULSION_TYPE_TO_ANOTHER_UNIVERSITY = 2;
	
	
	const SIGNATURE_TYPE_DIGITAL = 1;
	const SIGNATURE_TYPE_PAPER   = 2;
	
	const DELIVERY_METHOD_PORSONAL    = 1;
	const DELIVERY_METHOD_POST_OFFICE = 2;
	
	//const FORM_TYPE_ORDER		= 1; //--тип формы - заказать справку
	//const FORM_TYPE_SEND_DOC	= 2; //--тип формы - отправить документ на проверку.
    
    static public function getStatuses() {
        return array(
            self::SATUS_NEW      	=> _('Новая'),
            self::SATUS_ACCEPTED 	=> _('В работе'),
			self::SATUS_READY 		=> _('Готово'),              
        );
    }
	
	static public function getTypes() {
        return array(
			self::TYPE_STUDY_COGNIZANCE	=> _('Справка, подтверждающая обучение в РГСУ (с гербовой печатью)'),
			self::TYPE_STUDY			=> _('Справка, подтверждающая обучение в РГСУ'), 
			self::TYPE_GRANT 			=> _('Справка о выплатах/размере стипендии'),
			self::TYPE_GIA  			=> _('Справка-вызов на ГИА'),
			self::TYPE_RECORD_BOOK		=> _('Выписка из зачетной книжки (с оценками и часами)'),
			self::TYPE_DOC_EDU			=> _('Копия документа об образовании'),
			self::TYPE_VALIDATION		=> _('Справка-вызов на промежуточную аттестацию'),
			self::TYPE_SOLDIER			=> _('Справка для военкомата'),
			self::TYPE_LICENSE			=> _('Копия лицензии на ведение образовательной деятельности/копия свидетельства о государственной аккредитации'),
			self::TYPE_OUT_OF_ORDER		=> _('Выписка из приказа (о зачислении, отчислении, переводе)'),
			self::TYPE_SNILS			=> _('СНИЛС'),
			self::TYPE_PHOTO			=> _('Фотография'),
			self::TYPE_PASSPORT			=> _('Паспорт'),
			self::TYPE_TICKET			=> _('Студенческий билет'),			
			self::TYPE_RECORD_BOOK_TRUE_COPY	=> _('Заверенная копия зачетной книжки'),
			
			self::TYPE_GRANT_STATE_SOCIAL				=> _('Государственная социальная стипендия (ГСС)'),
			self::TYPE_GRANT_STATE_SOCIAL_INCREASED		=> _('Повышенная государственная социальная стипендия (ПГСС)'),			
			self::TYPE_GRANT_STATE_ACADEMIC_INCREASED	=> _('Повышенная государственная академическая стипендия (ПГАС)'),
			self::TYPE_MATERIAL_HELP					=> _('Материальная помощь (скан паспорта) (МП)'),
			
			self::TYPE_MILITARY_DOC		=> _('Документы для военно-учетного стола'),
			#self::TYPE_TRANSFER_CLAIM   => _('Оформить заявление на перевод'),
			self::TYPE_ACADEMIC_LEAVE   => _('Академический отпуск'),
			self::TYPE_TRANSFER         => _('Перевод'),
			self::TYPE_EXPULSION        => _('Отчисление'),
			
        );
    }
	
	static public function getExpulsionTypes()
	{
		return array(
			self::EXPULSION_TYPE_AT_WILL => _('по собственному желанию'),
			self::EXPULSION_TYPE_TO_ANOTHER_UNIVERSITY => _('в порядке перевода в другой вуз'),			
		);
	}
	
	static public function getExpulsionTypeName($type_id)
	{
		$types = self::getExpulsionTypes();
		return $types[$type_id];
	}
	
	
	static public function getEducationTypes()
	{
		return array(
			self::EDUCATION_TYPE_SECONDARY_GENERAL    => _('среднего общего образования'),
			self::EDUCATION_TYPE_SECONDARY_VOCATIONAL => _('среднего профессионального образования'),
			self:: EDUCATION_TYPE_HIGHER              => _('высшего образования'),
		);
	}
	
	static public function getEducationTypeName($type_id)
	{
		$types = self::getEducationTypes();
		return $types[$type_id];
	}
	
	
	static public function getTypesComments() {
        return array(
			self::TYPE_STUDY_COGNIZANCE	=> _('<p>Представляется только в РУСЗН, Пенсионный фонд, Налоговую инспекцию.</p><p>Заказанные документы можно получить в деканате после уведомления о готовности.</p>'),
			self::TYPE_STUDY			=> _('<p>Заказанные документы можно получить в деканате после уведомления о готовности.</p>'), 
			self::TYPE_GRANT 			=> _('<p>В графе «Период начисления стипендии» должен быть указан период, необходимый для отображения сведений о стипендии в справке (например, «за последние три месяца»/ «за последние полгода»/ определенные месяцы «сентябрь, октябрь, ноябрь» и т.п.).</p><p>Заказанные документы можно получить в деканате после уведомления о готовности.</p>'),
			self::TYPE_GIA 				=> _('<p>Заказанные документы можно получить в деканате после уведомления о готовности.</p>'),
			self::TYPE_RECORD_BOOK		=> _('<p>Заказанные документы можно получить в деканате после уведомления о готовности.</p>'),
			self::TYPE_DOC_EDU			=> _('<p>Заказанные документы можно получить в деканате после уведомления о готовности.</p>'),
			self::TYPE_VALIDATION		=> _('<p>Выдается, как правило, за 2 недели до начала сессии.</p><p>Заказанные документы можно получить в деканате после уведомления о готовности.</p>'),
			self::TYPE_SOLDIER			=> _('<p>В соответствии с действующим законодательством получение справок для военного комиссариата возможен только при личном присутствии обучающегося и предьявлении:  паспорта, «Удостоверения гражданина подлежащего призыву на военную службу» либо «Военного билета». Получить справку можно в централизованном деканате по месту обучения.</p>'),			
			self::TYPE_LICENSE			=> _('<p>Заказанные документы можно получить в деканате после уведомления о готовности.</p>'),
			self::TYPE_OUT_OF_ORDER		=> _('<p>Указать вид приказа, выписка из которого требуется.</p><p>Заказанные документы можно получить в деканате после уведомления о готовности.</p>'),
			self::TYPE_SNILS			=> _('<p>В случае наличия у Вас СНИЛС, Вам необходимо в срок до 30.06.2015 г. ввести его данные во вкладку «Страховой номер ПФР» в личном кабинете, и отправить отсканированную копию СНИЛС.</p><p>Если Вы еще не оформляли и не получали Страховое свидетельство Обязательного Пенсионного Страхования, то Вы можете:</p><p>1. самостоятельно обратиться в территориальный орган ПФР по месту Вашей регистрации (жительства), предъявить паспорт (или любое другое удостоверение личности) и заполнить «Анкету застрахованного лица (Форма АДВ)». Свидетельство оформляется в течение одной-двух недель,</p><p>ИЛИ</p><p>2. обратиться к работникам Централизованного деканата, которые помогут Вам заполнить «Анкету застрахованного лица (Форма АДВ)». При себе иметь паспорт, копию паспорта. В этом случае Вы получите СНИЛС по месту учебы.</p>'),
			self::TYPE_RECORD_BOOK_TRUE_COPY	=> _('<p>Заказанные документы можно получить в деканате после уведомления о готовности.</p>'),
			#self::TYPE_TRANSFER_CLAIM	=> _('<p>Необходимо скачать заявление, подписать заявление и приложить скан-копию.</p>'),
			self::TYPE_ACADEMIC_LEAVE	=> _('<p>Для обработки заявки необходимо прикрепить скан-копию подписанного собственноручно заявления и скан-копию документа, подтверждающего основание предоставления отпуска</p>'),
			self::TYPE_TRANSFER	        => _('<p>Для обработки заявки необходимо прикрепить скан-копию подписанного собственноручно заявления</p>'),
		);
    }
	
	static public function getDescription($type_id)
	{
		$comments = self::getTypesComments();
		return $comments[$type_id];
	}
	
	/**
	 * - распределение email по типам справок. В зависимости от типа отправляем разным адресатам
	*/
	static public function getEmailToList()
	{
		return array(
			self::TYPE_SNILS							=> 'skp_moskva@rgsu.net',
			self::TYPE_MILITARY_DOC						=> 'NovichkovaAV@rgsu.net',
			self::TYPE_GRANT_STATE_SOCIAL				=> 'stipendia@rgsu.net',
			self::TYPE_GRANT_STATE_SOCIAL_INCREASED		=> 'stipendia@rgsu.net',
			self::TYPE_GRANT_STATE_ACADEMIC_INCREASED	=> 'stipendia@rgsu.net',
			self::TYPE_MATERIAL_HELP					=> 'stipendia@rgsu.net',
		);
	}
	
	static public function getEmailTo($type)
	{
		$list = self::getEmailToList();
		return $list[$type];
	}
	
	static public function getEmailToCopyList() 
	{
		return array(			
			self::TYPE_MILITARY_DOC		=> 'IvolzhatovAV@rgsu.net​',
        );		
	}
	
	static public function getEmailToCopy($type)
	{
		$list = self::getEmailToCopyList();
		return $list[$type];
	}
	
	/**
	 * - указывает, какие блоки надо паказать. Не используется нигде вроде как. Проверить да удалить
	*/
	/*
	static public function getHideShowGroups() {
		return array(
			self::FORM_TYPE_ORDER		=> '11',
			self::FORM_TYPE_SEND_DOC	=> '22',
        );		
	}
	*/
	
	/**
	 * - Абревеатура факультета
	*/
	static public function getShortNameFacultet($key) {		
		$t = array(
				'ФАКУЛЬТЕТ ДИСТАНЦИОННОГО ОБУЧЕНИЯ' 							=> 'ДО',
				'ФАКУЛЬТЕТ ДИСТАНЦИОННОГО ОБУЧЕНИЯ_Б' 							=> 'ДО_Б',
				'СОЦИАЛЬНО-ГУМАНИТАРНЫЙ ФАКУЛЬТЕТ' 								=> 'СГФ',
				'ФАКУЛЬТЕТ ДОВУЗОВСКОГО ОБРАЗОВАНИЯ' 							=> 'ФДО',
				'ФАКУЛЬТЕТ ИСКУССТВ И СОЦИОКУЛЬТУРНОЙ ДЕЯТЕЛЬНОСТИ' 			=> 'ФИиСКД',
				'ФАКУЛЬТЕТ ИНФОРМАЦИОННЫХ ТЕХНОЛОГИЙ И ТЕХНОСФЕРНОЙ БЕЗОПАСНОСТИ' => 'ФИТиТБ',
				'ФАКУЛЬТЕТ ИНОСТРАННЫХ ЯЗЫКОВ' 									=> 'ФИЯ',
				'ФАКУЛЬТЕТ КОММУНИКАТИВНОГО МЕНЕДЖМЕНТА' 						=> 'ФКМ',
				'ФАКУЛЬТЕТ ПОДГОТОВКИ КАДРОВ ВЫСШЕЙ КВАЛИФИКАЦИИ' 				=> 'ФПКВК',
				'ФАКУЛЬТЕТ ПСИХОЛОГИИ, СОЦИАЛЬНОЙ МЕДИЦИНЫ И АДАПТАЦИОННО-РЕАБИЛИТАЦИОННЫХ ТЕХНОЛОГИЙ' => 'ФПСМАРТ',
				'ФАКУЛЬТЕТ СОЦИАЛЬНОЙ РАБОТЫ, ПЕДАГОГИКИ И ЮВЕНОЛОГИИ' 			=> 'ФСРПЮ',
				'ФАКУЛЬТЕТ СОЦИАЛЬНОГО СТРАХОВАНИЯ, ЭКОНОМИКИ И СОЦИОЛОГИИ ТРУДА' => 'ФССЭиСТ',
				'ФАКУЛЬТЕТ СОЦИАЛЬНОГО УПРАВЛЕНИЯ И СОЦИОЛОГИИ' 				=> 'ФСУС',
				'ФАКУЛЬТЕТ ЮРИСПРУДЕНЦИИ И ЮВЕНАЛЬНОЙ ЮСТИЦИИ' 					=> 'ФЮиЮЮ',
				'ФАКУЛЬТЕТ ОХРАНЫ ТРУДА И ОКРУЖАЮЩЕЙ СРЕДЫ' 					=> 'ФОТИОС', //--фейковое сокращение
		);
		
		if(isset($t[strtoupper ($key)])) {
			return $t[strtoupper ($key)];
		}
		
		return $key;
	}
	
	static public function getPrivilegeTypeList()
	{
		return array(
			8 		=> _('Дети-сироты'),			
			9 		=> _('Дети, оставшиеся без попечения родителей'),			
			12 		=> _('Инвалиды I группы'),
			13		=> _('Инвалиды II группы'),
			60 		=> _('Граждане, проходивших в течение не менее 3 лет воен. службу по контракту на воинских должностях, подлежащих замещению солдатами, матросами, сержантами'),
			61 		=> _('Получатели государственной социальной помощи'),
			62 		=> _('Лица, подвергшиеся воздействию радиации вследствие катастрофы на Чернобыльской АЭС и иных радиационных катастроф'),
			63 		=> _('Лица, подвергшиеся воздействию радиации вследствие ядерных испытаний на Семипалатинском полигоне'),
			64 		=> _('Лица, являющиеся инвалидами вследствие военной травмы или заболевания, полученных в период прохождения военной службы, и ветеранами боевых действий'),
			74 		=> _('Ребенок-инвалид'),
			1002 	=> _('Лица из числа детей-сирот и детей, оставшихся без попечения родителей'),
			1011 	=> _('Инвалиды войны, участники боевых действий, ветераны боевых действий'),
			1014 	=> _('Инвалиды с детства'),
			1501 	=> _('Лица, потерявшие в период обучения обоих родителей или единственного родителя'),
			#11	=> _('Дети-инвалиды'),
			#57 => _('Дети-инвалиды, инвалиды I и II групп'),
			#58 => _('Дети-сироты и дети, оставшиеся без попечения родителей, лица из числа детей-сирот и детей, оставшихся без попечения родителей'),
			#59 => _('Инвалиды с детства'),
			#67 => _('Дети умерших (погибших) Героев Советского Союза, Героев Россий-ской Федерации и полных кавалеров ордена Славы'),
			#68 => _('Дети сотрудников ОВД, учреждений и органов уголовно-исполнительной системы, ФПС ГПС, органов по контролю за оборотом наркотических средств'),
			#69 => _('Дети прокурорских работников, погибших (умерших) вследствие увечья или иного повреждения здоровья, полученных ими в период прохождения службы в органах'),
			#70 => _('Граждане, непосредственно принимавшие участие в испытаниях ядерного оружия, боевых радиоактивных веществ в атмосфере, ядерного  оружия под землей'),
			#71 => _('Военнослужащие, в том числе военнослужащие внутренних войск МВД РФ, сотрудники ОВД РФ, уголовно-исполнительной системы, ФПС ГПС, выполнявшие задачи'),
			#72 => _('Инвалиды войны'),
			#73 => _('Малоимущие граждане'),
		);
	}
	
	static function getSendDocTypes()
	{	
		$types = self::getTypes();
		return array(		
			self::TYPE_SNILS 						  => $types[self::TYPE_SNILS],
			self::TYPE_PHOTO 						  => $types[self::TYPE_PHOTO],
			self::TYPE_PASSPORT 					  => $types[self::TYPE_PASSPORT],
			self::TYPE_MILITARY_DOC 				  => $types[self::TYPE_MILITARY_DOC],
			#self::TYPE_GRANT_STATE_SOCIAL 			  => $types[self::TYPE_GRANT_STATE_SOCIAL],			
			#self::TYPE_GRANT_STATE_SOCIAL_INCREASED   => $types[self::TYPE_GRANT_STATE_SOCIAL_INCREASED],			
			#self::TYPE_GRANT_STATE_ACADEMIC_INCREASED => $types[self::TYPE_GRANT_STATE_ACADEMIC_INCREASED],
		);
	}
	
	static function getTypeName($type)
	{
		$types = self::getTypes();
		return $types[$type];
	}
	
	static function getPrivilegeTypeName($type)
	{
		$types = self::getPrivilegeTypeList();
		return $types[$type];
	}
	
	static function getCertificateTypes()
	{	
		$types = self::getTypes();
		return array(		
			self::TYPE_STUDY_COGNIZANCE      => $types[self::TYPE_STUDY_COGNIZANCE],
			self::TYPE_STUDY                 => $types[self::TYPE_STUDY],
			self::TYPE_GRANT                 => $types[self::TYPE_GRANT],
			self::TYPE_GIA                   => $types[self::TYPE_GIA],
			self::TYPE_DOC_EDU               => $types[self::TYPE_DOC_EDU],
			self::TYPE_VALIDATION            => $types[self::TYPE_VALIDATION],
			self::TYPE_SOLDIER               => $types[self::TYPE_SOLDIER],
			self::TYPE_LICENSE               => $types[self::TYPE_LICENSE],
			self::TYPE_OUT_OF_ORDER          => $types[self::TYPE_OUT_OF_ORDER],
			self::TYPE_RECORD_BOOK_TRUE_COPY => $types[self::TYPE_RECORD_BOOK_TRUE_COPY],
			#self::TYPE_MATERIAL_HELP         => $types[self::TYPE_MATERIAL_HELP],
			#self::TYPE_TRANSFER_CLAIM        => $types[self::TYPE_TRANSFER_CLAIM],
			self::TYPE_ACADEMIC_LEAVE        => $types[self::TYPE_ACADEMIC_LEAVE],
			self::TYPE_TRANSFER              => $types[self::TYPE_TRANSFER],
			self::TYPE_EXPULSION             => $types[self::TYPE_EXPULSION],
		);
	}
	
	static function getTransferTypes()
	{
		return array(
			#self::TRANSFER_TYPE_TO_SPECIALYTY                 => _('на другую специальность'),
			#self::TRANSFER_TYPE_TO_STUDY_FORM                 => _('на другую форму обучения'),
			#self::TRANSFER_TYPE_TO_PROGRAM                    => _('на другую программу обучения'),
			#self::TRANSFER_TYPE_TO_EXTRAMURAL_WITH_DISTANCE   => _('перевод на заочную форму с применением дистанционных технологий'),
			#self::TRANSFER_TYPE_FROM_EXTRAMURAL_WITH_DISTANCE => _('перевод с заочной формы с применением дистанционных технологий'),			
			self::TRANSFER_TYPE_CHANGE_STUDY_FORM             => _('смена формы обучения'),
			self::TRANSFER_TYPE_CHANGE_SPECIALITY             => _('смена специальности'),
			self::TRANSFER_TYPE_CHANGE_FILIAL                 => _('перевод в/из филиала РГСУ'),
		);
	}
	
	static function getTransferTypeById($id)
	{
		$list = self::getTransferTypes();
		return $list[$id];
	}
	
	static function getOrganizations()
	{
		return array(
			'РГСУ МОСКВА'      => _('РГСУ МОСКВА'),
			'Анапа'            => _('Анапа'),
			'Клин'             => _('Клин'),
			'Минск'            => _('Минск'),
			'Ош'               => _('Ош'),
			'Павловский Посад' => _('Павловский Посад'),
			'Пятигорск'        => _('Пятигорск'),
			'Сочи'             => _('Сочи'),
		);
	}
	
	static function getPrograms()
	{
		return array(
			#self::PROGRAM_SECONDARY_VOCATIONAL_EDUCATION => _('среднее профессионально образование'),
			self::PROGRAM_BACHELOR                       => _('бакалавриат'),
			self::PROGRAM_MAGISTRACY                     => _('магистратура'),
			self::PROGRAM_SPECIALTY                      => _('специалитет'),
		);
	}
	
	static function getProgramById($id)
	{
		$list = self::getPrograms();
		return $list[$id];
	}
	
	
	static function getFaculties()
	{
		return array(
			'Колледж'                                         => _('Колледж'),
			'факультет искусств'                              => _('факультет искусств'),
			'гуманитарный факультет'                          => _('гуманитарный факультет'),
			'факультет информационных технологий'             => _('факультет информационных технологий'),
			'факультет коммуникативного менеджмента'          => _('факультет коммуникативного менеджмента'),
			'медицинский факультет'                           => _('медицинский факультет'),
			'лингвистический факультет'                       => _('лингвистический факультет'),
			'факультет психологии'                            => _('факультет психологии'),
			'факультет социальной работы'                     => _('факультет социальной работы'),
			'факультет социологии'                            => _('факультет социологии'),
			'факультет управления'                            => _('факультет управления'),
			'факультет физической культуры'                   => _('факультет физической культуры'),
			'факультет экологии  и техносферной безопасности' => _('факультет экологии  и техносферной безопасности'),
			'экономический факультет'                         => _('экономический факультет'),
			'юридический факультет'                           => _('юридический факультет'),
		);
	}
	
	static function getStudyForms()
	{
		return array(
			self::STUDY_FORM_FULL_TIME                => _('очная'),
			self::STUDY_FORM_PART_TIME                => _('очно-заочная'),
			self::STUDY_FORM_EXTRAMURAL               => _('заочная'),
			self::STUDY_FORM_EXTRAMURAL_WITH_DISTANCE => _('заочная с применением дистанционных технологий'),			
		);	
	}
	
	static function getStudyFormById($id)
	{
		$list = self::getStudyForms();
		return $list[$id];
	}
	
	static function getBasisLearningList()
	{
		return array(
			self::BASIS_LEARNING_BUDGET   => _('бюджетная'),
			self::BASIS_LEARNING_CONTRACT => _('контрактная'),
		);
	}
	
	static function getBasisLearningById($id)
	{
		$list = self::getBasisLearningList();
		return $list[$id];
	}
	
	

	
	static function getAcademicLeaveTypes()
	{
		return array(
			self::ACADEMIC_LEAVE_TYPE_FAMILY    => _('по семейным обстоятельствам'),
			self::ACADEMIC_LEAVE_TYPE_MEDICAL   => _('по медицинским показаниям'),
			self::ACADEMIC_LEAVE_TYPE_ARMY      => _('в связи с призывом в ВС РФ'),
			self::ACADEMIC_LEAVE_TYPE_PREGNANCY => _('по беременности и родам'),
			self::ACADEMIC_LEAVE_TYPE_BABY_CARE => _('по уходу за ребенком'),
		);
	}
	
	static function getAcademicLeaveTypeName($id)
	{
		$list = self::getAcademicLeaveTypes();
		return $list[$id];
	}
	
	static function getAcademicLeaveDurations()
	{
		return array(
			self::ACADEMIC_LEAVE_TYPE_FAMILY    => _('1 год'),
			self::ACADEMIC_LEAVE_TYPE_MEDICAL   => _('1 год'),
			self::ACADEMIC_LEAVE_TYPE_ARMY      => _('1 год с даты убытия по повестке'),
			self::ACADEMIC_LEAVE_TYPE_PREGNANCY => '',
			self::ACADEMIC_LEAVE_TYPE_BABY_CARE => _('3 года'),
		);
	}
	
	static function getAcademicLeaveDuration($id)
	{
		$list = self::getAcademicLeaveDurations();
		return $list[$id];
	}
	
	static function getSignatureTypeName($id)
	{
		$list = self::getSignatureTypes();
		return $list[$id];
	}
	
	static function getSignatureTypes()
	{
		return array(
			self::SIGNATURE_TYPE_DIGITAL => _('Электронная подпись'),
			self::SIGNATURE_TYPE_PAPER   => _('Подлинная печать на оригинале'),	
		);
	}
	
	static function getDeliveryMethods()
	{
		return array(
			self::DELIVERY_METHOD_PORSONAL    => _('Лично при посещении'),
			self::DELIVERY_METHOD_POST_OFFICE => _('Почтой РФ'),	
		);
	}
	
	static function getDeliveryMethodName($id)
	{
		$list = self::getDeliveryMethods();
		return $list[$id];
	}
	
	
		
	
	
	
}