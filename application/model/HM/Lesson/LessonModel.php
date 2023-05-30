<?php

/**
 * @property int $SHEID ID занятия
 * @property string $title
 * @property int $CID ID курса
 * @property int $typeID ID типа занятия см. @link HM_Activity_ActivityModel
 * @property int $vedomost
 * @property int $teacher
 * @property int $moderator
 * @property int $createID
 * @property int $recommend
 * @property int $all
 * @property int $cond_sheid
 * @property int $cond_mark
 * @property int $cond_progress
 * @property int $cond_avgbal
 * @property int $cond_sumbal
 * @property int $gid
 * @property int $notice
 * @property int $notice_days
 * @property int $activities
 * @property int $descript
 * @property int $tool
 * @property int $startday
 * @property int $stopday
 * @property string $begin
 * @property string $end
 * @property int $timetype
 */

abstract class HM_Lesson_LessonModel extends HM_Model_Abstract implements HM_Lesson_LessonModel_Interface
{
    const MODE_PLAN = 0;
    const MODE_FREE = 1;
    const MODE_FREE_BLOCKED = 2; 
    
    const TIMETYPE_DATES      = 0;
    const TIMETYPE_RELATIVE   = 1;
    const TIMETYPE_TIMES      = 3;
    const TIMETYPE_FREE       = 2;

    const CONDITION_NONE      = 0;
    const CONDITION_PROGRESS  = 1;
    const CONDITION_AVGBAL    = 2;
    const CONDITION_SUMBAL    = 3;
    const CONDITION_LESSON    = 4;

    const ICON_LARGE    = 105;
    const ICON_MEDIUM   = 64;

    const DATE_UNLIMITED = 'unlimited';
    
    const SORT_ORDER_DEFAULT = 0;
    const SORT_ORDER_OVERDUE = 1;
    const SORT_ORDER_FREE = 2;
	

	const MAX_BALL_TOTAL_CURRENT_RATING = 80; # "итоговый текущий рейтинг"

	const PASS_TOTAL_RATING_PERCENT 	= 0.65; # 65% - положительный прходной рубеж для "Рубежного рейтинга"
	const PASS_MEDIUM_RATING_PERCENT 	= 0.65; # 65% - положительный прходной рубеж для "Итогового текущего рейтинга"
		
	const PASS_MODULE_PERCENT 		= 0.65; # 65% - положительный прходной рубеж для сдачи модульной сессии
	const MODULE_MAX_MEDIUM_BALL 	= 80; # максимальный балл в модульной сессии за "Итоговый текущий рейтинг"

	const PASS_LESSON_PERCENT = 0.65; # 65% - процент успешного прохождегия уолка по которому не надо сбрасывать попытки при продлении


    protected $_sortOrder;

    protected $_primaryName = 'SHEID';

    public $material;

    static public function getDateTypes(){

        return array(
            self::TIMETYPE_FREE      => _('Без ограничений'),
            self::TIMETYPE_DATES     => _('Диапазон дат'),
            self::TIMETYPE_TIMES     => _('Диапазон времени'),
            self::TIMETYPE_RELATIVE  => _('Относительный диапазон')
        );

    }

    static public function getConditionTypes(){

        return array(
            self::CONDITION_NONE      => _('Без условия'),
            self::CONDITION_PROGRESS  => _('Процент выполнения'),
            self::CONDITION_AVGBAL    => _('Средний балл по курсу'),
            self::CONDITION_SUMBAL    => _('Суммарный балл по курсу'),
            self::CONDITION_LESSON    => _('Выполнение другого занятия')
        );

    }

