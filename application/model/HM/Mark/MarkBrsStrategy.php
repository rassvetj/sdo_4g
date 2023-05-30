<?php
/**
 * Created by PhpStorm.
 * User: CuTHuK
 * Date: 18.03.14
 * Time: 14:02
 */

class HM_Mark_MarkBrsStrategy extends HM_Mark_MarkStrategy implements HM_Mark_MarkBehavior{

    public function setUserScore($userId, $scheduleId, $score, $courseId, $automatic)
    {
        //$currentUserId = $this->getService('User')->getCurrentUserId();
        $collection = $this->getService('LessonAssign')->fetchAllDependence('Lesson', array('MID = ?' => $userId, 'SHEID = ?' => $scheduleId));
        if (count($collection)) {
            $lessonAssign = $collection->current();
            $lesson = $lessonAssign->lessons->current();
            if (isset($lesson->max_ball)) {
                switch($lesson->getType()) {
//                    case HM_Event_EventModel::TYPE_TASK:
//                        break;
                    default:
                    $score = round($score * $lesson->max_ball / 100, 2);
                }
            }
            //дурацкий костыль с типом, при тесте и таске в анменеджде считается штраф, мб можно и убрать надо тестить
            if ($lesson->getFormulaPenaltyId() && !in_array($lesson->getType(),array(HM_Event_EventModel::TYPE_TEST, HM_Event_EventModel::TYPE_TASK))) {
                $formulaService = $this->getService('Formula');
                $lessonEnd = new HM_Date($lessonAssign->endRelative);
                $penalty = $formulaService->getPenalty(
                    $lesson->getFormulaPenaltyId(),
                    $formulaService->getPenaltyDays(time(), $lessonEnd->getTimestamp())
                );
                if ($penalty) {
                    $score = round($score*$penalty, 2);
                }
            }
            if($lesson->getType() == HM_Event_EventModel::TYPE_TEST){
                $score = $this->getBestScore($score, $lessonAssign);
            } 
			
            if($lesson->getType() == HM_Event_EventModel::TYPE_JOURNAL_PRACTICE){
				$this->getService('LessonJournalResult')->recalculateMark($userId, $lesson);
			} else {
				if($lessonAssign->V_STATUS != $score){
					$lessonAssign->V_STATUS = $score;
					$this->getService('LessonAssign')->updateUserLessonScore($lessonAssign->getValues());
				}
            }
            $this->onLessonScoreChanged($lesson->CID, $userId);
        }
    }

    public function getPenaltyScore() {

    }

    public function onLessonScoreChanged($subjectId, $userId)
    {
        $subjectService = $this->getService('Subject');
        $subjectMarkService = $this->getService('SubjectMark');
        
        $subject = $subjectService->find($subjectId)->current();
        $total = $this->calcTotalValue($subjectId, $userId, true);
        $total = ($total > 100) ? 100 : $total;
        $data = array(
            'cid' => $subjectId,
            'mid' => $userId,
            'mark' => $total,
            'confirmed' => HM_Subject_Mark_MarkModel::MARK_NOT_CONFIRMED,
        );
        $collection = $subjectMarkService->fetchAll(array(
            'cid = ?' => $subjectId,
            'mid = ?' => $userId
        ));
        if (count($collection)) {
            $subjectMarkService->updateWhere($data, array(
                'cid = ?' => $subjectId,
                'mid = ?' => $userId
            ));
        } else {
            $subjectMarkService->insert($data);
        }
        return $total;
    }

