<?php
class HM_Subject_SubjectModel extends HM_Model_Abstract
{
	const FACULTY_DO = 1; // ФДО или ФДО_Б
	const FACULTY_OTHER = 0; // все осталные
	const FACULTY_DPO = 2; // ДПО
	
	// Тип обучения
    const TYPE_FULLTIME = 0; //Очное
    const TYPE_DISTANCE = 1; // Дистанционное

    // Базовый/ учебный
    const BASETYPE_PRACTICE = 0;
    const BASETYPE_BASE     = 1;
    const BASETYPE_SESSION  = 2;

    // Тип регистрации
    const REGTYPE_FREE  = 0; // deprecated
    const REGTYPE_MODER = 1; // самостоятельная регистация и назначение
    const REGTYPE_SAP   = 2; // только назначение

    /**
     * Диапазон дат
     * @const int PERIOD_DATES
     */
    const PERIOD_DATES = 0;
    /**
     * Без ограничений
     * @const int PERIOD_FREE
     */
    const PERIOD_FREE  = 1;

    /**
     * Фиксированная длительность
     * @const int PERIOD_FIXED
     */
    const PERIOD_FIXED = 2;


    // Режим просмотра
    const MODE_REGULATED = 0; // Регулярный
    const MODE_FREE    = 1; // Свободный

    // Доступные элементы для свободного просмотра
    const MODE_FREE_COURSES   = 1;
    const MODE_FREE_RESOURCES = 2;
    const MODE_FREE_TESTS     = 4;

    // статусы прохождения
    const MODE_FREE_ELEMENT_STATUS_INCOMPLETE = 0;
    const MODE_FREE_ELEMENT_STATUS_COMPLETE = 1;

    // Количество показываемых элементов в аккордеоне
    const MODE_FREE_ELEMENT_AMOUNT = 5;

    const STATUS_CLAIMANT = 0;
    const STATUS_STUDENT = 1;
    const STATUS_GRADUATED = 2;

    // Перевод в прошедшие обучение
    const MODE_SET_GRADUATE_STATUS_MODER = 0;
    const MODE_SET_GRADUATE_STATUS_AUTO  = 1;

    // Статус курсов
    const MSG_STATUS_ACTIVE	= 'Идёт';
    const MSG_STATUS_END	= 'Курс завершён';

    // Тип ограничения времени
    /**
     * Строгое ограничение
     * @const int PERIOD_RESTRICTION_STRICT
     */
    const PERIOD_RESTRICTION_STRICT = 0;

    /**
     * Нестрогое ограничение
     * @const int PERIOD_RESTRICTION_DECENT
     */
    const PERIOD_RESTRICTION_DECENT = 1;

    /**
     * Ручной старт
     * @const int PERIOD_RESTRICTION_MANUAL
     */
    const PERIOD_RESTRICTION_MANUAL = 2;

    // Агрегатные состояния курса
    const STATE_PENDING = 0;
    const STATE_ACTUAL = 1;
    const STATE_CLOSED = 2;

    //тип экзамена (Форма контроля)
    //нет, зачет, экзамен, диф. зачет.
    const EXAM_TYPE_NONE      = 0;
    const EXAM_TYPE_EXAM      = 1;
    const EXAM_TYPE_TEST      = 2;
    const EXAM_TYPE_TEST_MARK = 3;
    const EXAM_TYPE_INDEPENDENT_WORK = 4;
    const EXAM_TYPE_GIA 	  = 5;
	
	// временной промежуток сессий для отчета "обучение моих студентов"
	const SESSION_PRESENT 	= 1;
	const SESSION_PAST 		= 2;
	const SESSION_ALL 		= 3;
	
	# статус НЕ сдачи сессии: причины недопуска к экзамену.
	const FAIL_PASS_MIN_LANDMARK_COUNT	= 1;
	const FAIL_PASS_TOTAL_RAITING		= 2;
	const FAIL_PASS_TOTAL_PRACTIC		= 3;
	const FAIL_PASS_MIDDLE				= 4;
	const FAIL_PASS_MODULE_MIDDLE 	 = 5;  

	const OLD_SUBJECT_TO	=	'2019-09-01 23:59:59'; # Дата, до которой считаем сессию старой и назначать студентов без учета даты начала обучения 
	const USER_BEGIN_LEARNING_DATE_TO = '2021-08-31 00:00:00'; # Дата начала сессии, до которой "дату начала обучения" брать из студента. НЕ Включительно.
	
