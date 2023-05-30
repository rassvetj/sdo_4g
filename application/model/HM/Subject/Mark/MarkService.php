<?php
//require_once($_SERVER['DOCUMENT_ROOT']."/formula_calc.php");

class HM_Subject_Mark_MarkService extends HM_Service_Abstract implements Es_Entity_Trigger
{

    protected $userId = null;

    public function setUserId($id) {
        $this->userId = $id;
    }

    public function getUserId() {
        return $this->userId;
    }

    public function  update($data)
    {
        if(isset($data['mark'])){
            $data['mark'] = HM_Subject_Mark_MarkModel::filterMark($data['mark']);
        }
        $mark = parent::update($data);
        return $mark;
    }

    public function insert($data)
    {
        if(isset($data['mark'])){
            $data['mark'] = HM_Subject_Mark_MarkModel::filterMark($data['mark']);
        }
        $mark = parent::insert($data);
        return $mark;
    }

    public function updateWhere($data, $where) {
        if(isset($data['mark'])){
            $data['mark'] = HM_Subject_Mark_MarkModel::filterMark($data['mark']);
        }
        $updateResult = parent::updateWhere($data, $where);
        return $updateResult;
    }

    public function createEvent(HM_Model_Abstract $model) {
        $event = $this->getService('ESFactory')->newEvent($model, array(
            'mark'
        ), $this);
        $event->setParam('date', date('Y-m-d H:i:s'));
        return $event;
    }

    public function getRelatedUserList($id) {
        return array(intval($this->userId));
    }

    public function triggerPushCallback() {
        return function($ev) {
            $params = $ev->getParameters();
            $service = $ev->getSubject();
            $mark = $params['mark'];
            $service->setUserId(intval($mark->mid));
            $event = $service->createEvent($mark);
            $subject = $service->getService('Subject')->find(intval($mark->cid))->current();
            $event->setEventType(Es_Entity_AbstractEvent::EVENT_TYPE_COURSE_SCORE_TRIGGERED);
            $event->setParam('course_name', $subject->name);
            $event->setParam('course_id', $subject->getPrimaryKey());

            $eventGroup = $service->getService('ESFactory')->eventGroup(
                    HM_Subject_SubjectService::EVENT_GROUP_NAME_PREFIX, (int)$mark->cid
            );
            $eventGroup->setData(json_encode(
                array(
                    'course_name' => $subject->name,
                    'course_id' => $subject->getPrimaryKey()
                )
            ));
            
            $event->setGroup($eventGroup);

            $esService = $service->getService('EventServerDispatcher');
            $esService->trigger(
                Es_Service_Dispatcher::EVENT_PUSH,
                $service,
                array('event' => $event)
            );
        }; 
    }

    // кэширующие функции для работы calcTotalValue (иногда может вызываться много раз
    protected $_lessonAssignCache = array();

    protected function _getLessonAssign($userId, $subjectId)
    {
        $cache = &$this->_lessonAssignCache;

        if (!isset($cache[$subjectId])) {
            $cache[$subjectId] = array();
        }

        if (!isset($cache[$subjectId][$userId])) {

            $lessonAssignService = $this->getService('LessonAssign');

            $cache[$subjectId][$userId] = $lessonAssignService->fetchAllDependenceJoinInner('Lesson', $lessonAssignService->quoteInto(array(
                'self.MID = ? AND ',
                'Lesson.CID = ? AND ',
                'Lesson.isfree = ? AND ',
                'Lesson.vedomost = 1'
            ), array(
                $userId,
                $subjectId,
                HM_Lesson_LessonModel::MODE_PLAN
            )));
        }

        return $cache[$subjectId][$userId];

    }

    protected function _loadLessonAssignCache($subjectId)
    {
        $cache = array();

        $lessonAssigns = $this->getService('LessonAssign')->fetchAllDependenceJoinInner('Lesson', $this->getService('LessonAssign')->quoteInto(array(
            'Lesson.CID = ? AND ',
            'Lesson.isfree = ? AND ',
            'Lesson.vedomost = 1'
        ), array(
            $subjectId,
            HM_Lesson_LessonModel::MODE_PLAN
        )));

        foreach ($lessonAssigns as $lessonAssign) {
            $mid = $lessonAssign->MID;

            if (!isset($cache[$mid])) {
                $cache[$mid] = array();
            }

            $cache[$mid][] = $lessonAssign;
        }

        $this->_lessonAssignCache[$subjectId] = $cache;

        return $cache;
    }