    static public function factory($data, $default = 'HM_Lesson_LessonModel')
    {

        if (isset($data['typeID']))
        {
            if ($data['typeID'] < 0) {
                return parent::factory($data, 'HM_Lesson_Custom_CustomModel');
            }

            switch($data['typeID']) {
                case HM_Event_EventModel::TYPE_POLL:
                    return parent::factory($data, 'HM_Lesson_Poll_PollModel');
                    break;
                case HM_Event_EventModel::TYPE_TEST:
                case HM_Event_EventModel::TYPE_EXERCISE:
                    return parent::factory($data, 'HM_Lesson_Test_TestModel');
                    break;
                case HM_Event_EventModel::TYPE_TASK:
                    return parent::factory($data, 'HM_Lesson_Task_TaskModel');
                    break;
				case HM_Event_EventModel::TYPE_JOURNAL:
                    return parent::factory($data, 'HM_Lesson_Journal_JournalModel');
                    break;
				case HM_Event_EventModel::TYPE_JOURNAL_LECTURE:
                    return parent::factory($data, 'HM_Lesson_Journal_Lecture_LectureModel');
                    break;
				case HM_Event_EventModel::TYPE_JOURNAL_PRACTICE:
                    return parent::factory($data, 'HM_Lesson_Journal_Practice_PracticeModel');
                    break;
				case HM_Event_EventModel::TYPE_JOURNAL_LAB:
                    return parent::factory($data, 'HM_Lesson_Journal_Lab_LabModel');
                    break;
                case HM_Event_EventModel::TYPE_LECTURE:
                    return parent::factory($data, 'HM_Lesson_Lecture_LectureModel');
                    break;
                case HM_Event_EventModel::TYPE_EMPTY:
                    return parent::factory($data, 'HM_Lesson_Empty_EmptyModel');
                    break;
                case HM_Event_EventModel::TYPE_COURSE:
                    return parent::factory($data, 'HM_Lesson_Course_CourseModel');
                    break;
                case HM_Event_EventModel::TYPE_WEBINAR:
                    return parent::factory($data, 'HM_Lesson_Webinar_WebinarModel');
                    break;
                case HM_Event_EventModel::TYPE_RESOURCE:
                    return parent::factory($data, 'HM_Lesson_Resource_ResourceModel');
                    break;
                case HM_Event_EventModel::TYPE_DEAN_POLL_FOR_LEADER:
                    return parent::factory($data, 'HM_Lesson_Poll_Dean_Leader_LeaderModel');
                    break;
                case HM_Event_EventModel::TYPE_DEAN_POLL_FOR_STUDENT:
                    return parent::factory($data, 'HM_Lesson_Poll_Dean_Student_StudentModel');
                    break;
                case HM_Event_EventModel::TYPE_DEAN_POLL_FOR_TEACHER:
                    return parent::factory($data, 'HM_Lesson_Poll_Dean_Teacher_TeacherModel');
                    break;
				case HM_Event_EventModel::TYPE_LANGUAGE:
                    return parent::factory($data, 'HM_Lesson_Language_LanguageModel');
                    break;
                default:
                    // Если занятие на основе сервиса взаимодействия
                    $activities = HM_Activity_ActivityModel::getActivityServices();
                    if (isset($activities[$data['typeID']])) {
                        $service = HM_Activity_ActivityModel::getActivityService($data['typeID']);

                        if (!Zend_Registry::get('serviceContainer')->hasService($service)) {
                            throw new HM_Exception(sprintf(_('Service %s not found'), $service));
                        }

                        if (!method_exists(Zend_Registry::get('serviceContainer')->getService($service), 'getLessonModelClass')) {
                            throw new HM_Exception(sprintf(_('Method getLessonModelClass not found in service %s'), $service));
                        }

                        $class = Zend_Registry::get('serviceContainer')->getService($service)->getLessonModelClass();
                        return parent::factory($data, $class);
                    }
                    break;
            }
        }
        if ($default != 'HM_Lesson_LessonModel') {
            return parent::factory($data, $default);
        }
    }

    public function setAssigns($assigns)
    {
        if (!isset($this->assigns)) {
            $this->assigns = $assigns;
        }
        return true;
    }
    
    public function getAssigns()
    {
        if (isset($this->assigns)) {
            return $this->assigns;
        }
        return false;
    }

    public function getStudentScore($studentId)
    {
        if ($studentId) {
            if ($assigns = $this->getAssigns()) {
                foreach($assigns as $assign) {
                    if ($assign->MID == $studentId) {
                        return $assign->getScore();
                    }
                }
            }
        }
        return false;
    }
	
	public function getStudentScoreAcademActivity($studentId)
    {
        if ($studentId) {
            if ($assigns = $this->getAssigns()) {
                foreach($assigns as $assign) {
                    if ($assign->MID == $studentId) {
                        return $assign->getScoreAcademActivity();
                    }
                }
            }
        }
        return false;
    }
	
	public function getStudentScoreAcademPractic($studentId)
    {
        if ($studentId) {
            if ($assigns = $this->getAssigns()) {
                foreach($assigns as $assign) {
                    if ($assign->MID == $studentId) {
                        return $assign->getScoreAcademPractic();
                    }
                }
            }
        }
        return false;
    }

    public function getStudentAssign($studentId)
    {
        if ($studentId) {
            if ($assigns = $this->getAssigns()) {
                foreach($assigns as $assign) {
                    if ($assign->MID == $studentId) {
                        return $assign;
                    }
                }
            }
        }
        return false;
    }