	const MAX_PERCENT_PRACTICE_MARK_CURRENT 	= 80;
	const MAX_PERCENT_PRACTICE_MARK_LANDMARK	= 20;
	
	const MAX_AUTOMATIC_MARK_CURRENT  = 80;
    const MAX_AUTOMATIC_MARK_LANDMARK = 20;
	
	# Максимальный балл за рубежный рейтинг
	const MAX_MARK_LANDMARK_DEFAULT = 20;
    
    protected $_primaryName = 'subid';
	
	static public function getOldSubjectToTimestamp()
	{
		return strtotime(self::OLD_SUBJECT_TO);
	}
	
	static public function getUserBeginLearningDateToTimestamp()
	{
		return strtotime(self::USER_BEGIN_LEARNING_DATE_TO);
	}
	
	static public function getOldDate()
	{
		return self::OLD_SUBJECT_TO;
	}
	
	static public function getOldDateFormatted()
	{
		return date('d.m.Y', strtotime(self::OLD_SUBJECT_TO));
	}
	
	

    static public function getExamTypes()
    {
        return array(
            self::EXAM_TYPE_NONE      => _('Нет'),
            self::EXAM_TYPE_EXAM      => _('Экзамен'),
            self::EXAM_TYPE_TEST      => _('Зачет'),
            self::EXAM_TYPE_TEST_MARK => _('Диф. зачет'),
            self::EXAM_TYPE_INDEPENDENT_WORK => _('Самостоятельная работа'),
            self::EXAM_TYPE_GIA 	  => _('ГИА'),
        );
    }
	
	static public function getExamTypeName($exam_type)
    {
        $types = self::getExamTypes();
		return $types[$exam_type];
    }
	
	
	 static public function getFacultys()
    {
        return array(		
            self::FACULTY_DO      => _('ДО'),
            self::FACULTY_OTHER   => _('не ДО'),
            self::FACULTY_DPO     => _('ДПО'),            
        );
    }

    static public function getZetValues()
    {
        $values = array();
        for($i = 1; $i<=12; $i++){
            $values[$i] = $i;
        }
        return $values;
    }
	
	static public function getSemesterList(){
		$values = array();
        for($i = 0; $i<=10; $i++){
            $values[$i] = $i;
        }
        return $values;
	}
    
    
    static public function getLearningStatuses()
    {
        return array(
            self::STATUS_CLAIMANT  => _('Заявка подана'),
            self::STATUS_STUDENT   => _('В процессе'),
            self::STATUS_GRADUATED => _('Пройден')
        );
    }

    /**
     * Return type of subject
     *
     * @return int
     */
    public function getBaseType()
    {
        return $this->base;
    }

    // только для учебных курсов, содержащих сессии
    public static function getTrainingProcessIds()
    {
        return array(6);

    }

    // для остальных уч.курсов и сессий
    public static function getSessionProcessIds()
    {
        return array(5);

    }



    /**
     * Return True if there is a base subject
     * @return bool
     */
    public function isBase()
    {
        if($this->base == self::BASETYPE_BASE)
        {
            return true;
        }
    }

    /*public function isSession()
    {
        if($this->base == self::BASETYPE_SESSION)
        {
            return true;
        }
    } */


    static public function getPeriodTypes()
    {
        return array(
            self::PERIOD_FREE  => _('Без ограничений'),
            self::PERIOD_DATES => _('Диапазон дат'),
            self::PERIOD_FIXED => _('Фиксированная длительность')
        );
    }

    public function getPeriod()
    {
        $periods = self::getPeriodTypes();
        return $periods[$this->period];
    }

    public function getLongtime()
    {
        return sprintf(_('%s дней'), $this->longtime);
    }

    public function getPriceWithCurrency()
    {
        return ($this->price)? number_format($this->price, 2, '.', ' ') . ' ' . $this->price_currency : '' ;
    }

    public function getClassifierLinks()
    {
        if (isset($this->classifierlinks)) {
            return $this->classifierlinks;
        }
        return new HM_Collection();
    }

    public function isClassified($classifierId)
    {
        foreach($this->getClassifiers() as $classifier) {
            if ($classifier->classifier_id == $classifierId) {
                return true;
            }
        }
        return false;
    }

