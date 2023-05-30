<?php
class HM_Form_EvaluationSettings extends HM_Form {

    private $_evaluationSettings;

    public function init() {
        $subjectId = $this->getParam('subject_id', 0);
        $lessonId = $this->getParam('lesson_id', 0);

        $evaluationSettings = $this->getEvaluationSettings($lessonId);
        $evaluators = $this->getService('LessonEvaluators')->getEvaluators($lessonId);
        $evaluation_pairs = array();
        if ($evaluators) {
            foreach ($evaluators as $item) {
                $evaluation_pairs[$item->MID_evaluated] = $item->MID_evaluator;
            }
        }

        //список занятий для селектора
        $lessons = $this->getService('Lesson')->fetchAll(
            array(
                'CID = ?' => $subjectId,
                'typeID NOT IN (?)' => array_keys(HM_Event_EventModel::getExcludedTypes()),
                'isfree = ?' => HM_Lesson_LessonModel::MODE_PLAN
            )
        );
        $lessonsList = array('');
        if (count($lessons)) {
            foreach($lessons as $lesson) {
                $lessonsList[$lesson->SHEID] = $lesson->title;
            }
        }

        $studentsList = $this->getUserList($this->getService('Student'));
        $tutorsList   = $this->getUserList($this->getService('Tutor'));

        $this->addElement('hidden', 'subject_id', array(
            'Required' => true,
            'Validators' => array('Int'),
            'Filters' => array('Int'),
            'Value' => $subjectId
        ));

        $this->addElement('hidden', 'lesson_id', array(
            'Required' => true,
            'Validators' => array('Int'),
            'Filters' => array('Int'),
            'Value' => $lessonId
        ));

        $this->addElement('RadioGroup', 'evaluation_type', array(
            'Label' => '',
            'Value' => $evaluationSettings ? $evaluationSettings->evaluation_type : 0,
            'MultiOptions' => HM_Lesson_Evaluation_EvaluationSettingsModel::getEvaluationTypes(),
            'form' => $this,
            'dependences' => array(
                HM_Lesson_Evaluation_EvaluationSettingsModel::EVALUATION_TYPE_STUDENT => array('students_assign_mode'),
                HM_Lesson_Evaluation_EvaluationSettingsModel::EVALUATION_TYPE_TUTOR   => array('tutors_assign_mode')
            )
        ));

        $this->addElement('RadioGroup', 'students_assign_mode', array(
            'Label' => '',
            'Value' => $evaluationSettings ? $evaluationSettings->assign_mode : 0,
            'MultiOptions' => array(
                _('Автоматический выбор из слушателей курса'),
                _('Ручной выбор из слушателей курса')
            ),
            'form' => $this,
            'dependences' => array(
                0 => array('lesson_required', 'mark_required', 'students_evaluators_count'),
                1 => array('students_evaluators')
            )
        ));

        //Автоматический выбор из слушателей курса
        $this->addElement('select', 'lesson_required', array(
            'Label' => _('Выполнившим занятие'),
            'Value' => $evaluationSettings ? $evaluationSettings->lesson_required : 0,
            'Required' => false,
            'Validators' => array(
                'Int'
            ),
            'Filters' => array('Int'),
            'MultiOptions' => $lessonsList
        ));
        $this->addElement('text', 'mark_required', array(
            'Label' => _('Минимальная оценка за занятие'),
            'Value' => ($evaluationSettings && $evaluationSettings->mark_required != 0) ? $evaluationSettings->mark_required : '',
            'Required' => false,
            'Validators' => array(
                'Int'
            )
        ));

        $evaluators_count = range(0, 5);
        $evaluators_count[0] = '';
        $this->addElement('select', 'students_evaluators_count', array(
            'Label' => _('Оценщиков на 1 работу'),
            'Value' => $evaluationSettings ? $evaluationSettings->evaluators_count_required : 1,
            'Required' => false,
            'Filters' => array('Int'),
            'MultiOptions' => $evaluators_count
        ));

        //Ручной выбор из слушателей курса
        $students_evaluators = array();
        if ($evaluationSettings
            && $evaluationSettings->evaluation_type == HM_Lesson_Evaluation_EvaluationSettingsModel::EVALUATION_TYPE_STUDENT
            && $evaluationSettings->assign_mode == HM_Lesson_Evaluation_EvaluationSettingsModel::EVALUATION_MODE_MANUAL)
        {
            $students_evaluators = $evaluation_pairs;
        }
        $this->addElement(
            'associativeSelect',
            'students_evaluators',
            array(
                'Label'  => _('Оцениваемые/Оценщики'),
                'keys'   => $studentsList,
                'values' => $studentsList,
                'Value'  => $students_evaluators,
            )
        );






        $this->addElement('RadioGroup', 'tutors_assign_mode', array(
            'Label' => '',
            'Value' => $evaluationSettings ? $evaluationSettings->assign_mode : 0,
            'MultiOptions' => array(
                0 => _('Автоматический выбор из тьюторов курса'),
                1 => _('Ручной выбор из тьюторов курса')
            ),
            'form' => $this,
            'dependences' => array(
                0 => array('tutors_evaluators_count', 'tutors_list'),
                1 => array('tutors_evaluators')
            )
        ));

        //Автоматический выбор из тьюторов курса
        $this->addElement('select', 'tutors_evaluators_count', array(
            'Label' => _('Оценщиков на 1 работу'),
            'Value' => $evaluationSettings ? $evaluationSettings->evaluators_count_required : 1,
            'Required' => false,
            'Filters' => array('Int'),
            'MultiOptions' => $evaluators_count
        ));

        //набор тьюторов, из которых выбираем
        $tutors_list = array();
        if ($evaluationSettings
            && $evaluationSettings->evaluation_type == HM_Lesson_Evaluation_EvaluationSettingsModel::EVALUATION_TYPE_TUTOR
            && $evaluationSettings->assign_mode == HM_Lesson_Evaluation_EvaluationSettingsModel::EVALUATION_MODE_AUTO)
        {
            $tutors_list = array_unique(array_values($evaluation_pairs));
        }
        $this->addElement('UiMultiSelect', 'tutors_list',
            array(
                'Label' => '',
                'Required' => false,
                'Validators' => array(
                    'Int'
                ),
                'Filters' => array(
                    'Int'
                ),
                'Value' => $tutors_list,
                'multiOptions' => $tutorsList,
                'class' => 'multiselect'
            )
        );

        //Ручной выбор из тьюторов курса
        $tutors_evaluators = array();
        if ($evaluationSettings
            && $evaluationSettings->evaluation_type == HM_Lesson_Evaluation_EvaluationSettingsModel::EVALUATION_TYPE_TUTOR
            && $evaluationSettings->assign_mode == HM_Lesson_Evaluation_EvaluationSettingsModel::EVALUATION_MODE_MANUAL)
        {
            $tutors_evaluators = $evaluation_pairs;
        }
        $this->addElement(
            'associativeSelect',
            'tutors_evaluators',
            array(
                'Label'  => _('Оцениваемые/Оценщики'),
                'keys'   => $studentsList,
                'values' => $tutorsList,
                'Value'  => $tutors_evaluators,
            )
        );


        $this->addDisplayGroup(
            array(
                'evaluation_type',

                'students_assign_mode',
                'tutors_assign_mode',

                'lesson_required',
                'mark_required',

                'students_evaluators_count',
                'tutors_evaluators_count',

                'tutors_list',

                'students_evaluators',
                'tutors_evaluators'
            ),
            'EvaluationSettingsGroup',
            array('legend' => _('Система оценивания'))
        );

        $date_limit = $evaluationSettings ? $evaluationSettings->date_limit : '';
        $timestamp = strtotime($date_limit);
        $date = $timestamp ? date('d.m.Y', $timestamp) : '';

        $this->addElement('RadioGroup', 'date_limit_switcher', array(
            'Label' => '',
            'Value' => $timestamp ? 1 : 0,
            'MultiOptions' => array(
                0 => _('Без ограничений'),
                1 => _('До даты')
            ),
            'form' => $this,
            'dependences' => array(
                1 => array('date_limit')
            )
        ));
        $this->addElement('DatePicker', 'date_limit', array(
            'Value' => $date,
            'id' => 'date_limit',
            'Filters' => array('StripTags')
        ));


        $this->addDisplayGroup(
            array(
                'date_limit_switcher',
                'date_limit'
            ),
            'EvaluationDateLimitGroup',
            array('legend' => _('Сроки оценивания'))
        );

        //Шкалы и критерии оценивания
        $scales = $this->getService('Scale')->fetchAll(
            array('type = ?' => HM_Scale_ScaleModel::TYPE_DISCRETE),
            'scale_id'
        )->getList('scale_id', 'name');
        $scales = array('Значения от 0 до 100') + $scales;
        $this->addElement('select', 'scale_id', array(
                'Label' => _('Шкала оценивания'),
                'Required' => false,
                'multiOptions' => $scales,
                'Validators' => array('Int'),
                'Filters' => array('Int'),
                'Value' => $evaluationSettings ? $evaluationSettings->scale_id : 0
            )
        );
        //набор критериев
        $criterions = $this->getService('Criterion')->fetchAll(array(), 'id');
        $criterionsList = array();
        foreach ($criterions as $criterion) {
            $criterionsList[intval($criterion->id)] = $criterion->title;
        }
        $currentCriterions = unserialize($evaluationSettings->criterions);
        $this->addElement('UiMultiSelect', 'criterions',
            array(
                'Label' => '',
                'Required' => false,
                'Value' => array_keys($currentCriterions),
                'multiOptions' => $criterionsList,
                'class' => 'multiselect'
            )
        );

        $this->addDisplayGroup(
            array(
                'scale_id',
                'criterions'
            ),
            'ScaleCriterionGroup',
            array('legend' => _('Шкалы и критерии оценивания'))
        );

        $this->addElement('Submit', 'submit', array('Label' => _('Сохранить')));
        $this->addElement('hidden',
            'cancelUrl',
            array(
                'Required' => false,
                'Value' => $this->getView()->url(
                        array(
                            'action' => 'index',
                            'controller' => 'list',
                            'module' => 'lesson',
                            'subject_id' => $subjectId
                        ), false, true)
            )
        );

        parent::init();
    }

    /**
     * @param $lessonId
     * @return bool|HM_Lesson_Evaluation_EvaluationSettingsModel
     */
    public function getEvaluationSettings($lessonId) {
        if (!isset($this->_evaluationSettings)) {
            /** @var HM_Lesson_Evaluation_EvaluationSettingsService $service */
            $service = $this->getService('LessonEvaluationSettings');
            $this->_evaluationSettings = $service->getEvaluationSettings($lessonId);
        }
        return $this->_evaluationSettings;
    }

    /**
     * @param HM_Role_StudentService|HM_Role_TutorService $roleService
     * @return array
     */
    private function getUserList($roleService) {
        $where      = $roleService->quoteInto('CID=?', $this->getParam('subject_id', -1));
        $collection = $roleService->fetchAllDependence('User', $where);

        $userList    = array();
        if (count($collection)) {
            foreach ($collection as $model) {
                if ($users = array_pop($model->getData())) {
                    if (count($users) && !is_string($users)) {
                        foreach ($users as $user) {
                            $userList[$user->MID] = $user->getName();
                        }
                    }
                }
            }
        }
        return $userList;
    }

}