    public function getStudentComment($studentId)
    {
        if ($studentId) {
            if ($assigns = $this->getAssigns()) {
                foreach($assigns as $assign) {
                    if ($assign->MID == $studentId) {
                        return $assign->getComment();
                    }
                }
            }
        }
        return false;
    }

    public function getBeginDatetime($registered = null)
    {
        if ($this->isRelative() && (null !== $registered)) {
            $date = new Zend_Date(strtotime($registered) + $this->startday - 86400); // например, 172800 - это 2 полных дня; но относительная дата - это начало 2-го дня  
            $this->begin = $date->get(Zend_Date::DATETIME);
        }

        return $this->dateTimeWithoutSeconds($this->begin);
    }

    public function getEndDatetime($registered = null)
    {
        if ($this->isRelative() && (null !== $registered)) {
            $date = new Zend_Date(strtotime($registered) + $this->stopday - 86400);
            $this->end = $date->get(Zend_Date::DATETIME);
        }

        return $this->dateTimeWithoutSeconds($this->end);
    }

    public function getBeginDate()
    {
        return $this->date($this->begin);
    }

    public function getEndDate()
    {
        return $this->date($this->end);
    }

    public function getBeginDateRelative($mid = false, $date = null)
    {
        if($date == null){
    	    $assign = $this->getStudentAssign($mid ? $mid : Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserId());
            $date = $assign->beginRelative;
        }
        return $this->date($date);
    }

    public function getEndDateRelative($mid = false, $date = null)
    {
        if($date == null){
    	    $assign = $this->getStudentAssign($mid ? $mid : Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserId());
            $date = $assign->endRelative;
        }

    	return $this->date($date);
    }

    public function getBeginTime()
    {
        return $this->timeWithoutSeconds($this->begin);
    }

    public function getEndTime()
    {
        return $this->timeWithoutSeconds($this->end);
    }

    public function getModuleId()
    {
        $params = $this->getParams();
        return $params['module_id'];
    }

    public function getFormulaId()
    {
        $params = $this->getParams();
        if (isset($params['formula_id'])) {
            return $params['formula_id'];
        }
        return 0;
    }

    public function getFormulaGroupId()
    {
        $params = $this->getParams();
        if (isset($params['formula_group_id'])) {
            return $params['formula_group_id'];
        }
        return 0;
    }

    public function getFormulaPenaltyId()
    {
        $params = $this->getParams();
        if (isset($params['formula_penalty_id'])) {
            return $params['formula_penalty_id'];
        }
        return 0;
    }

    public function isStudentAssigned($studentId)
    {
        if (isset($this->assigns)) {
            foreach($this->assigns as $assign) {
                if ($assign->MID == $studentId) {
                    return true;
                }
            }
        }
        return false;
    }

    public function setParams($params)
    {
        $string = '';
        if (is_array($params) && count($params)) {
            foreach($params as $key => $value) {
                if ($value === '') continue;
                $string .= sprintf("%s=%s;", $key, $value);
            }
        }
        $this->params = $string;
    }

    public function getParams()
    {
        $params = array();
        if (isset($this->params)) {
            $lines = explode(';', $this->params);
            if (count($lines)) {
                foreach($lines as $line) {
                    list($key, $value) = explode('=', $line);
                    $params[$key] = $value;
                }
            }
        }
        return array_filter($params);
    }

    public function isExecutable()
    {

        $now = new Zend_Date();

        if ($this->recommend) return true;
        if ($this->timetype == self::TIMETYPE_FREE) return true; // занятие без ограничений
        if (!$this->timetype && $now->isLater(new Zend_Date($this->begin)) && $now->isEarlier(new Zend_Date($this->end))) {
            return true;
        }
        if ($this->timetype == self::TIMETYPE_RELATIVE) { // относительное занятие


            $assign = $this->getStudentAssign(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserId());
            if ($now->isLater(new Zend_Date($assign->beginRelative)) && $now->isEarlier(new Zend_Date($assign->endRelative))) {
            	return true;
            }

        }

        return false;
    }

    public function getBeginDay()
    {
        return floor($this->startday / 24 / 60 / 60);
    }

    public function getEndDay()
    {
        return floor($this->stopday / 24 / 60 / 60);
    }

    public function getNecessaryLessonsId()
    {
        $ids = array();
        if (strlen($this->cond_sheid)) {
            $ids = explode('#', $this->cond_sheid);
        }

        return $ids;
    }