    public function calcTotalValue($subjectId, $userId, $throwExceptionIfLessonStatusIsNA = false, $byLessonType = false)
    {
        /** @var $formulaService HM_Formula_FormulaService */
        //$formulaService = $this->getService('Formula');

        $lessonAssigns = $this->_getLessonAssign($userId, $subjectId);

     //   $events = $this->_getAllEvents();
  //      $eventWeights = $events->getList('event_id', 'weight');
//        $eventScales = $events->getList('event_id', 'scale_id');

        //$lessonsByType = $avgByType = $weightsByType = array();
        $total = 0;
        if (!$byLessonType) {
			
			if($this->getService('Lesson')->issetJournalPractic($subjectId)){
				$total = $this->getService('LessonJournalResult')->calculateTotalBall($subjectId, $userId);
			} else {			
				foreach ($lessonAssigns as $lessonAssign) {
					if ($lessonAssign->V_STATUS == HM_Scale_Value_ValueModel::VALUE_NA) {
						continue;
					}
					$total += $lessonAssign->V_STATUS;	
					//if($this->getService('User')->getCurrentUserId() == '5128'){	
						//$lesson = $lessonAssign->lessons->current();
						//var_dump($lesson->required);
						//if($lessonAssign->V_STATUS == 100){
							//echo '<pre>';
							//echo 'SSID='.$lessonAssign->SSID.'. SHEID='.$lessonAssign->SHEID.'. V_STATUS='.$lessonAssign->V_STATUS.'. V_DONE='.$lessonAssign->V_DONE.'.<br>';							
							//echo '</pre>';					
						//}
					//}
					
				}
			}
        }
        else {
            $total = array(
                'value' => 0,
                'altValue' => 0
            );
            foreach ($lessonAssigns as $lessonAssign) {
                $lesson = $lessonAssign->lessons->current();
                if ($lessonAssign->V_STATUS == HM_Scale_Value_ValueModel::VALUE_NA) {
                    continue;
                }
                if ($lesson->required) {
                    $total['value'] += $lessonAssign->V_STATUS;
                }
                else {
                    $total['altValue'] += $lessonAssign->V_STATUS;
                }
            }
            $total['value'] = ($total['value'] > 100) ? 100 : $total['value'];
            $total['altValue'] = ($total['altValue'] > 40) ? 40 : $total['altValue'];
        }
        return $total;
    }

    public function calcMaxTotalValue($subjectId, $byLessonType = false)
    {
        return 100;
    }

    public function getValue()
    {
        return 'Накопительная система баллов';
    }

    public function addTypeElements(HM_Form &$form)
    {
        $prefix = HM_Mark_StrategyFactory::getType(HM_Mark_StrategyFactory::MARK_BRS);

        $form->addElement('hidden', $prefix.'_scale_id', array('Value'=> 1));

        $form->addElement('text', $prefix.'_threshold', array(
            'Label' => _('Порог прохождения'),
            'Description' => _('Пороговое значение, при достижении которого может выставляться оценка за курс'),
            'validators' => array(
                'Int',
                array('GreaterThan', false, array(-1)),
                array('LessThan', false, array(101))
            ),
            'filters' => array('int'),
            'disabled' => true,
            'class' => 'indent',
        ));

        /*$form->addElement('checkbox', $prefix.'_auto_mark', array(
            'Label' => _('Автоматически выставлять итоговую оценку за курс'),
            'Description' => '',
            'required' => false,
            'validators' => array('Int'),
            'filters' => array('int'),
            'value' => 0
        ));*/

        $collection = $this->getService('Formula')->fetchAll(
            $this->getService('Formula')->quoteInto(
                array('type = ?', ' AND  cid = 0'),
                array(HM_Formula_FormulaModel::TYPE_SUBJECT)
            ),
            'name'
        );
        $formulas = $collection->getList('id', 'name', _('Нет'));

        $form->addElement('select', $prefix.'_formula_id', array(
            'Label' => _('Формула для выставления итоговой оценки'),
            'required' => false,
            'disabled' => true,
            'validators' => array(
                'int',
                array('GreaterThan', false, array(-1))
            ),
            'filters' => array('int'),
            'multiOptions' => $formulas,
            'class' => 'indent',
        ));

        return array($prefix.'_scale_id',
            $prefix.'_threshold',
            $prefix.'_formula_id'
        );
    }

    public function getElementsNameArray()
    {
        return array('scale_id',
            'threshold',
            'formula_id'
        );
    }

