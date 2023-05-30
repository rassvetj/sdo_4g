<?php
/**
 * Created by PhpStorm.
 * User: CuTHuK
 * Date: 18.03.14
 * Time: 14:02
 */

class HM_Mark_MarkWeightStrategy extends HM_Mark_MarkStrategy implements HM_Mark_MarkBehavior {

    public function setUserScore($userId, $scheduleId, $score, $courseId = 0, $automatic = false)
    {
        try {
            // не работает из unmanaged
            $currentUserId = $this->getService('User')->getCurrentUserId();

        } catch (Zend_Session_Exception $e) {
            $currentUserId = $GLOBALS['s']['mid'];
        }

        if ($scheduleId != "total") {

            if ($score === ''){
                $score = -1;
            }

            $collection = $this->getService('LessonAssign')->fetchAllDependence('Lesson', array('MID = ?' => $userId, 'SHEID = ?' => $scheduleId));
            if (count($collection)) {
                $lessonAssign = $collection->current();
                $lesson = $lessonAssign->lessons->current();
                if($lesson->getType() == HM_Event_EventModel::TYPE_TEST){
                    $score = $this->getBestScore($score, $lessonAssign);
                }
                $lessonAssign->V_STATUS = round($score, 2);
                $assign = $this->getService('LessonAssign')->updateUserLessonScore($lessonAssign->getValues());

                $this->getService('LessonAssignMarkHistory')->insert(array(
                        'MID'  => $currentUserId,
                        'SSID' => $lessonAssign->SSID,
                        'mark' => intval($score),
                        'updated' => $this->getService('User')->getDateTime())
                );

                $lesson = $lessonAssign->lessons->current();

                // только если это автоматическое выставление оценки за зантяие - пересчитываем оценку за курс
                if ($automatic) {
                    $this->onLessonScoreChanged($lesson->CID, $currentUserId);
                }
            }

        } else {
            $one = $this->getOne($this->getService('SubjectMark')->fetchAll(array('mid = ?' => $userId, 'cid = ?' => $courseId)));

            $array = array(
                'mid' => $userId,
                'cid' => $courseId,
                'mark' => $score,
                'confirmed' => HM_Subject_Mark_MarkModel::MARK_CONFIRMED, // если препод ставит оценку руками, то дополнительного подтверждения для слушателя не требуется
            );

            if ( $score != '' && $score != -1 ) {
                $srv = $this->getService('SubjectMark');
                if( $one ) {
                    $totalMark = $srv->update($array);
                } else {
                    $totalMark = $srv->insert($array);
                }
            } else {
                $this->getService('SubjectMark')->deleteBy(array('mid = ?' => $userId, 'cid = ?' => $courseId));
            }
        }
    }
    public function onLessonScoreChanged($subjectId, $userId)
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
            $subjectMarkService = $this->getService('SubjectMark');
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
        }

        if ($subject->auto_graduate) {
            $subjectService->assignGraduated($subjectId, $userId);
        }
        return $mark;
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

    public function getValue()
    {
        return 'Итоговая оценка "взвешенная"';
    }

    public function addTypeElements(HM_Form &$form)
    {
        $prefix = HM_Mark_StrategyFactory::getType(HM_Mark_StrategyFactory::MARK_WEIGHT);
        $scales = array(0 =>_('Не выбрана'));
        $scales += $this->getService('Scale')->fetchAll(array('scale_id IN (?)' => HM_Scale_ScaleModel::getBuiltInTypes()), 'scale_id')->getList('scale_id', 'name');
        $form->addElement('select', $prefix.'_scale_id', array(
                'Label' => _('Шкала оценивания'),
                'required' => false,
                'multiOptions' => $scales,
                'Validators' => array('Int'),
                'Filters' => array('Int')
            )
        );

        $form->addElement('checkbox', $prefix.'_auto_mark', array(
            'Label' => _('Автоматически выставлять итоговую оценку за курс'),
            'Description' => '',
            'required' => false,
            'validators' => array('Int'),
            'filters' => array('int'),
            'value' => 0
        ));

        $form->addElement('text', $prefix.'_threshold', array(
            'Label' => _('Порог прохождения'),
            'Description' => _('Пороговое значение (в процентах от максимально возможного результата за курс), при достижении которого итоговая оценка "Пройдено успешно" автоматически выставляется за курс.'),
            'validators' => array(
                'Int',
                array('GreaterThan', false, array(-1)),
                array('LessThan', false, array(101))
            ),
            'filters' => array('int'),
            'disabled' => true,
            'class' => 'indent',
        ));

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

        $form->addElement('checkbox', $prefix.'_auto_graduate', array(
            'Label' => _('Автоматически переводить в прошедшие обучение'),
            'Description' => '',
            'required' => false,
            'validators' => array('Int'),
            'filters' => array('int'),
            'value' => 0
        ));

        return array($prefix.'_scale_id',
            $prefix.'_auto_mark',
            $prefix.'_threshold',
            $prefix.'_formula_id',
            $prefix.'_auto_graduate'
        );
    }

    public function getElementsNameArray()
    {
        return array('scale_id',
            'threshold',
            'auto_mark',
            'formula_id',
            'auto_graduate'
        );
    }

} 