    public function getLessons()
    {

        $result = array();
        if (isset($this->lessons))
        {
            $result = $this->lessons;
        }
        return $result;
    }

    public function getStudents()
    {

        $result = array();
        if (isset($this->students))
        {
            $result = $this->students;
        }
        return $result;
    }

    public function getClaimants()
    {

        $result = array();
        if (isset($this->claimants))
        {
            $result = $this->claimants;
        }
        return $result;
    }

    public function getGraduated()
    {

        $result = array();
        if (isset($this->graduated))
        {
            $result = $this->graduated;
        }
        return $result;
    }

    public function getTeachers()
    {
        $result = array();
        if (isset($this->teachers))
        {
            $result = $this->teachers;
        }

        return $result;

    }

    public function isStudent($studentId)
    {

        $students = $this->getStudents();
        if (count($students))
        {
            foreach ( $students as $student )
            {
                if ($studentId == $student->MID)
                    return true;
            }
        }
        return false;

    }

    public function isClaimant($studentId)
    {

        $claimants = $this->getClaimants();
        if (count($claimants))
        {
            foreach ( $claimants as $claimant )
            {
                if (($claimant->MID == $studentId) && ($claimant->status == HM_Role_ClaimantModel::STATUS_NEW)) // чтоб можно было подавать повторно
                    return true;
            }
        }
        return false;
    }

    public function isGraduated($studentId)
    {

        $graduated = $this->getGraduated();
        if (count($graduated))
        {
            foreach ( $graduated as $user )
            {
                if ($user->MID == $studentId)
                    return true;
            }
        }
        return false;
    }

    static public function getTypes()
    {

        return array(
            self::TYPE_FULLTIME => _('Очный'),
            self::TYPE_DISTANCE => _('Дистанционный'));

    }

    public function getType($type  = null)
    {

        $types = $this->getTypes();
        if($type==NULL){
            $type= $this->type;
        }

        return $types[$type];
    }

    static public function getRegTypes()
    {

        return array(
            //self::REGTYPE_FREE  => _('Открытая'),
            self::REGTYPE_MODER => _('Подача заявки или назначение'),
            self::REGTYPE_SAP   => _('Только назначение')
        );
    }

    public function getRegType($regtype = null)
    {

        $regtypes = $this->getRegTypes();


        if($regtype==NULL){
            $regtype= $this->reg_type;
        }


        return $regtypes[$regtype];
    }

    public function getCourses()
    {
        if (isset($this->courses)) {
            return $this->courses;
        }
        return array();
    }

    public function isCourseExists($courseId)
    {
        $courses = $this->getCourses();
        if (count($courses)) {
            foreach($courses as $course) {
                if (isset($course->course_id)) { // Это нужно для проверки в подгрузке только назначений
                    if ($course->course_id == $courseId) return true;
                }
                if (isset($course->CID)) {
                    if ($course->CID == $courseId) return true;
                }
            }

        }
        return false;
    }

    public function getEventTypes()
    {
        $types = HM_Event_EventModel::getTypes();
        if ($this->services) {
            foreach(HM_Activity_ActivityModel::getEventActivities() as $id => $name) {
                if (($this->services & $id) || in_array($id, HM_Activity_ActivityModel::getFreeEventActivities())) {
                    $types[$id] = $name;
                }
            }
        } else {
            foreach(array_intersect_key(HM_Activity_ActivityModel::getEventActivities(), array_flip(HM_Activity_ActivityModel::getFreeEventActivities())) as $id => $name) {
                $types[$id] = $name;
            }
        }

        // Добавляем Custom Events
        $events = Zend_Registry::get('serviceContainer')->getService('Event')->fetchAll(null, 'title');
        if (count($events)) {
            $types[999] = '---';
            foreach($events as $event) {
                $types[-$event->event_id] = $event->title;
            }

        }

        return $types;
    }

    public function getBegin()
    {
        return (strtotime($this->begin)) ? $this->date($this->begin) : '';
    }

    public function getEnd()
    {
        return (strtotime($this->end)) ? $this->date($this->end) : '';
    }