    protected function _getAllEvents()
    {
        static $cache = null;

        if ($cache === null) {
            $cache = $this->getService('Event')->fetchAll();
        }

        return $cache;
    }

    public function getCourseProgress($subjectId, $userId)
    {
        $subjectService = $this->getService('Subject');
        $subject = $subjectService->getOne($subjectService->find($subjectId));
        $factory = $this->getService('MarkStrategyFactory')->getStrategy($subject->getMarkType());
        $maxValue = $factory->calcMaxTotalValue($subjectId);
        $userValue = $factory->calcTotalValue($subjectId, $userId);
        $maxValueOfStudents = $factory->calcMaxTotalValueOfStudents($subjectId);



        return array(
            'value' => $userValue,
            'maxValue' => $maxValue,
            'maxValueOfStudents' => $maxValueOfStudents,
            'threshold' => $subject->threshold
        );
    }

    /**
     * Подсчитывает лучший результат среди текущих слушаталей курса
     *
     * @param $subjectId
     * @return int|number
     */
    public function calcMaxTotalValueOfStudents($subjectId)
    {
        $cache = $this->_loadLessonAssignCache($subjectId);
        $max = 0;

        foreach ($cache as $mid => $lessonAssigns) {
            $userTotal = $this->calcTotalValue($subjectId, $mid);

            if ($userTotal > $max) {
                $max = $userTotal;
            }
        }

        return $max;
    }



    /**
     * Вероятно, функцию можно значительно упростить... Но нет времени
     * Написано на основе calcTotalValue
     *
     * @param $subjectId
     * @return number
     */
    public function calcMaxTotalValue($subjectId)
    {
        /** @var $formulaService HM_Formula_FormulaService */
        $formulaService = $this->getService('Formula');
        $lessonService = $this->getService('Lesson');

        $lessons = $lessonService->fetchAll($lessonService->quoteInto(array(
            'CID = ? AND ',
            'isfree = ? AND ',
            'vedomost = ?'
        ), array(
            $subjectId,
            HM_Lesson_LessonModel::MODE_PLAN,
            1
        )));

        $events = $this->_getAllEvents();
        $eventWeights = $events->getList('event_id', 'weight');
        $eventScales = $events->getList('event_id', 'scale_id');

        $lessonsByType = $avgByType = $weightsByType = array();

        foreach ($lessons as $lesson) {

            if (!isset($lessonsByType[$lesson->typeID])) {

                $scaleId = isset($eventScales[-$lesson->typeID]) ? $eventScales[-$lesson->typeID] : $lesson->getScale();

                list($min, $max) = HM_Scale_ScaleModel::getRange($scaleId);

                $lessonsByType[$lesson->typeID] = array(
                    'sum' => 0,
                    'count' => 0,
                    'min' => $min,
                    'max' => $max,
                );

                $weightsByType[$lesson->typeID] = isset($eventWeights[-$lesson->typeID]) ? $eventWeights[-$lesson->typeID] : HM_Event_EventModel::WEIGHT_DEFAULT;
            }

            $lessonParams = $lesson->getParams();
            $lessonMark   = $max;
            // нормализация оценки по формуле под шкалу
            /**
             * @todo: пока сделано для тестов для остальных типов занятий при создании в параметр formula_id при автоматичестом выставлении всегда записывается 1 как ИД формулы, что все портит
             */
            if ( isset($lessonParams['formula_id']) && $lesson->getType() == HM_Event_EventModel::TYPE_TEST) {
                $formula = $formulaService->getById($lessonParams['formula_id']);
                if ( $formula ) {
                    $formulaMarks = $formulaService->getFormulaMarksByScale($formula->formula, $min, $max);
                    if ( $formulaMarks && isset($formulaMarks[$max]) ) {
                        $lessonMark = $formulaMarks[$max];
                    }
                }
            }

            $lessonsByType[$lesson->typeID]['sum'] += $lessonMark;
            $lessonsByType[$lesson->typeID]['count']++;

        }


        HM_Event_EventService::normalizeWeights($weightsByType);

        foreach ($lessonsByType as $typeId => $values) {
            $avgByType[$typeId] = (100 * $weightsByType[$typeId] * $values['sum']) / ($values['count'] * ($values['max'] - $values['min']));
        }

        return array_sum($avgByType);

    }