    public function getSubjectProgressData($userId, $subjectId) {
		
		//if($this->getService('User')->getCurrentUserId() == '5128'){
			//var_dump($userId);
			//var_dump($subjectId);			
			//var_dump($this->calcTotalValue($subjectId, $userId));
		//}
		
		$cache = $this->_loadLessonAssignCache($subjectId);
        $markAllowed = true;
        $lessonAssigns = $this->_getLessonAssign($userId, $subjectId);
        $section = new HM_Section_SectionModel(array());
        $sectionAlternate  = new HM_Section_SectionModel(array());
        $sectionLessons = array();
        $sectionAlternateLessons = array();
        $section->name = _('Обязательные занятия');
        $sectionAlternate->name = _('Альтернативные занятия');
        $userTotal = $this->calcTotalValue($subjectId, $userId, false, true);
		
        $subjectTotal = $this->calcMaxTotalValueOfStudents($subjectId, true);
        $titles = array();

		$serviceLJResult = $this->getService('LessonJournalResult');
		
        foreach ($lessonAssigns as $lessonAssign) {
            $lesson = $lessonAssign->lessons->current();
            if ($lesson->isfree != HM_Lesson_LessonModel::MODE_PLAN) {
                continue;
            }
            $titles[$lesson->SHEID] = $lesson->title;
            $lesson->assigns = new HM_Collection(array($lessonAssign->getValues()), 'HM_Lesson_Assign_AssignModel');
			
			
			if($lesson->typeID == HM_Event_EventModel::TYPE_JOURNAL_PRACTICE){ 
                $lesson->max_ball_academ  = $lesson->max_ball;
				$lesson->max_ball_practic = $serviceLJResult->getPracticMaxBall($lesson->CID);                   
				$lesson->max_ball    += $lesson->max_ball_practic;                              				
			}
			
            if ($lesson->required) {
                if ($lessonAssign->V_STATUS == HM_Scale_Value_ValueModel::VALUE_NA) {
                    $markAllowed = false;
                }
                $sectionLessons[] = $lesson;
            }
            else {
                $sectionAlternateLessons[] = $lesson;
            }
        }
        $section->lessons = $sectionLessons;
        $sectionAlternate->lessons = $sectionAlternateLessons;
		
		//if($this->getService('User')->getCurrentUserId() == '5128'){
			//var_dump($userId);
			//var_dump($subjectId);			
			//var_dump($this->calcTotalValue($subjectId, $userId));
		//}
		
		
        return array('sections'=>array($section, $sectionAlternate),
            'currentScore' => $this->calcTotalValue($subjectId, $userId),
            'value' => $userTotal['value'],
            'bestValue' => $subjectTotal['value'],
            'targetValue' => $this->_subject->threshold,
            'altValue' => $userTotal['altValue'],
            'bestAltValue' => $subjectTotal['altValue'],
            'markAllowed' => $markAllowed,
            'titles' => $titles,
            'lesson-preview'=> 'lesson-preview-markBrs');
    }

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
                'Lesson.isfree = ?'
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
            'Lesson.CID = ? ',
        ), array(
            $subjectId,
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

    public function calcMaxTotalValueOfStudents($subjectId, $byLessonType = false)
    {
        $cache = $this->_loadLessonAssignCache($subjectId);
        $max = 0;
        if (!$byLessonType) {
            foreach ($cache as $mid => $lessonAssigns) {
                $userTotal = $this->calcTotalValue($subjectId, $mid, false, $byLessonType);

                if ($userTotal > $max) {
                    $max = $userTotal;
                }
            }
        }
        else {
            $max = array(
                'value' => 0,
                'altValue' => 0
            );
            foreach ($cache as $mid => $lessonAssigns) {
                $userTotal = $this->calcTotalValue($subjectId, $mid, false, $byLessonType);
                $max['value'] = ($userTotal['value'] > $max['value']) ? $userTotal['value'] : $max['value'];
                $max['altValue'] = ($userTotal['altValue'] > $max['altValue']) ? $userTotal['altValue'] : $max['altValue'];
            }
        }
        return $max;
    }
	
	
	public function clearLessonAssignCache(){
		$this->_lessonAssignCache = NULL;
	}
} 