    /**
     * Дата начала курса для студента.
     * 
     * В зависимости от типа ограничения прохождения курса по времени
     * возвращается актуальная дата начала курса.
     */
    public function getBeginForStudent()
    {
        switch($this->period){
            case self::PERIOD_FIXED:
                // Относительно зачисления студента на курс
                $studentCourseData = Zend_Registry::get('serviceContainer')->getService('Student')->getOne(
			Zend_Registry::get('serviceContainer')->getService('Student')->fetchAll(
				array(
					'CID = ?' => $this->subid,
					'MID = ?' => Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserId()
				)
			)
		);
                $beginDate = $studentCourseData->time_registered;
                break;
            case self::PERIOD_DATES:
                // Время действия самого курса
                $beginDate = $this->getBegin();
                break;
            case self::PERIOD_FREE:
                // Без ограничений
            default:
                $beginDate = '';
    }
        return (strtotime($beginDate)) ? $this->date($beginDate) : '';
    }

    /**
     * Дата окончания курса для студента.
     * 
     * В зависимости от типа ограничения прохождения курса по времени
     * возвращается актуальная дата окончания курса.
     */
    public function getEndForStudent()
    {
        switch($this->period){
            case self::PERIOD_FIXED:
                // Относительно зачисления студента на курс
                $studentCourseData = Zend_Registry::get('serviceContainer')->getService('Student')->getOne(
			Zend_Registry::get('serviceContainer')->getService('Student')->fetchAll(
				array(
					'CID = ?' => $this->subid,
					'MID = ?' => Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserId()
				)
			)
		);
                $getEnd = $studentCourseData->time_ended_planned;
                break;
            case self::PERIOD_DATES:
                // Время действия самого курса
                $getEnd = $this->getEnd();
                break;
            case self::PERIOD_FREE:
                // Без ограничений
            default:
                $getEnd = '';
    }
        return (strtotime($getEnd)) ? $this->date($getEnd) : '';
    }