    public function calcTotalValue($subjectId, $userId, $throwExceptionIfLessonStatusIsNA = false)
    {
        /** @var $formulaService HM_Formula_FormulaService */
        $formulaService = $this->getService('Formula');

        $lessonAssigns = $this->_getLessonAssign($userId, $subjectId);

        $events = $this->_getAllEvents();
        $eventWeights = $events->getList('event_id', 'weight');
        $eventScales = $events->getList('event_id', 'scale_id');

        $lessonsByType = $avgByType = $weightsByType = array();

        foreach ($lessonAssigns as $lessonAssign) {

            if ($lessonAssign->V_STATUS == HM_Scale_Value_ValueModel::VALUE_NA) {

                if ($throwExceptionIfLessonStatusIsNA) {
                    throw new HM_Exception(_('Курс пройден не полностью'));
                }

                continue;
            }

            $lesson = $lessonAssign->lessons->current();

            if (!isset($lessonsByType[$lesson->typeID])) {

                $scaleId = isset($eventScales[-$lesson->typeID]) ? $eventScales[-$lesson->typeID] : $lesson->getScale();

                list($min, $max) = HM_Scale_ScaleModel::getRange($scaleId);

                $lessonsByType[$lesson->typeID] = array(
                    'sum' => 0,
                    'count' => 0,
                    'min' => $min,
                    'max' => $max,
                );

                $weightsByType[$lesson->typeID] = isset($eventWeights[-$lesson->typeID]) ? $eventWeights[-$lesson->typeID] : HM_Event_EventModel::WEIGHT_DEFAULT;
            }

            $lessonParams = $lesson->getParams();
            $lessonMark   = $lessonAssign->V_STATUS;
            // нормализация оценки по формуле под шкалу
            /**
             * @todo: пока сделано для тестов для остальных типов занятий при создании в параметр formula_id при автоматичестом выставлении всегда записывается 1 как ИД формулы, что все портит
             */
            if ( isset($lessonParams['formula_id']) && $lesson->getType() == HM_Event_EventModel::TYPE_TEST) {
                $formula = $formulaService->getById($lessonParams['formula_id']);
                if ( $formula ) {
                    $formulaMarks = $formulaService->getFormulaMarksByScale($formula->formula, $min, $max);
                    if ( $formulaMarks && isset($formulaMarks[$lessonMark]) ) {
                        $lessonMark = $formulaMarks[$lessonMark];
                    }
                }
            }

            $lessonsByType[$lesson->typeID]['sum'] += $lessonMark;
            $lessonsByType[$lesson->typeID]['count']++;

        }


        HM_Event_EventService::normalizeWeights($weightsByType);

        foreach ($lessonsByType as $typeId => $values) {
            $avgByType[$typeId] = (100 * $weightsByType[$typeId] * $values['sum']) / ($values['count'] * ($values['max'] - $values['min']));
        }

        return array_sum($avgByType);

    }

/*    public function onLessonScoreChanged($subjectId, $userId)
    {
        $subjectService = $this->getService('Subject');
        $subject = $subjectService->getOne($subjectService->find($subjectId));

        try {
            $total = $this->calcTotalValue($subjectId, $userId, true);
        } catch (HM_Exception $e) {
            return;
        }

        if ($subject->auto_mark) {

            switch ($subject->getScale()) {
                case HM_Scale_ScaleModel::TYPE_BINARY:
                    if ($total >= $subject->threshold) {
                        $mark = HM_Scale_Value_ValueModel::VALUE_BINARY_ON;
                    }
                    break;
                case HM_Scale_ScaleModel::TYPE_TERNARY:
                    if (!empty($subject->threshold)) {
                        if ($total >= $subject->threshold) {
                            $mark = HM_Scale_Value_ValueModel::VALUE_TERNARY_ON;
                        } else {
                            $mark = HM_Scale_Value_ValueModel::VALUE_TERNARY_OFF;
                        }
                    }
                    break;
                case HM_Scale_ScaleModel::TYPE_CONTINUOUS:
                    if (count($subject->formula)) {
                        list($min, $max) = HM_Scale_ScaleModel::getRange($subject->getScale());
                        $text = ''; //некий дебильный параметр из unmanaged
                        $mark = viewFormula($subject->formula->current()->formula, $text, $min, $max, $total);
                    } else {
                        $mark = (int)$total;
                    }
                    break;
            }

            $data = array(
                'cid' => $subjectId,
                'mid' => $userId,
                'mark' => $mark,
                'confirmed' => HM_Subject_Mark_MarkModel::MARK_NOT_CONFIRMED,
            );

            $collection = $this->fetchAll(array(
                'cid = ?' => $subjectId,
                'mid = ?' => $userId
            ));

            if (count($collection)) {
                $this->updateWhere($data, array(
                    'cid = ?' => $subjectId,
                    'mid = ?' => $userId
                ));
            } else {
                $this->insert($data);
            }
        }

        if ($subject->auto_graduate) {
            $subjectService->assignGraduated($subjectId, $userId);
        }

    }*/
    