    public function getLessonId(){
    	return $this->SHEID;
    }

    public function isConditionalLesson()
    {
        return ($this->cond_sheid || $this->cond_progress ||$this->cond_avgbal || $this->cond_sumbal);
    }

    public function isRelative()
    {
        return ($this->timetype == self::TIMETYPE_RELATIVE);
    }

    public function isTimeFree()
    {
        return ($this->timetype == self::TIMETYPE_FREE);
    }

    public function getServiceName()
    {
        return 'Lesson';
    }

    public function isResultInTable()
    {
        return true;
    }

    public function isFreeModeEnabled()
    {
        return false;
    }

    public function getUserIcon() {
        $user_ico = rtrim(Zend_Registry::get('config')->path->upload->lesson, '/') . '/' . $this->SHEID . '.jpg';
        if (file_exists($user_ico)) {
            return '/'. trim(Zend_Registry::get('config')->src->upload->lesson, '/') . '/' . $this->SHEID . '.jpg';
        } else {
            return null;
        }
    }

    public static function getIconClass($type)
    {

        /*
         * .grid_icon_chat,
            .grid_icon_course,
            .grid_icon_exercise,
            .grid_icon_forum,
            .grid_icon_lecture,
            .grid_icon_poll,
            .grid_icon_resource,
            .grid_icon_task,
            .grid_icon_test,
            .grid_icon_webinar,
            .grid_icon_wiki
         *
         *
         */
        $icons = array(
            HM_Event_EventModel::TYPE_COURSE => 'course',
            HM_Event_EventModel::TYPE_LECTURE => 'lecture',
            HM_Event_EventModel::TYPE_EXERCISE => 'exercise',
            HM_Event_EventModel::TYPE_POLL => 'poll',
            HM_Event_EventModel::TYPE_RESOURCE => 'resource',
            HM_Event_EventModel::TYPE_TASK => 'task',
            HM_Event_EventModel::TYPE_TEST=> 'test',
            HM_Event_EventModel::TYPE_WEBINAR => 'webinar'
        );
        return "tiny_icon_" . $icons[$type];
    }

    static public function getTypesFreeModeEnabled()
    {
        return array(
            HM_Event_EventModel::TYPE_COURSE,
            HM_Event_EventModel::TYPE_LECTURE,
            HM_Event_EventModel::TYPE_RESOURCE,
        );
    }
    
    public function setSortOrder($sortOrder)
    {
        $this->_sortOrder = $sortOrder;
    }

    public function getSortOrder()
    {
        return isset($this->_sortOrder) ? $this->_sortOrder : self::SORT_ORDER_DEFAULT;
    }
    
    static public function getDefaultScale()
    {
        return HM_Scale_ScaleModel::TYPE_CONTINUOUS;
    }    

    public function getScale($mark_type = HM_Mark_StrategyFactory::MARK_WEIGHT)
    {
        if ($mark_type == HM_Mark_StrategyFactory::MARK_BRS) {
            return HM_Scale_ScaleModel::TYPE_CONTINUOUS;
        }
        return $this->getDefaultScale();
    }    

    /**
     * Публичные методы для поля order
     * @author Artem Smirnov <tonakai.personal@gmail.com>
     * @date 17 january 2012
     */

    /**
     * Устанавливает значение поля order
     *
     * @param $newOrder
     *
     * @return HM_Lesson_LessonModel
     */
    public function setOrder($newOrder)
    {
        $this->order = intval($newOrder);
        return $this;
    }

    /**
     * Возвращает значение поля order
     *
     * @return int
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Список студентов данного занятия
     * @return HM_Collection
     */
    public function getAssignedStudents()
    {
        $select = $this->getService()->getSelect();
        $select->from(array('sID' => 'scheduleID'), array('sID.MID'))
            ->where('sID.SHEID = ? AND sID.MID != 0', $this->SHEID)
            ->group('sID.MID');

        $userIds = $select->query()->fetchAll(PDO::FETCH_NUM);

        foreach ($userIds as $key => $val) {
            $userIds[$key] = $val[0];
        }

        return Zend_Registry::get('serviceContainer')->getService('User')->getUsersByIds($userIds);
    }