    /**
     * Сообщение о статусе курса
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->isExpired() ? _(self::MSG_STATUS_END) : _(self::MSG_STATUS_ACTIVE);
    }

    /**
     * Истёк ли срок курса ?
     *
     * @return boolean
     */
    public function isExpired()
    {
        switch ($this->period) {
        	case self::PERIOD_FREE:
        	case self::PERIOD_FIXED: // невозможно определить, считаем что нет
        		return false;
        	case self::PERIOD_DATES:
        		if ($this->period_restriction_type == self::PERIOD_RESTRICTION_STRICT) {
        		return time() > strtotime($this->end);
        		} elseif ($this->period_restriction_type == self::PERIOD_RESTRICTION_MANUAL) {
        		    if (Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_ENDUSER)) {
                        return $this->state != self::STATE_ACTUAL;
        }
        		}
        return false;
    		break;
    }
        return false;
    }

    public function getName(){
        $request = Zend_Controller_Front::getInstance()->getRequest();
		$lng = $request->getCookie(HM_User_UserService::COOKIE_NAME_LANG);
		
        if($lng = 'eng' && $this->name_translation != '') 
			return $this->name_translation;	
		else 
			return $this->name;
    }

    public function getIcon()
    {
        if ($icon = $this->getUserIcon()) {
            return $icon;
        } else {
            return $this->getDefaultIcon();
        }
    }
    public function getUserIcon() {
        $path = rtrim(Zend_Registry::get('config')->path->upload->subject, '/') . '/' . $this->subid . '.jpg';
        if(is_file($path)){
            return Zend_Registry::get('config')->url->base.'upload/subject-icons/' . $this->subid . '.jpg';
        }
        return null;
    }
    public function getDefaultIcon() {
        if($this->type == self::TYPE_DISTANCE){
            return ($this->isSession()) ?
                Zend_Registry::get('config')->url->base.'images/subject-icons/distance-session.png' :
                Zend_Registry::get('config')->url->base.'images/subject-icons/distance.png';
        }else{
            return ($this->isSession()) ?
                Zend_Registry::get('config')->url->base.'images/subject-icons/fulltime-session.png' :
                Zend_Registry::get('config')->url->base.'images/subject-icons/fulltime.png';
        }
    }

    public static function getIconFolder($subjectId = 0)
    {

        $folder = Zend_Registry::get('config')->path->upload->subject;

        $maxFilesPerFolder = Zend_Registry::get('config')->path->upload->maxfilescount;

        $folder = $folder . floor($subjectId / $maxFilesPerFolder) . '/';

        if(!is_dir($folder)){
            mkdir($folder, 0774);
            chmod($folder, 0774);
        }
        return floor($subjectId / $maxFilesPerFolder);
    }


    public static function getModes(){
        return array(
            self::MODE_FREE      => _('Свободный'),
            self::MODE_REGULATED => _('С планом занятий')
        );
    }


    public function getModeName(){
        $modes = self::getModes();
        return $modes[$this->access_mode];
    }

    public function getModeSwitcher()
    {
        $container = Zend_Registry::get('serviceContainer');
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $mca = $request->getModuleName() . ':' .
               $request->getControllerName() . ':' .
               $request->getActionName();

        if (  !in_array($container->getService('User')->getCurrentUserRole(),
                       array(HM_Role_RoleModelAbstract::ROLE_TEACHER,
                             HM_Role_RoleModelAbstract::ROLE_DEAN)) ||
              !in_array($mca, array('subject:index:card'))) {
            return $this->getModeName();
        }

        $modes  = self::getModes();
        $select = new Zend_Form_Element_Select('subjectsetmode_new_mode',
                                               array('multiOptions'=> $modes,
                                                     'value'       => $this->access_mode));
        $select->removeDecorator('Label')
               ->removeDecorator('HtmlTag');

        return $select->render();
    }

    public function getStateSwitcher()
    {
        $container = Zend_Registry::get('serviceContainer');
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $mca = $request->getModuleName() . ':' .
               $request->getControllerName() . ':' .
               $request->getActionName();

        if (  !in_array($container->getService('User')->getCurrentUserRole(),
                       array(HM_Role_RoleModelAbstract::ROLE_TEACHER,
                             HM_Role_RoleModelAbstract::ROLE_DEAN)) ||
              !in_array($mca, array('subject:index:card'))) {
            return self::getStateTitle($this->state);
        }

        $states  = self::getAvailableStates();
        $select = new Zend_Form_Element_Select('subjectsetstate_new_mode',
                                               array('multiOptions'=> $states,
                                                     'value'       => $this->state));
        $select->removeDecorator('Label')
               ->removeDecorator('HtmlTag');

        return $select->render();
    }

    public function getFreeAccessElements(){
        return array(
            self::MODE_FREE_COURSES   => _('Свободный доступ к учебным модулям'),
            self::MODE_FREE_RESOURCES => _('Свободный доступ к информационным ресурсам'),
            self::MODE_FREE_TESTS     => _('Свободный доступ к тестам')
        );

    }

    public function isSession()
    {
        return ($this->base == self::BASETYPE_SESSION);
    }

    public function getBaseTypeTitle()
    {
        if ($this->isSession()) {
            return _('Учебная сессия');
        }

        return _('Учебный курс');
    }

    public function getColorField()
    {
        if (!$this->base_color) return _('по умолчанию');
        return '<div class="color_field" style="background-color: #' . $this->base_color . '"></div>';
    }

    static public function getPeriodRestrictionTypes()
    {
        return array(
            self::PERIOD_RESTRICTION_STRICT=> _('Строгое ограничение'),
            self::PERIOD_RESTRICTION_DECENT   => _('Нестрогое ограничение'),
            self::PERIOD_RESTRICTION_MANUAL   => _('Начало и окончание обучения только по факту подтверждения преподавателем'),
        );
    }

    public function getPeriodRestrictionType($type  = null)
    {

        $types = $this->getPeriodTypes();
        if($type==NULL){
            $type= $this->type;
        }

        return $types[$type];
    }

    public function isAccessible()
    {
        if ($this->period == self::PERIOD_DATES) {
    	    $now = time();
            switch ($this->period_restriction_type) {
            	case self::PERIOD_RESTRICTION_DECENT:
            		return true;
            		break;
            	case self::PERIOD_RESTRICTION_STRICT:
            		return ($now < strtotime($this->end)) && ($now > strtotime($this->begin));
            		break;
            	case self::PERIOD_RESTRICTION_MANUAL:
            	    return ($this->state == self::STATE_ACTUAL);
            		break;
            }
        }
        return true;
    }

    static public function getStates()
    {
        return array(
            self::STATE_PENDING  => _('Не начато'),
            self::STATE_ACTUAL   => _('Идёт'),
            self::STATE_CLOSED => _('Закончено'),
        );
    }

    static public function getStateTitle($state)
    {
        $states = self::getStates();
        return $states[$state];
    }

    public function getAvailableStates()
    {
        if ($this->state == self::STATE_PENDING) {
            return array(
                self::STATE_PENDING  => self::getStateTitle(self::STATE_PENDING),
                self::STATE_ACTUAL   => self::getStateTitle(self::STATE_ACTUAL)
            );
        } elseif ($this->state == self::STATE_ACTUAL) {
            return array(
                self::STATE_ACTUAL => self::getStateTitle(self::STATE_ACTUAL),
                self::STATE_CLOSED => self::getStateTitle(self::STATE_CLOSED)
            );
        }
        return array(
            self::STATE_CLOSED => self::getStateTitle(self::STATE_CLOSED)
        );
    }

    public function isStateAllowed($state)
    {
        switch ($this->state) {
        	case self::STATE_PENDING:
        		return $state == self::STATE_ACTUAL;
        		break;
        	case self::STATE_ACTUAL:
        		return $state == self::STATE_CLOSED;
        		break;
        }
        return false;
    }

    public function getGraduatedMsg()
    {
        return _('Курс завершён');
    }

    public function getScale()
    {
        return $this->scale_id ? $this->scale_id : HM_Scale_ScaleModel::TYPE_CONTINUOUS; // default
    }


    public function getDefaultUri()
    {
        if (!empty($this->default_uri) && Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_ENDUSER)) {
    
            // dirty hack
            $uri = str_replace(array(
                    'lesson/list/index',
            ), array(
                    'lesson/list/my',
            ), $this->default_uri);
    
            return $uri;
    
        } else {
            $view = Zend_Registry::get('view');
            return $view->url(array('module' => 'subject', 'controller' => 'index', 'action' => 'card', 'subject_id' => $this->subid));
        }
    }    

    public function getMarkType()
    {
        return HM_Mark_StrategyFactory::getType($this->mark_type);
    }
	
	
	static public function getPeriodsTime()
    {
        return array(
            self::SESSION_PRESENT	=> _('Текущие'),            
            self::SESSION_PAST      => _('Прошедшие'),            
            self::SESSION_ALL      	=> _('Все'),            
        );
    }

	# Причины недопуска к экзамену
	static public function getFailPassMessageList()
    {
        return array(
            self::FAIL_PASS_MIN_LANDMARK_COUNT	=> _('Не сдан один из рубежных контролей на положительную оценку (набрано менее 65% от общего числа рубежных контролей)'),
			self::FAIL_PASS_TOTAL_RAITING		=> _('Не сдан один из рубежных контролей на положительную оценку (набрано менее 65% от максимальной оценки за рубежный контроль)'),
			self::FAIL_PASS_TOTAL_PRACTIC		=> _('Не сдан ИПЗ (итоговое практическое задание)'),
			self::FAIL_PASS_MIDDLE      		=> _('Итоговый текущий рейтинг менее 65% (менее 52 баллов)'),
            self::FAIL_PASS_MODULE_MIDDLE 	 	=> _('Итоговый текущий рейтинг доп. модуля менее 65% (менее 52 баллов)'),            
        );
    }
	
	# Текстовые сообщения для тьютора в ведомости
	static public function getFailPassMessageListForTutor()
    {
        return array(
            self::FAIL_PASS_MIN_LANDMARK_COUNT	=> _('всего РК < 65%'),
			self::FAIL_PASS_TOTAL_RAITING		=> _('РК < 65%'),
            self::FAIL_PASS_TOTAL_PRACTIC   	=> _('ИПЗ = 0'),
            self::FAIL_PASS_MIDDLE      		=> _('&sum; < 52'),
            self::FAIL_PASS_MODULE_MIDDLE    	=> _('модул. &sum; < 52'),            
        );
    }
	
	public function isPractice()
	{
		return empty($this->is_practice) ? false : true;
	}
	
	# старый вариант практик, если больше 20 баллов из 100
	public function isClassicPractice()
	{
		return ( $this->isPractice() && $this->mark_landmark > self::MAX_MARK_LANDMARK_DEFAULT )
				? true : false;
	}
	
	# сессия по распределению языков
	# 1 - английский
	public function isLanguageLeveling()
	{
		return $this->language_code == 1 ? true : false;
	}
	
	public function isLanguage()
	{
		return empty($this->language_code) ? false : true;
	}
	
	public function getPracticePeriod()
	{
		$result = '';
		$timestamp_begin	= strtotime($this->practice_begin);
		$timestamp_end		= strtotime($this->practice_end);
		
		$result .= $timestamp_begin > 0 ? date('d.m.Y', $timestamp_begin) : '';
		$result .= ' - ';
		$result .= $timestamp_end   > 0 ? date('d.m.Y', $timestamp_end)   : '';
		
		$result = trim($result, ' -');
		
		return $result;
	}
	
	public function isModule()
	{
		return empty($this->module_code) ? false : true;
	}
	
	public function getModuleName()
	{
		return empty($this->module_name) ? false : $this->module_name;
	}
	
	public function getDateDebt()
    {
        return (strtotime($this->time_ended_debt)) ? $this->date($this->time_ended_debt) : '';
    }
	
	public function getDateDebt2()
    {
        return (strtotime($this->time_ended_debt_2)) ? $this->date($this->time_ended_debt_2) : '';
    }
	
	public function getBeginLearning()
    {
        return (strtotime($this->begin_learning)) ? $this->date($this->begin_learning) : '';
    }
	
	# старя сессия
	public function isOld()
	{
		return strtotime($this->date_created) <= strtotime(self::getOldDate()) ? true : false;		
	}
	
	public function getDateCreated()
	{
		return (strtotime($this->date_created)) ? $this->date($this->date_created) : '';
	}

	public function isDOT()
	{
		return empty($this->isDO) ? false : true;	
	}
	
	public function isWithoutHours()
	{
		return ( empty($this->lection) && empty($this->lab) && empty($this->practice) )
               ? true : false;
	}
	
	public function getTypeModel()
	{
		if($this->isDOT()){ 
			return new HM_Subject_Type_Dot();
		}
			
		if($this->isClassicPractice()){
			return new HM_Subject_Type_ClassicPractice();
		}
		
		if($this->isPractice()){
			return new HM_Subject_Type_Practice();
		}
		
		if($this->isWithoutHours()){
			return new HM_Subject_Type_WithoutHours();
		}
		return new HM_Subject_Type_Default();
	}
	
	public function getDisciplineCode()
	{
		return substr($this->learning_subject_id_external, 5, 6);
	}
	
	# Дата окончания обучения, не позднее
	public function getEstimatedDateEnd()
	{
		$end_timestamp = strtotime($this->end);
		if($end_timestamp <= 0){
			return false;
		}
		
		if(!$this->isDOT()){
			return date('d.m.Y', $end_timestamp);
		}
		
		$dt = new DateTime();
		$dt->setTimestamp($end_timestamp);
		$dt->sub(new DateInterval('P5D'));			
		
		return $dt->format('d.m.Y');
	}
	
	# Дата приема рубежного контроля. Если это воскресенье, то сдвигаем на субботу.
	public function getEstimatedDateLandmarkControl()
	{
		$end_timestamp = strtotime($this->end);
		if($end_timestamp <= 0){
			return false;
		}
		
		if(!$this->isDOT()){
			return date('d.m.Y', $end_timestamp);
		}
		
		$dt = new DateTime();
		$dt->setTimestamp($end_timestamp);
		if($dt->format('w') == 0){ $dt->sub(new DateInterval('P1D')); } # воскресенье -1 день
		return $dt->format('d.m.Y');
	}

    public static function normalizMarkCurrent($markCurrent, $maxMarkLandmark, $markLandmark = false)
    {
        $ball               = ($markCurrent + $markLandmark);
		$normalMarkLandmark = self::normalizMarkLandmark($markLandmark, $maxMarkLandmark);
		return ($ball - $normalMarkLandmark);
		#$maxMarkCurrent        = 100 - $maxMarkLandmark;
        #$defaultMaxMarkCurrent = 100 - self::MAX_MARK_LANDMARK_DEFAULT;
        #return ($markCurrent/$maxMarkCurrent)*$defaultMaxMarkCurrent;
    }

    public static function normalizMarkLandmark($markLandmark, $maxMarkLandmark)
    {
        $defaultMaxMarkLandmark = self::MAX_MARK_LANDMARK_DEFAULT;
        return ($markLandmark/$maxMarkLandmark)*$defaultMaxMarkLandmark;
    }
	
}