    public function isConfirmationNeeded($subjectId, $userId)
    {
        $collection = $this->fetchAll(array(
            'cid = ?' => $subjectId,        
            'mid = ?' => $userId,        
            'confirmed = ?' => HM_Subject_Mark_MarkModel::MARK_NOT_CONFIRMED,        
        ));  
        return count($collection);
    }    
    
    public function setConfirmed($subjectId, $userId)
    {
        $collection = $this->fetchAll(array(
            'cid = ?' => $subjectId,        
            'mid = ?' => $userId,        
            'confirmed = ?' => HM_Subject_Mark_MarkModel::MARK_NOT_CONFIRMED,     
        ));
        
        if (count($collection)) {
            $this->updateWhere(array(
                'confirmed' => HM_Subject_Mark_MarkModel::MARK_CONFIRMED,
            ), array(
                'cid = ?' => $subjectId,        
                'mid = ?' => $userId,        
                'confirmed = ?' => HM_Subject_Mark_MarkModel::MARK_NOT_CONFIRMED,     
            ));
            return $collection->current();
        }
        return false;
    }
    
    public function setMark($score, $subjectId, $userId) {
        $subject = $this->getService('Subject')->find($subjectId)->current();
        $score = ($score>100) ? 100 : $score;

        $formula = $this->getService('Formula')->getById($subject->formula_id);
        list($min, $max) = HM_Scale_ScaleModel::getRange($subject->getScale());
        $text = ''; //некий дебильный параметр из unmanaged
        $mark = $this->getService('Formula')->viewFormula($formula->formula, $text, $min, $max, $score);

        $data = array(
            'cid' => $subjectId,
            'mid' => $userId,
            'mark' => $mark,
            'confirmed' => HM_Subject_Mark_MarkModel::MARK_CONFIRMED,
        );

        $existingMark = $this->getOne($this->fetchAll(array(
            'cid = ?' => $subjectId,
            'mid = ?' => $userId
        )));
        
        if ($existingMark->mid && $existingMark->cid) {
//            $result = $existingMark;
//            if ($existingMark->mark < $mark) {
                $result = $this->updateWhere($data, array(
                    'cid = ?' => $subjectId,
                    'mid = ?' => $userId
                ));
//            }
        } else {
            $result = $this->insert($data);
        }
        return $result;
    }
	
	/**
	 * обновление итоговой оценки
	*/
	