    /**
     * Возвращает список подходящих оценщиков:
     * студенты с того же курса, с минимальной оценкой/или без (за конкретное занятие/или без)
     * @param null $mark_required
     * @param null $lesson_required
     * @return HM_Collection
     */
    public function getSuitableEvaluatorStudents($mark_required = null, $lesson_required = null)
    {
        /** @var Zend_Db_Select $select */
        $select = $this->getService()->getSelect();
        $select->from(array('st' => 'Students'), array('sID.MID'));
        $select->joinLeft(array('sID' => 'scheduleID'), 'sID.MID = st.MID', array());
        $select->joinLeft(array('s' => 'schedule'), 's.SHEID = sID.SHEID', array());
        //только пользователи с данного курса
        $select->where('st.CID = ? AND sID.MID != 0', $this->CID);
        //требуемый урок с выставленной оценкой
        if (!empty($lesson_required)) {
            $select->where('sID.SHEID = ? AND sID.V_STATUS != -1', $lesson_required);
        }
        //минимальная оценка
        if (!empty($mark_required)) {
            $select->where('sID.V_STATUS >= ?', $mark_required);
        }
        $select->group('sID.MID');

        $userIds = $select->query()->fetchAll(PDO::FETCH_NUM);

        foreach ($userIds as $key => $val) {
            $userIds[$key] = $val[0];
        }

        return Zend_Registry::get('serviceContainer')->getService('User')->getUsersByIds($userIds);
    }


    public function onFinish($result)
    {
        return false;
    }

    public function onStart()
    {
        return false;
    }

    public function getColor() {
        return ($this->required) ? '#CCF5DD' : '#e8fbf9';
    }
	
	public function clearString($str)
	{
		$str	= strip_tags($str); 
		$str 	= preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $str);
		$str	= str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    ',"&nbsp;", " ", '.'), '', $str);
		$str	= str_replace(" ",'',$str);		
		return $str;
	}
	
	public function getLandmarkNumber()
	{
		$title = $this->clearString($this->title);
		
		$phrases = array(
			'РубежныйКонтроль',
			'РубежныйКонтрольКРазделу',
		);
		
		foreach($phrases as $phrase){		
			preg_match('/(?:' . $phrase . ')([0-9]*)/iu', $title, $matches);
			$number = (int)$matches[1];
			if(!empty($number)){ return $number; }
		}		
		return false;
	}
	
	public function getNumberTask()
	{
		$title = $this->clearString($this->title);
		
		$phrases = array(
			'ПрактическоеЗадание',
			'ПрактическоеЗаданиеКРазделу',
			'ПрактическиеЗадания',
			'ПрактическиеЗаданияКРазделу',
			'ЗаданиеКРазделу',
			'ЗаданияКРазделу',
		);
		
		foreach($phrases as $phrase){		
			preg_match('/(?:' . $phrase . ')([0-9]*)/iu', $title, $matches);
			$number = (int)$matches[1];
			if(!empty($number)){ return $number; }
		}
		return false;		
	}	

	public function isLandmarkRating() 
	{
		return ( 	stristr($this->title, 'Итоговый тест')		!== FALSE
				||	stristr($this->title, 'Итоговый контроль')	!== FALSE ) 
			? true : false;
	}	
	
	#  Итоговый текущий рейтинг
	public function isCurrentRating() 
	{
		if($this->isEencouragement()){
			return true;
		}
		
		if(		!$this->isLandmarkRating() 
			&&	!$this->isJournal()
			&&	!$this->isTask()
		){	
			return true;			
		}
		return false;
	}	
	
	
	public function isEencouragement()
	{
		return (	$this->typeID == HM_Event_EventModel::TYPE_TASK 
				&&	stristr($this->title, 'поощрени') !== false )
			? true : false;
	}
	
	public function isJournal()
	{
		return (	$this->typeID == HM_Event_EventModel::TYPE_JOURNAL_LECTURE 
				||	$this->typeID == HM_Event_EventModel::TYPE_JOURNAL_LAB
				||	$this->typeID == HM_Event_EventModel::TYPE_JOURNAL_PRACTICE
			) ? true : false;
	}
	
	
	public function isTask()
	{
		return (
					$this->typeID == HM_Event_EventModel::TYPE_TASK 
				&& (stristr($this->title, 'задание') !== FALSE)
			) ? true : false;
	}
	
	public function isBoundaryControl() 
	{
		return ( stristr($this->title, 'Рубежный контрол') !== FALSE ) 
			   ? true : false;
	}
	
	public function isTotalPractic()
	{
		return ( stristr($this->title, 'ИПЗ') !== FALSE ) 
			   ? true : false;		
	}
	
	public function isTest()
	{
		return ( stristr($this->title, 'тест') !== FALSE ) 
			   ? true : false;		
	}
	
	
}