	public function recalculateSubjectMark($subject_id, $user_id) {
		if(!$subject_id || !$user_id) { return false; }
		
		$lessonsIDs = $this->getService('Lesson')->fetchAll(array(
				'CID = ?' 			=> $subject_id,        
				'typeID NOT IN (?)' => array_keys(HM_Event_EventModel::getExcludedTypes()),        
				'isfree = ?' 		=> HM_Lesson_LessonModel::MODE_PLAN,   			
			))->getList('SHEID');
		if(empty($lessonsIDs)) { return false; }
		
		$userMarks = $this->getService('LessonAssign')->fetchAll(array(
			'MID = ?' 		=> $user_id, 
			'V_STATUS > ?' 	=> 0, 
			'SHEID IN (?)' 	=> $lessonsIDs, 
		));
		if(empty($userMarks)){ return false; }
		$totalBall = 0;
		foreach($userMarks as $mark){			
			$totalBall = $totalBall + $mark->V_STATUS;

		}
		
		$existingMark = $this->getOne($this->fetchAll(array(
			'cid = ?' => $subject_id,
			'mid = ?' => $user_id,
		)));
		if($existingMark->mark == $totalBall){ return true; }
		
		$data = array(
			'cid' 		=> $subject_id,
			'mid' 		=> $user_id,
			'mark' 		=> $totalBall,
			'confirmed' => HM_Subject_Mark_MarkModel::MARK_CONFIRMED,
		);
		
		if ($existingMark->mid && $existingMark->cid) {		
			$result = $this->updateWhere($data, array(
				'cid = ?' => $subject_id,
				'mid = ?' => $user_id
			));		
		} else {
			$result = $this->insert($data);
		}
		return $result;		
	}
	
	public function getRow($subject_id, $student_id)
	{
		return $this->getOne($this->fetchAll($this->quoteInto(array('cid = ?', ' AND mid =?'), array($subject_id, $student_id))));
	}
	
	public function getBySubject($subject_id){
		return $this->fetchAll($this->quoteInto('cid = ?', $subject_id));
	}
	
	public function getBall($subject_id, $student_id, $get_biggest = false)
	{
		$item 			= $this->getOne($this->fetchAll($this->quoteInto(array('cid = ?', ' AND mid =?'), array($subject_id, $student_id))));
		$sum_separated	= (empty($item->mark_current) && empty($item->mark_landmark)) ? false : $item->mark_current + $item->mark_landmark;
		
		if($sum_separated === false){
			return $item->mark;
		}
		
		if($get_biggest){
			return ($item->mark > $sum_separated) ? $item->mark : $sum_separated;
		}
		
		return $sum_separated;
	}
	
	/**		
		@return object of HM_Subject_Mark_MarkModel
	*/
	public function calculateScore($subject_id, $student_id)
	{
		$data      = array(
			'cid' => $subject_id,
			'mid' => $student_id,
		);
		$model     = new HM_Subject_Mark_MarkModel($data);
		
		$subject   = $this->getService('Subject')->getById($subject_id);
		if(!$subject){ return $model; }
		
		$type = $subject->getTypeModel();
		
		return $type->calculateScore($subject_id, $student_id);
	}
	
	public function setScore($subject_id, $student_id)
	{
		$model       = $this->calculateScore($subject_id, $student_id);		
		$model->info = $this->encodeInfo($this->getService('Subject')->getInadmissibilityReasons($subject_id, $student_id));
		
		$exists_item = $this->getScore($subject_id, $student_id);
		if(!$exists_item){
			$model->need_to_1c = 0;
			$model->confirmed  = HM_Subject_Mark_MarkModel::MARK_CONFIRMED;			
			return $this->insert($model->getValues());
		}
		
		if($this->isSameItems($model, $exists_item)){
			return $exists_item;
		}
		
		$exists_item->mark          = $model->mark;
		$exists_item->mark_current  = $model->mark_current;
		$exists_item->mark_landmark = $model->mark_landmark;
		$exists_item->info          = $model->info;
		
		return $this->update($exists_item->getValues());		
	}
	
	public function isSameItems($item_1, $item_2)
	{
		if($item_1->cid           != $item_2->cid)          { return false; }
		if($item_1->mid           != $item_2->mid)          { return false; }
		if($item_1->mark          != $item_2->mark)         { return false; }
		if($item_1->mark_current  != $item_2->mark_current) { return false; }
		if($item_1->mark_landmark != $item_2->mark_landmark){ return false; }
		if($item_1->info          != $item_2->info)         { return false; }
		return true;		
	}
	
	public function encodeInfo($raw)
	{
		if(empty($raw) || $raw === TRUE){ return ''; }
		return json_encode($raw);
	}
	
	public function getScore($subject_id, $student_id)
	{
		return $this->getOne($this->fetchAll(array(
            'cid = ?' => $subject_id,
            'mid = ?' => $student_id
        )));
	}
	
}
