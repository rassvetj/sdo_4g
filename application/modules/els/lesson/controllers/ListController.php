<?php

class Lesson_ListController extends HM_Controller_Action_Subject implements Es_Entity_EventViewer {

    protected $_formName = 'HM_Form_Lesson';
    protected $_module = 'lesson';
    protected $_controller = 'list';
    protected $_tutorList  = array();
    protected $_currentLang = 'rus';

    public function generateAction() {
        $subjectId = (int) $this->_getParam('subject_id', 0);
        $i = 0;
        //$students = $lesson->getService()->getAvailableStudents($subjectId);
        // Тесты более не могут быть свободными
        /**
         * Да, но нужно чтобы при генерации тесты которые относятся к уроку добавлялись в занятия.
         * Поэтому раскомментировано обратно.
         * @author Artem Smirnov <tonakai.personal@gmail.com>
         * @date 14.01.2013
         */
        $tests = $this->getService('TestAbstract')->fetchAllDependenceJoinInner('SubjectAssign', 'SubjectAssign.subject_id = ' . $subjectId);
        foreach ($tests as $value) {

            $tests = $this->getService('Test')->fetchAll(array('test_id = ?' => $value->test_id));
            $testIds = $tests->getList('tid', 'title');

            $lessons = $this->getService('Lesson')->fetchAll(
                    array(
                        'CID = ?' => $subjectId,
                        //'params LIKE "%module_id=' . $test->tid . ';%" ',
                        'typeID = ?' => HM_Event_EventModel::TYPE_TEST,
                        'isfree = ?' => HM_Lesson_LessonModel::MODE_PLAN,
                    )
            );
            foreach ($lessons as $lesson) {
                if (in_array($lesson->getModuleId(), array_keys($testIds))) {
                    continue 2;
                }
            }

            $form = new $this->_formName();
            $form->getSubForm('step2')->initTest();
            $form->populate(
                    array(
                        'title' => $value->title,
                        'event_id' => HM_Event_EventModel::TYPE_TEST,
                        'vedomost' => 1,
                        'teacher' => 0,
                        'moderator' => 0,
                        'recommend' => 1,
                        'all' => 1,
                        'GroupDate' => HM_Lesson_LessonModel::TIMETYPE_FREE,
                        'beginDate' => '',
                        'endDate' => '',
                        'currentDate' => '',
                        'beginTime' => '',
                        'endTime' => '',
                        'beginRelative' => '',
                        'endRelative' => '',
                        'Condition' => HM_Lesson_LessonModel::CONDITION_NONE,
                        'cond_sheid' => '',
                        'cond_mark' => '',
                        'cond_progress' => '',
                        'cond_avgbal' => '',
                        'cond_sumbal' => '',
                        'module' => $value->test_id,
                        'gid' => 0,
                        'formula' => 0,
                        'formula_group' => 0,
                        'formula_penalty' => 0,
                        'students' => array(),
                        'isfree' => HM_Lesson_LessonModel::MODE_PLAN,
                    )
            );
            $this->addLesson($form);
            unset($form);

            $i++;
        }

        // Электронные курсы
        $courses = $this->getService('Course')->fetchAllDependenceJoinInner('SubjectAssign', 'SubjectAssign.subject_id = ' . $subjectId);
        foreach ($courses as $value) {

            $lesson = $this->getService('Lesson')->fetchAll(
                    array(
                        'CID = ?' => $subjectId,
                        'params LIKE ?' => '%module_id=' . $value->CID . ';%',
                        'typeID = ?' => HM_Event_EventModel::TYPE_COURSE,
                        'isfree = ?' => HM_Lesson_LessonModel::MODE_PLAN,
                    )
            );

            if (count($lesson) > 0) {
                continue;
            }

            // исключить из своб.доступа
            $this->getService('Lesson')->updateWhere(
                    array(
                'isfree' => HM_Lesson_LessonModel::MODE_FREE_BLOCKED,
                    ), array(
                'CID = ?' => $subjectId,
                'params LIKE ?' => '%module_id=' . $value->CID . ';%',
                'typeID = ?' => HM_Event_EventModel::TYPE_COURSE,
                'isfree = ?' => HM_Lesson_LessonModel::MODE_FREE,
                    )
            );

            $form = new $this->_formName();
            $form->getSubForm('step2')->initCourse();
			$form->getSubForm('step2')->setFormula(); 
            $form->populate(
                    array(
                        'title' => $value->Title,
                        'event_id' => HM_Event_EventModel::TYPE_COURSE,
                        'vedomost' => 1,
                        'teacher' => 0,
                        'moderator' => 0,
                        'recommend' => 1,
                        'all' => 1,
                        'GroupDate' => HM_Lesson_LessonModel::TIMETYPE_FREE,
                        'beginDate' => '',
                        'endDate' => '',
                        'currentDate' => '',
                        'beginTime' => '',
                        'endTime' => '',
                        'beginRelative' => '',
                        'endRelative' => '',
                        'Condition' => HM_Lesson_LessonModel::CONDITION_NONE,
                        'cond_sheid' => '',
                        'cond_mark' => '',
                        'cond_progress' => '',
                        'cond_avgbal' => '',
                        'cond_sumbal' => '',
                        'module' => $value->CID,
                        'gid' => 0,
                        'formula' => 1,
                        'formula_group' => 0,
                        'formula_penalty' => 0,
                        'students' => array(),
                        'isfree' => HM_Lesson_LessonModel::MODE_PLAN,
                    )
            );
            $this->addLesson($form);
            unset($form);

            $i++;
        }


        // Ресурсы
        $resources = $this->getService('Resource')->fetchAllDependenceJoinInner('SubjectAssign', 'SubjectAssign.subject_id = ' . $subjectId);
        foreach ($resources as $value) {

            $lesson = $this->getService('Lesson')->fetchAll(
                    array(
                        'CID = ?' => $subjectId,
                        "params LIKE ?" => '%module_id=' . $value->resource_id . ';%',
                        'typeID = ?' => HM_Event_EventModel::TYPE_RESOURCE,
                        'isfree = ?' => HM_Lesson_LessonModel::MODE_PLAN,
                    )
            );

            if (count($lesson) > 0) {
                continue;
            }

            // исключить из своб.доступа
            $this->getService('Lesson')->updateWhere(
                    array(
                'isfree' => HM_Lesson_LessonModel::MODE_FREE_BLOCKED,
                    ), array(
                'CID = ?' => $subjectId,
                "params LIKE ?" => '%module_id=' . $value->resource_id . ';%',
                'typeID = ?' => HM_Event_EventModel::TYPE_RESOURCE,
                'isfree = ?' => HM_Lesson_LessonModel::MODE_FREE,
                    )
            );

            $form = new $this->_formName();
            $form->getSubForm('step2')->initResource();
            $form->populate(
                    array(
                        'title' => $value->title,
                        'event_id' => HM_Event_EventModel::TYPE_RESOURCE,
                        'vedomost' => 1,
                        'teacher' => 0,
                        'moderator' => 0,
                        'recommend' => 1,
                        'all' => 1,
                        'GroupDate' => HM_Lesson_LessonModel::TIMETYPE_FREE,
                        'beginDate' => '',
                        'endDate' => '',
                        'currentDate' => '',
                        'beginTime' => '',
                        'endTime' => '',
                        'beginRelative' => '',
                        'endRelative' => '',
                        'Condition' => HM_Lesson_LessonModel::CONDITION_NONE,
                        'cond_sheid' => '',
                        'cond_mark' => '',
                        'cond_progress' => '',
                        'cond_avgbal' => '',
                        'cond_sumbal' => '',
                        'module' => $value->resource_id,
                        'gid' => 0,
                        'formula' => 0,
                        'formula_group' => 0,
                        'formula_penalty' => 0,
                        'students' => array(),
                        'isfree = ?' => HM_Lesson_LessonModel::MODE_PLAN,
                    )
            );
            $this->addLesson($form);
            unset($form);

            $i++;
        }



        //Опросы
        $polls = $this->getService('Poll')->fetchAllDependenceJoinInner('SubjectAssign', 'SubjectAssign.subject_id = ' . $subjectId);
        foreach ($polls as $value) {

            $tests = $this->getService('Test')->fetchAll(array('test_id = ?' => $value->quiz_id));
            $testIds = $tests->getList('tid', 'title');

            $lessons = $this->getService('Lesson')->fetchAll(
                    array(
                        'CID = ?' => $subjectId,
                        //'params LIKE "%module_id=' . $test->tid . ';%" ',
                        'typeID = ?' => HM_Event_EventModel::TYPE_POLL
                    )
            );
            foreach ($lessons as $lesson) {
                if (in_array($lesson->getModuleId(), array_keys($testIds))) {
                    continue 2;
                }
            }


            $form = new $this->_formName();
            $form->getSubForm('step2')->initPoll();
            $form->populate(
                    array(
                        'title' => $value->title,
                        'event_id' => HM_Event_EventModel::TYPE_POLL,
                        'vedomost' => 0,
                        'teacher' => 0,
                        'moderator' => 0,
                        'recommend' => 1,
                        'all' => 1,
                        'GroupDate' => HM_Lesson_LessonModel::TIMETYPE_FREE,
                        'beginDate' => '',
                        'endDate' => '',
                        'currentDate' => '',
                        'beginTime' => '',
                        'endTime' => '',
                        'beginRelative' => '',
                        'endRelative' => '',
                        'Condition' => HM_Lesson_LessonModel::CONDITION_NONE,
                        'cond_sheid' => '',
                        'cond_mark' => '',
                        'cond_progress' => '',
                        'cond_avgbal' => '',
                        'cond_sumbal' => '',
                        'module' => $value->quiz_id,
                        'gid' => 0,
                        'formula' => 0,
                        'formula_group' => 0,
                        'formula_penalty' => 0,
                        'students' => array()
                    )
            );
            $this->addLesson($form);
            unset($form);

            $i++;
        }


        //webinars
        $webinars = $this->getService('Webinar')->fetchAll(array('subject_id = ?' => $subjectId));
        foreach ($webinars as $value) {

            $lesson = $this->getService('Lesson')->fetchAll(
                    array(
                        'CID = ?' => $subjectId,
                        'params LIKE ?' => '%module_id=' . $value->webinar_id . ';%',
                        'typeID = ?' => HM_Event_EventModel::TYPE_WEBINAR
                    )
            );
            if (count($lesson) > 0) {
                continue;
            }


            $form = new $this->_formName();
            $form->getSubForm('step2')->initPoll();
            $form->populate(
                    array(
                        'title' => $value->name,
                        'event_id' => HM_Event_EventModel::TYPE_WEBINAR,
                        'vedomost' => 1,
                        'teacher' => 0,
                        'moderator' => 0,
                        'recommend' => 1,
                        'all' => 1,
                        'GroupDate' => HM_Lesson_LessonModel::TIMETYPE_FREE,
                        'beginDate' => '',
                        'endDate' => '',
                        'currentDate' => '',
                        'beginTime' => '',
                        'endTime' => '',
                        'beginRelative' => '',
                        'endRelative' => '',
                        'Condition' => HM_Lesson_LessonModel::CONDITION_NONE,
                        'cond_sheid' => '',
                        'cond_mark' => '',
                        'cond_progress' => '',
                        'cond_avgbal' => '',
                        'cond_sumbal' => '',
                        'module' => $value->webinar_id,
                        'gid' => 0,
                        'formula' => 0,
                        'formula_group' => 0,
                        'formula_penalty' => 0,
                        'students' => array()
                    )
            );
            $this->addLesson($form);
            unset($form);

            $i++;
        }


        //Tasks
        $tasks = $this->getService('Task')->fetchAllDependenceJoinInner('SubjectAssign', 'SubjectAssign.subject_id = ' . $subjectId);
        foreach ($tasks as $value) {

            $tests = $this->getService('Test')->fetchAll(array('test_id = ?' => $value->task_id));
            $testIds = $tests->getList('tid', 'title');

            $lessons = $this->getService('Lesson')->fetchAll(
                    array(
                        'CID = ?' => $subjectId,
                        //'params LIKE "%module_id=' . $test->tid . ';%" ',
                        'typeID = ?' => HM_Event_EventModel::TYPE_TASK
                    )
            );
            foreach ($lessons as $lesson) {
                if (in_array($lesson->getModuleId(), array_keys($testIds))) {
                    continue 2;
                }
            }



            $form = new $this->_formName();
            $form->getSubForm('step2')->initTest();
            $form->populate(
                    array(
                        'title' => $value->title,
                        'event_id' => HM_Event_EventModel::TYPE_TASK,
                        'vedomost' => 1,
                        'teacher' => 0,
                        'moderator' => 0,
                        'recommend' => 1,
                        'all' => 1,
                        'GroupDate' => HM_Lesson_LessonModel::TIMETYPE_FREE,
                        'beginDate' => '',
                        'endDate' => '',
                        'currentDate' => '',
                        'beginTime' => '',
                        'endTime' => '',
                        'beginRelative' => '',
                        'endRelative' => '',
                        'Condition' => HM_Lesson_LessonModel::CONDITION_NONE,
                        'cond_sheid' => '',
                        'cond_mark' => '',
                        'cond_progress' => '',
                        'cond_avgbal' => '',
                        'cond_sumbal' => '',
                        'module' => $value->task_id,
                        'gid' => 0,
                        'formula' => 0,
                        'formula_group' => 0,
                        'formula_penalty' => 0,
                        'students' => array()
                    )
            );
            $this->addLesson($form);
            unset($form);

            $i++;
        }

        $this->_flashMessenger->addMessage(sprintf(_('Сгенерировано занятий: %s'), $i));
        $this->_redirector->gotoSimple('index', $this->_controller, $this->_module, array('subject_id' => $subjectId));
    }

    protected function addLesson($form) {
        $subjectId = (int) $this->_getParam('subject_id', 0);

        $activities = '';
        if (null !== $form->getValue('activities')) {
            if (is_array($form->getValue('activities')) && count($form->getValue('activities'))) {
                $activities = serialize($form->getValue('activities'));
            }
        }

        $tool = '';
        if ($form->getValue('event_id') < 0) {
            $event = $this->getOne(
                    $this->getService('Event')->find(-$form->getValue('event_id'))
            );
            if ($event) {
                $tool = $event->tool;
            }
        }

        $typeId = $form->getValue('event_id');
        $moduleId = $form->getValue('module');
        if ($typeId == HM_Event_EventModel::TYPE_LECTURE) {
            $typeId = HM_Event_EventModel::TYPE_COURSE; // скрываем весь модуль
            $moduleId = $this->getService('CourseItem')->getCourse($moduleId);
        }
        $this->getService('Lesson')->setLessonFreeMode($moduleId, $typeId, $subjectId, HM_Lesson_LessonModel::MODE_FREE_BLOCKED);

        $data = array(
            'title' => $form->getValue('title'),
            'title_translation' => $form->getValue('title_translation'),
            'CID' => $subjectId,
            'typeID' => $form->getValue('event_id'),
            'vedomost' => $form->getValue('vedomost'),
            'allowTutors' => $form->getValue('allowTutors'),
            'teacher' => $form->getValue('teacher'),
            'moderator' => $form->getValue('moderator'),
            'createID' => $this->getService('User')->getCurrentUserId(),
            'recommend' => ($form->getValue('formula_penalty', 0)) ? 1 :0,
            'all' => (int) $form->getValue('all'),
            'GroupDate' => $form->getValue('GroupDate'),
            'beginDate' => $form->getValue('beginDate'),
            'endDate' => $form->getValue('endDate'),
            'currentDate' => $form->getValue('currentDate'),
            'beginTime' => $form->getValue('beginTime'),
            'endTime' => $form->getValue('endTime'),
            'beginRelative' => ($form->getValue('beginRelative')) ? $form->getValue('beginRelative') : 1,
            'endRelative' => ($form->getValue('endRelative')) ? $form->getValue('endRelative') : 1,
            'Condition' => $form->getValue('Condition'),
            'cond_sheid' => (string) $form->getValue('cond_sheid'),
            'cond_mark' => (string) $form->getValue('cond_mark'),
            'cond_progress' => (string) $form->getValue('cond_progress'),
            'cond_avgbal' => (string) $form->getValue('cond_avgbal'),
            'cond_sumbal' => (string) $form->getValue('cond_sumbal'),
            'gid' => $form->getValue('subgroups'),
            'notice' => $form->getValue('notice'),
            'notice_days' => (int) $form->getValue('notice_days'),
            'activities' => $activities,
            'descript' => $form->getValue('descript'),
            'descript_translation' => $form->getValue('descript_translation'),
            'tool' => $tool,
            'isfree' => HM_Lesson_LessonModel::MODE_PLAN,
            'max_ball' => $form->getValue('max_ball', 0),
            'required' => $form->getValue('required', 0),			
			'isCanMarkAlways' => $form->getValue('isCanMarkAlways'),
			'isCanSetMark' 		=> $form->getValue('isCanSetMark'),
			'max_ball_academic' => $form->getValue('max_ball_academic'),
			'max_ball_practic' 	=> $form->getValue('max_ball_practic'),
            //'formula_penalty_id' => $form->getValue('formula_penalty', 0)
        );

        $lessons = $this->getService('Lesson')->fetchAll(array('CID = ?' => $subjectId));
        $lessonsOrders = $lessons->getList('order');
        if ($lessonsOrders) {
            $highestValue = max(array_values($lessonsOrders));
            $highestValue++;
            $data['order'] = $highestValue;
        }

        $lesson = $this->getService('Lesson')->insert($data);

        if ($lesson) {
            $this->_postProcess($lesson, $form);
            $params = $lesson->getParams();

            if ($form->getValue('module')) {
                $params['module_id'] = $form->getValue('module');
            }

            if ($form->getValue('assign_type')) {
                $params['assign_type'] = $form->getValue('assign_type');
            } elseif (isset($params['assign_type']) && $params['assign_type']) {
                unset($params['assign_type']);
            }

            if ($form->getValue('is_hidden', 0)) {
                $params['is_hidden'] = $form->getValue('is_hidden');
            } elseif (isset($params['is_hidden']) && $params['is_hidden']) {
                unset($params['is_hidden']);
            }

            if ($form->getValue('formula')) {
                $params['formula_id'] = $form->getValue('formula');
            } elseif (isset($params['formula_id'])) {
                unset($params['formula_id']);
            }

            if ($form->getValue('formula_group')) {
                $params['formula_group_id'] = $form->getValue('formula_group');
            }

            if ($form->getValue('formula_penalty')) {
                $params['formula_penalty_id'] = $form->getValue('formula_penalty');
            }

            if ($form->getValue('event_id') == HM_Event_EventModel::TYPE_LECTURE) {
                $params['course_id'] = $moduleId; // кэшируем id уч.модуля, чтоб потом легко найти и удалить
            }

            $lesson->setParams($params);
            $this->getService('Lesson')->update($lesson->getValues());

            $students = $form->getValue('students');
            $groupId = $form->getValue('subgroups');

            //**//
            $group = explode('_', $groupId);

            /* TODO Отписываем людей которые в ручнов выборе, если выбрана группа подгруппа? */
            if ($group[0] == 'sg' || $group[0] == 's') {
                $this->getService('Lesson')->unassignStudent($lesson->SHEID, $students);
            }

            /* Параметр Учебная группа */
            if ($group[0] == 'sg') {
                $groupId = (int) $group[1];
                $students = $this->getService('StudyGroup')->getUsers($groupId);


                /* Добавляем запись что группа подписана на урок */
                $this->getService('StudyGroupCourse')->addLessonOnGroup($subjectId, $lesson->SHEID, $groupId);
            }
            /* Параметр Подгруппа */
            if ($group[0] == 's') {
                $groupId = (int) $group[1];
                if ($groupId > 0) {
                    $students = $this->getService('GroupAssign')->fetchAll(array('gid = ?' => $groupId));

                    $res = array();
                    foreach ($students as $value) {
                        $res[] = $value->mid;
                    }
                    $students = $res;
                }
            }
            //**//

            if (!$form->getValue('switch')) {
                $students = $lesson->getService()->getAvailableStudents($subjectId);
            }

                /**
                 * тут что-то связано с вебинарами,
                 * принудительная запись модераторов и учителей в студенты - нарушает работу системы
                 * TODO: разобраться для чего это сделано и пофиксить по человечески
                 *
                if (is_array($students)) {
                    $students[] = $form->getValue('moderator');
                    $students[] = $form->getValue('teacher');
                    $students = array_unique($students);
                }
                else {
                    $students = array($form->getValue('moderator'), $form->getValue('teacher'));
                }
                 */
			$userVariants = $form->getValue('user_variant', array());
			if($userVariants !== NULL) { 
				$userVariants = array_filter($userVariants); // filter_empty// ???
			}			
            //$userVariants = array_filter($form->getValue('user_variant', array())); // filter_empty
            // Это круто кто-то закомментировал условие.....
            if ($form->getValue('assign_type', HM_Lesson_Task_TaskModel::ASSIGN_TYPE_RANDOM) == HM_Lesson_Task_TaskModel::ASSIGN_TYPE_MANUAL) {
                $students = array_keys($userVariants);
            }

            if (is_array($students) && count($students)
                    && (($this->_subject->period_restriction_type != HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL)
                    || ($this->_subject->state == HM_Subject_SubjectModel::STATE_ACTUAL ))) {
                $this->assignStudents($lesson->SHEID, $students, $userVariants);
                $lessonAssignService = $this->getService('LessonAssign');
            }

            $this->getService('Subject')->update(array(
                'last_updated' => $this->getService('Subject')->getDateTime(),
                'subid' => $subjectId
            ));
        }
    }

    protected function assignStudents($lessonId, $students, $taskUserVariants = null) {

        if (is_array($students) && count($students)) {
            $this->getService('Lesson')->assignStudents($lessonId, $students, true, $taskUserVariants);
        } else {
            $this->getService('Lesson')->unassignAllStudents($lessonId);
        }
    }

    public function getFilterByRequest(Zend_Controller_Request_Http $request) {
        /* @var $factory Es_Service_Factory */
        $factory = $this->getService('ESFactory');
        /*@var $filter Es_Entity_AbstractFilter|Es_Entity_Filter */
        $filter = $factory->newFilter();
        $filter->setUserId((int) $this->getService('User')->getCurrentUserId());
        
        $eventGroup = $factory->eventGroup(
            HM_Subject_SubjectService::EVENT_GROUP_NAME_PREFIX, $request->getParam('subject_id')
        );

        $filter->setGroup($eventGroup);
        if ($eventGroup->getId() !== null) {
            $filter->setGroupId($eventGroup->getId());
        }
        if ($filter->getGroup()->getData() === null) {
            $data = array();
            $subject = $this->getOne($this->getService('Subject')->find($request->getParam('subject_id', 0)));
            if ($subject) {
                    $data = array(
                        'course_name' => $subject->name,
                        'course_id' => $subject->subid
                    );
            }
            $filter->getGroup()->setData(json_encode($data));
        }
        $eventTypeListPushEventResult = $this->getService('EventServerDispatcher')->trigger(
            Es_Service_Dispatcher::EVENT_GET_TYPES_LIST,
            $this
        );
        $types = $eventTypeListPushEventResult->getReturnValue();
        $typesArr = array();
        $requiredTypes = array(
            Es_Entity_AbstractEvent::EVENT_TYPE_COURSE_ATTACH_LESSON,
            Es_Entity_AbstractEvent::EVENT_TYPE_LESSON_SCORE_TRIGGERED,
            Es_Entity_AbstractEvent::EVENT_TYPE_COURSE_SCORE_TRIGGERED,
            Es_Entity_AbstractEvent::EVENT_TYPE_COURSE_TASK_ACTION
        );
        foreach ($types as $eventType) {
            if (in_array($eventType->getId(), $requiredTypes)) {
                $typesArr[] = $eventType->getName();
            }
        }
        $filter->setTypes($typesArr);
        $filter->setEventId($request->getParam('eventId', null));
        
        return $filter;
    }

	
	
	//--Это старый экшен.
	//--новый экшен с рабочей машины не работает, т.е. проблема в полях - l.required и l.max_ball и, возможно, condition
	public function indexAction2() {   
try {       
	   $subjectId = (int) $this->_getParam('subject_id', 0);

        $this->view->headLink()->appendStylesheet($this->view->serverUrl() . "/css/content-modules/schedule_table.css");

// @tocheck
//         if($this->_subject->access_mode == HM_Subject_SubjectModel::MODE_FREE){
//             $this->_redirector->gotoSimple('card', 'index', 'subject', array('subject_id' => $subjectId));
//         }
        /**
         * Добавлено поле порядок - скрытое.
         * по умолчанию сортировка по порядку, потом уже в zfdatagrid меняется от выбранной пользователем.
         * @author Artem Smirnov <tonakai.personal@gmail.com>
         * @date 17 january 2012
         */
        $switcher = $this->_getParam('switcher', 0);
        if ($switcher && $switcher != 'index') {
            $this->getHelper('viewRenderer')->setNoRender();
            $action = $switcher . 'Action';
            $this->$action();
            $this->view->render('list/' . $switcher . '.tpl');
            return true;
        }
        $select = $this->getService('Lesson')->getSelect();
        $select->from(array('l' => 'lessons'), array(        
            'lesson_id' => 'l.SHEID',
            'l.SHEID',
            'TypeID2' => 'l.typeID',            					
            'l.title',			
            'l.typeID',
            'l.begin',
            'l.end',
            'l.timetype',
            'l.condition',
            'l.cond_sheid',
            'l.cond_mark',
            'l.cond_progress',
            'l.cond_avgbal',
            'l.cond_sumbal',
            'l.isfree',			
            'sort_order' => 'l.order'
                )
        );
        $select->where('CID = ?', $subjectId)
                ->where('typeID NOT IN (?)', array_keys(HM_Event_EventModel::getExcludedTypes()))
                ->where('isfree = ?', HM_Lesson_LessonModel::MODE_PLAN)
                ->order(array('sort_order'));
        if ($this->getService('User')->getCurrentUserRole() != HM_Role_RoleModelAbstract::ROLE_ADMIN) {
            // нужно разобраться и потом раскомментировать
            // этот where() от вебинаров ломает всё расписание
            //$select->where("teacher = " . $this->getService('User')->getCurrentUserId() . ' OR ' . "moderator = " . $this->getService('User')->getCurrentUserId());
            //$select->where('teacher = ?', $this->getService('User')->getCurrentUserId());
        }
		

		
        $grid = $this->getGrid($select, array(
            'sort_order' => array('order' => true, 'hidden' => true),
            'SHEID' => array('hidden' => true),
            'TypeID2' => array('hidden' => true),
            'lesson_id' => array('hidden' => true),
            'title' => array('title' => _('Название')),
            'typeID' => array('title' => _('Тип')),
            'begin' => array('title' => _('Ограничение по времени')),
            'condition' => array('title' => _('Условие')),
            'end' => array('hidden' => true),
            'timetype' => array('hidden' => true),
            'cond_sheid' => array('hidden' => true),
            'cond_mark' => array('hidden' => true),
            'cond_avgbal' => array('hidden' => true),
            'cond_sumbal' => array('hidden' => true),
            'cond_progress' => array('hidden' => true),
            'isfree' => array('hidden' => true),
                ), array(
            'title' => null,
            'typeID' => array('values' => HM_Event_EventModel::getAllTypes(false)),
            'begin' => array('render' => 'DateTimeStamp'),
            'condition' => array('values' => array('0' => _('Нет условия'), '1' => _('Есть условие')))
                )
        );

        $grid->updateColumn('typeID', array('searchType' => '='));
        $grid->addAction(
                array('module' => 'lesson', 'controller' => 'result', 'action' => 'index', 'preview' => 1), array('lesson_id'), _('Просмотр результатов')
        );

        $grid->setActionsCallback(
                array('function' => array($this, 'updateActions'),
                    'params' => array('{{TypeID2}}', '{{lesson_id}}')
                )
        );

        $grid->addAction(array(
            'module' => 'lesson',
            'controller' => 'list',
            'action' => 'edit'
                ), array('lesson_id'), $this->view->icon('edit')
        );

        $grid->addAction(array(
            'module' => 'lesson',
            'controller' => 'list',
            'action' => 'delete'
                ), array('lesson_id'), $this->view->icon('delete')
        );
        
        if ($this->getService('User')->getCurrentUserRole() == HM_Role_RoleModelAbstract::ROLE_TEACHER) {
            $grid->addMassAction(array('action' => 'delete-by'), _('Удалить'), _('Вы подтверждаете удаление отмеченных занятий? Если занятие было создано на основе информационного ресурса или учебного модуля, эти материалы вновь станут доступными всем слушателям курса в меню <Материалы курса>.'));
        }
        $grid->updateColumn('typeID', array(
            'callback' =>
            array(
                'function' => array($this, 'getTypeString'),
                'params' => array('{{typeID}}')
            )
                )
        );

        $grid->updateColumn('begin', array(
            'callback' =>
            array(
                'function' => array($this, 'getDateTimeString'),
                'params' => array('{{begin}}', '{{end}}', '{{timetype}}')
            )
                )
        );

        $grid->updateColumn('title', array(
            'callback' =>
            array(
                'function' => array($this, 'updateName'),
                'params' => array('{{title}}', '{{lesson_id}}', '{{typeID}}')
            )
                )
        );

        $grid->updateColumn('condition', array(
            'callback' =>
            array(
                'function' => array($this, 'getConditionString'),
                'params' => array('{{cond_sheid}}', '{{cond_mark}}', '{{cond_progress}}', '{{cond_avgbal}}', '{{cond_sumbal}}')
            )
                )
        );



        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid->deploy();
        $this->getService('EventServerDispatcher')->trigger(
            Es_Service_Dispatcher::EVENT_UNSUBSCRIBE,
            $this,
            array('filter' => $this->getFilterByRequest($this->getRequest()))
        ); 

} catch (Exception $e) {
			//echo 'Выброшено исключение: ',  $e->getMessage(), "\n";
		}		
    }	

    public function updateActions($typeID, $lesson_id, $actions) {
        $lesson = HM_Lesson_LessonModel::factory(array('typeID' => $typeID));

        if (!$lesson)
            return $actions;

        $aclService = $this->getService('Acl');
        $currentUser = $this->getService('User')->getCurrentUserRole();
		
		
		# накладываем условия для "Выставить оценку вручную"
		if(
			$currentUser != HM_Role_RoleModelAbstract::ROLE_TUTOR
			||
			$typeID != HM_Event_EventModel::TYPE_TEST
		){
			$tmp = explode('<li>', $actions);
            unset($tmp[2]);
            $actions = implode('<li>', $tmp);                
		}
		
		
		
		
        
        if ($lesson->isResultInTable()) {
            return $actions;
        } else {
            $tmp = explode('<li>', $actions);
            unset($tmp[1]);
            return implode('<li>', $tmp);                
            
        }

        return $actions;
    }

    public function saveOrderAction() {
        $this->getHelper('viewRenderer')->setNoRender();
        $order = $this->_getParam('posById', array());
        foreach ($order as $key => $lesson) {
            $res = $this->getService('Lesson')->updateWhere(array('order' => $key), array('SHEID = ?' => $lesson));
            if ($res === false) {
                echo Zend_Json_Encoder::encode(array('result' => false));
                exit;
            }
        }
        echo Zend_Json_Encoder::encode(array('result' => true));
    }

    public function getConditionString($condSheid, $condMark, $condProgress, $condAvg, $condSum) {
        $conditions = HM_Lesson_LessonModel::getConditionTypes();
        if ($condSheid > 0) {
            return $conditions[HM_Lesson_LessonModel::CONDITION_LESSON];
        }
        if ($condProgress > 0) {
            return $conditions[HM_Lesson_LessonModel::CONDITION_PROGRESS];
        }
        if ($condAvg > 0) {
            return $conditions[HM_Lesson_LessonModel::CONDITION_AVGBAL];
        }
        if ($condSum > 0) {
            return $conditions[HM_Lesson_LessonModel::CONDITION_SUMBAL];
        }
        return _('Нет');
    }

    public function getTypeString($typeId, $isCanMarkAlways = false, $isCanSetMark = false) {
        $types = HM_Event_EventModel::getAllTypes();
		$suffix = '';
		if($typeId == HM_Event_EventModel::TYPE_TASK){			
			$suffix = ($isCanMarkAlways) ? (' (ф&minus;)') : ('');
		}
		
		if($typeId == HM_Event_EventModel::TYPE_TEST){			
			$suffix .= ($isCanSetMark) ? (' (Р+)') : ('');
		}
		
        if (isset($types[$typeId])) {
            return $types[$typeId].$suffix;
        }
    }

    public function getDateTimeString($begin, $end, $timetype) {
        switch ($timetype) {
            case 1:
                if (($end == 0) || ($begin == 0)) {
                    $beginOrEnd = ($begin == 0) ? $end : $begin;
                    return sprintf(_('%s-й день'), floor($beginOrEnd / 60 / 60 / 24));
                } elseif ($begin != $end) {
                    return sprintf(_('%s-й день - %s-й день'), floor($begin / 60 / 60 / 24), floor($end / 60 / 60 / 24));
                } else {
                    return sprintf(_('%s-й день'), floor($begin / 60 / 60 / 24));
                }
                break;
            case 2:
                return _('Без ограничений');
                break;
            default:
                $begin = new HM_Date($begin);
                $end = new HM_Date($end);
                return sprintf('%s - %s', $begin->get(Zend_Date::DATETIME_SHORT), $end->get(Zend_Date::DATETIME_SHORT));
                break;
        }
    }

    public function myAction()
    {
        $this->view->headLink()->appendStylesheet($config->url->base.'/css/rgsu_style.css');
		$subjectId = (int) $this->_getParam('subject_id', 0);
        $student = 0;
		$serviceLesson 	= $this->getService('Lesson');
		$serviceSubject = $this->getService('Subject');
		
		$serviceUser = $this->getService('User');
		if($this->getService('Acl')->inheritsRole($serviceUser->getCurrentUserRole(), array(HM_Role_RoleModelAbstract::ROLE_TUTOR ))){
			$studentIDs = $serviceSubject->getAvailableStudents($serviceUser->getCurrentUserId(), $subjectId);
			$user_id = $this->_getParam('user_id', false);
			if($user_id && $studentIDs && !in_array($user_id, $studentIDs)){				
				$this->_helper->getHelper('FlashMessenger')->addMessage(
					array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
					'message' => _('Вы не имеете права просматривать эту страницу'))
				);
				$this->_redirect('/');
			}			
		}
		
		if(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole() == HM_Role_RoleModelAbstract::ROLE_STUDENT){
			$this->view->isStudent 	= true;
			$this->view->isDOT 		= $serviceSubject->isDOT($subjectId);	
			
			if(!$this->view->isDOT){
				
				
				$rating 					= $serviceSubject->getUserBallsSeparately($subjectId, $this->getService('User')->getCurrentUserId());
				$this->view->isMainModule 	= $rating['isMainModule'];
				$this->view->ratingMmedium 	= $rating['medium'];
				$this->view->reasonFail 	= $serviceSubject->getFailPassMessage($this->getService('User')->getCurrentUserId(), $subjectId);
				
				if($rating['isMainModule']){
					# находим причины недопуска по каждому модулю, кроме указанного в параметрах.
					$this->view->reasonFailModule 	= $serviceSubject->getFailPassMessageModule($this->getService('User')->getCurrentUserId(), $subjectId);	
					$this->view->user_id 	= $this->getService('User')->getCurrentUserId();
					$this->view->additional	= array(
						'moduleData' => $this->getService('Subject')->getModuleData($subjectId, array($this->getService('User')->getCurrentUserId())),
					);
					
					if(!empty($this->view->additional['moduleData']['rating'])){
						foreach($this->view->additional['moduleData']['rating'] as $module_subject_id => $vals){
							if($module_subject_id == $subjectId){ continue; }
							$user_balls 			= $this->getService('LessonJournalResult')->getRatingSeparated($module_subject_id, $this->view->user_id);						
							$this->view->additional['moduleData']['rating'][$module_subject_id]['medium'][$this->view->user_id] = $user_balls['medium'];						
						}
					}
					
					$this->view->ratingTotal 	= round($rating['total']);
					$user_balls 				= $this->getService('LessonJournalResult')->getRatingSeparated($subjectId, $this->view->user_id);
					$this->view->ratingMmedium 	= round($user_balls['medium']);
				}
				
				if($rating['isMainModule']){
					if(empty($this->view->reasonFail)){
						$sum_balls = round($this->view->ratingTotal + $this->view->additional['moduleData']['integrate'][$this->view->user_id]['medium']);						
						$this->view->ballTotal      = $sum_balls;				
					} else {
						$sum_balls = 0;		
						$this->view->ratingTotal = '';
					}					
					$this->view->fiveBallText	= (!empty($this->view->ratingTotal)) ? $serviceLesson->getTextFiveScaleMark($serviceLesson->getFiveScaleMark($sum_balls), $this->_subject->exam_type) : ('');
					
				} else {
				
					#$rating 					= $this->getService('LessonJournalResult')->getRatingSeparated($subjectId, $serviceUser->getCurrentUserId());
					#$this->view->ratingMmedium = round($rating['medium']);
					#$this->view->reasonFail 	= $this->getService('Subject')->getFailPassMessage($this->getService('User')->getCurrentUserId(), $subjectId);	
					if($serviceLesson->isPassMediumRating($serviceLesson->getMaxBallMediumRating($subjectId), $rating['medium'], $this->_subject->isDO) && empty($this->view->reasonFail) ){
						if($serviceLesson->isPassTotalRating($serviceLesson->getMaxBallTotalRating($subjectId), $rating['total'], $this->_subject->isDO, $this->_subject->is_practice)){
							$this->view->ratingTotal 	= $rating['total'];
							$this->view->ballTotal		= $this->view->ratingTotal + $this->view->ratingMmedium; #ceil($rating['medium'] + $rating['total']);
						}
					}
					$this->view->fiveBallText	= (!empty($this->view->ratingTotal)) ? $serviceLesson->getTextFiveScaleMark($serviceLesson->getFiveScaleMark($this->view->ballTotal), $this->_subject->exam_type) : ('');
				}
				
			}
		}		
		
		$this->isTutorOnCourse = false;
		$this->view->isTutorOnCourse = false;
		if($this->tutorOnCourse($subjectId)){
			$this->isTutorOnCourse = true;
			$this->view->isTutorOnCourse = true;
		}
		
		//var_dump($this->isTutorOnCourse);
		

        if (
            $this->getService('Acl')->inheritsRole(
                $this->getService('User')->getCurrentUserRole(),
                HM_Role_RoleModelAbstract::ROLE_ENDUSER
            )
        ) {
            $student = $this->getService('User')->getCurrentUserId();
            $this->view->setHeaderOptions(
                array(
                    'pageTitle' => _('План занятий'),
                    'panelTitle' => $this->view->getPanelShortname(
                        array(
                            'subject' => $this->_subject,
                            'subjectName' => 'subject',
                        )
                    ),
                )
            );
        }

        if (
            $this->getService('Acl')->inheritsRole(
                $this->getService('User')->getCurrentUserRole(),
                HM_Role_RoleModelAbstract::ROLE_TEACHER
            )
			
			//-------------
			||
			$this->isTutorOnCourse			
			//--------------
			
        ) {
            $student = $this->_getParam('user_id', false);
            if ($student && count($user = $this->getService('User')->find($student))) {
                $this->getService('Unmanaged')->setSubHeader(
                    $user->current()->getName()
                );
            }
        }

        if($student){
            $subSelect = $serviceLesson->getSelect();
            $subSelect->from(array('ul' => 'scheduleID'), 'SHEID')
                      ->where('MID = ?', $student);
            $addingWhere = array('SHEID IN ?' => $subSelect);
        } else {
            $addingWhere = array();
        }

        $lessons = $serviceLesson->fetchAllDependence(
        		array('Assign', 'Teacher'),
        		array(
        			'CID = ?' => $subjectId,
                    'typeID NOT IN (?)' => array_keys(
                        HM_Event_EventModel::getExcludedTypes()
                    ),
    		        'isfree = ?' => HM_Lesson_LessonModel::MODE_PLAN,
        		) + $addingWhere,
        		array('order')
        );
		
        foreach($lessons as $key => $item){
			if($item->typeID == HM_Event_EventModel::TYPE_JOURNAL_PRACTICE){
				$item->max_ball_academ  = $item->max_ball;
				$item->max_ball_practic = $this->getService('LessonJournalResult')->getPracticMaxBall($item->CID);
                $item->max_ball    += $item->max_ball_practic;
                $lessons[$key]     = $item;                
			}
		}
		
        $titles = $lessons->getList('SHEID', 'title');	
        $titles_translations = $lessons->getList('SHEID', 'title_translation');	
		
		$percent = $serviceLesson->countPercents(
            $lessons
        );
        $collection = $this->getService('SubjectMark')->fetchAll(
            $this->getService('SubjectMark')->quoteInto(
                array('cid = ?', ' AND mid = ?'),
                array(
                    $subjectId,
                    $student,
                )
            )
        );
        $this->view->mark = count($collection)
            ? $this->getOne($collection)->mark
            : HM_Scale_Value_ValueModel::VALUE_NA;

		$this->view->titles = $titles;
		$this->view->titles = $titles_translations;
        $this->view->markDisplay = (boolean) $student;
        $this->view->percent = (int) $percent;
        $this->view->lessons = $lessons;
        $this->view->subject = $this->_subject;		
	

//        @todo: Сделать нормальный view и отображать с делением на sections
//        $titles = array();
//        $this->view->sections = $this->getService('Section')->getSectionsLessons($subjectId, $addingWhere, $titles);
//        $this->view->titles = $titles;
//        $this->view->isEditSectionAllowed = $this->getService('Acl')->isCurrentAllowed('mca:lesson:list:edit-section');
        $this->view->forStudent = $student;
        /* +++ Events +++ */
        /*@var $filter Es_Entity_Filter */
        $filter = $this->getService('ESFactory')->newFilter();
        $filter->setUserId((int)$this->getService('User')->getCurrentUserId());
        $filter->setTypes(array(
            'courseAttachLesson',
            'courseTaskScoreTriggered'
        ));
        $filter->setIsGroupResultRequire(false);
        $filter->setOnlyNotShowed(false);
        $filter->setFromTime($filter->getToTime() - 5*86400);
        
        $group = $this->getService('ESFactory')->eventGroup(
            HM_Subject_SubjectService::EVENT_GROUP_NAME_PREFIX, $subjectId
        );
        $group->setData(json_encode(array(
            'course_name' => $this->_subject->name
        )));
        $filter->setGroup($group);
        
        $event = $this->getService('EventServerDispatcher')->trigger(
            Es_Service_Dispatcher::EVENT_PULL,
            $this,
            array('filter' => $filter)
        );
        $eventCollection = $event->getReturnValue();
        $this->view->eventCollection = $eventCollection;
        /* ---Events --- */
        
        $this->getService('EventServerDispatcher')->trigger(
            Es_Service_Dispatcher::EVENT_UNSUBSCRIBE,
            $this,
            array('filter' => $this->getFilterByRequest($this->getRequest()))
        );
    }

    public function newAction() {    
		//--тут тоже не работает из-за поля requaed. После обновления такая проблема. Не видит псевдоним в связях
		try {
 

		
		
        if (isset($_POST['questions_by_theme'])) {
            if (is_array($_POST['questions_by_theme']) && count($_POST['questions_by_theme'])) {
                $_POST['questions_by_theme'] = serialize($_POST['questions_by_theme']);
            }
        }
		
		$subjectId	= (int) $this->_getParam('subject_id', 0);
		$type_id	= (int) $this->_getParam('event_id', 0);
		$request = $this->getRequest();

		##		
		/*
		if($type_id == HM_Event_EventModel::TYPE_JOURNAL){			
			if ($this->_getParam('title') != '' && $this->_getParam('max_ball') != '' ) {					
				$form = new $this->_formName();
				$form->populate(array(
					'title' 	=> $this->_getParam('title', ''),
					'event_id'	=> $type_id,
					'required'	=> $this->_getParam('required', 0),
					'descript'	=> $this->_getParam('descript', ''),														
					'max_ball'	=> $this->_getParam('max_ball', 0),																			
					'GroupDate'	=> HM_Lesson_LessonModel::TIMETYPE_FREE,
				));				
			
				$this->addLesson($form);
				if ($this->view->redirectUrl = $form->getValue('redirectUrl')) {
					$this->view->subjectId = $form->getValue('subject_id');
					return true;
				} else {
					$extraMsg = (in_array($form->getValue('event_id'), HM_Lesson_LessonModel::getTypesFreeModeEnabled())) ? _('Закрыт свободный доступ к материалы курса, использованным в данном занятии') : '';
					$this->_flashMessenger->addMessage(_('Занятие успешно добавлено. ') . $extraMsg);
					if ($checkResult) {
						$this->_flashMessenger->addMessage(_('Дата проведения занятия была скорректирована так как она выходила за рамки курса'));
					}
					$this->_redirector->gotoSimple('index', $this->_controller, $this->_module, array('subject_id' => $subjectId));
				}		
			}
		}
		*/
		##

     
       
        $form = new $this->_formName();  

        if ($request->isPost() && $form->isValid($request->getPost())) {
			
			$checkResult = $this->checkLessonDates($form, $subjectId);
            $this->addLesson($form);
			$this->checkTutorAssign($subjectId); 
            if ($this->view->redirectUrl = $form->getValue('redirectUrl')) {
                $this->view->subjectId = $form->getValue('subject_id');
                return true;
            } else {
                $extraMsg = (in_array($form->getValue('event_id'), HM_Lesson_LessonModel::getTypesFreeModeEnabled())) ? _('Закрыт свободный доступ к материалы курса, использованным в данном занятии') : '';
                $this->_flashMessenger->addMessage(_('Занятие успешно добавлено. ') . $extraMsg);
                if ($checkResult) {
                    $this->_flashMessenger->addMessage(_('Дата проведения занятия была скорректирована так как она выходила за рамки курса'));
                }
                $this->_redirector->gotoSimple('index', $this->_controller, $this->_module, array('subject_id' => $subjectId));
            }
        } else {
            $form->setDefault('subject_id', $subjectId);
            $form->setDefault('GroupDate', HM_Lesson_LessonModel::TIMETYPE_FREE);
        }

        $this->view->form = $form;
        $this->view->subject = $this->getService('Subject')->getOne($this->getService('Subject')->find($subjectId));
		
		
		} catch (Exception $e) {
    //echo $e->getMessage();
}
		
		
		
		
    }

    /**
     * #7590
     * Проверка дат при создании-обновлении занятия.
     * Если курс с регламентированными датами и правит-создает занятие не автор курса,
     * то не даем выскочить за рамки курса
     * @param $form
     * @param $subjectId
     * @return bool - были или нет внесены изменения в даты занятия (TRUE-были)
     */
    private function checkLessonDates($form, $subjectId) {
        $subjectService = $this->getService('Subject');
        $subject = $subjectService->getOne($subjectService->find($subjectId));
        $result = FALSE;
        if ($subject) {
            if ($subject->period == HM_Subject_SubjectModel::PERIOD_DATES /* && $subject->author_id != $this->getService('User')->getCurrentUserId() */) {
                $beginSubject = strtotime($subject->begin_planned);
                $endSubject   = strtotime($subject->end_planned);

                if ($beginSubject || $endSubject) {
                    if ($form->getValue('beginDate') && $form->getValue('endDate')) {
                        $beginLesson  = strtotime($form->getValue('beginDate'));
                        $endLesson    = strtotime($form->getValue('endDate'));

                        if ($subject->begin_planned && ($beginLesson - $beginSubject) < 0 || ($endSubject - $beginLesson) < 0 ) {
                            $date = new HM_Date($beginSubject);
                            $form->getSubForm('step1')->getElement('beginDate')->setValue($date->get(Zend_Date::DATETIME));
                            $result = true;
                        }

                        if ($subject->end_planned && ($endSubject - $endLesson) < 0 || ($endLesson - $beginSubject) < 0 ) {
                            $date = new HM_Date($endSubject);
                            $form->getSubForm('step1')->getElement('endDate')->setValue($date->get(Zend_Date::DATETIME));
                            $result = true;
                        }
                    }

                    if ($form->getValue('currentDate')) {
                        $curLesson  = strtotime($form->getValue('currentDate'));
                        if ($beginSubject && ($curLesson - $beginSubject) < 0 || ($endSubject - $curLesson) < 0 ) {
                            $date = new HM_Date($beginSubject);
                            $form->getSubForm('step1')->getElement('currentDate')->setValue($date->get(Zend_Date::DATETIME));
                            $result = true;
                        }
                    }
                }
            }
        }

        return $result;
    }
    public function editIconAction() {
        $form = new HM_Form_Icon();
        $request = $this->getRequest();

        $subjectId = (int) $this->_getParam('subject_id', 0);
        $lessonId = (int) $this->_getParam('lesson_id', 0);

        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {
                if ($form->getValue('icon') != null) {
                    HM_Lesson_LessonService::updateIcon($lessonId, $form->getElement('icon'));
                } else {
                    HM_Lesson_LessonService::updateIcon($lessonId, $form->getElement('server_icon'));
                }
            }
            $params = array();
            $params['subject_id'] = $subjectId;
            $params['switcher'] = 'my';
            $this->_redirector->gotoSimple('index', 'list', 'lesson', $params);
            exit;
        }

        $this->view->form = $form;
    }

    public function editAction()
    {
        $subjectId = (int) $this->_getParam('subject_id', 0);
        $lessonId = (int) $this->_getParam('lesson_id', 0);
        $lesson = $this->getService('Lesson')->getOne($this->getService('Lesson')->find($lessonId));
		
		$oldMaxBall = $lesson->max_ball; # использыем при обновлении уже высталвенных оценок.
        if ($lesson) {
            $this->getService('Unmanaged')->setSubHeader($lesson->title);
        } else {
            $this->_flashMessenger->addMessage(array('message' => _('Занятие не найдено'), 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
            $this->_redirector->gotoSimple('index', $this->_controller, $this->_module, array('subject_id' => $subjectId));
        }

        $form = new $this->_formName();
        $params = $lesson->getParams();

        if ($this->_getParam('fromlist', false)) {
            $form->getSubForm('step1')
                    ->getElement('cancelUrl')
                    ->setValue($this->view->url(array('module' => 'lesson', 'controller' => 'list', 'action' => 'my', 'subject_id' => $this->_getParam('subject_id', 0), 'user_id' => intval($this->_getParam('user_id', null))), null, true) . '#lesson_' . $lessonId);
            if ($this->_getParam('user_id', false)) {
                $form->getSubForm('step1')
                        ->getElement('fromList')
                        ->setValue(intval($this->_getParam('user_id')));
            } else {
                $form->getSubForm('step1')
                        ->getElement('fromList')
                        ->setValue('y');
            }
        }

        if (isset($_POST['questions_by_theme'])) {
            if (is_array($_POST['questions_by_theme']) && count($_POST['questions_by_theme'])) {
                $_POST['questions_by_theme'] = serialize($_POST['questions_by_theme']);
            }
        }

        $request = $this->getRequest();
        if ($request->isPost() && $form->isValid($request->getPost())) {

            $activities = '';
            if (null !== $form->getValue('activities')) {
                if (is_array($form->getValue('activities')) && count($form->getValue('activities'))) {
                    $activities = serialize($form->getValue('activities'));
                }
            }
            $checkResult = $this->checkLessonDates($form, $subjectId);

            $tool = '';
            if ($form->getValue('event_id') < 0) {
                $event = $this->getOne(
                        $this->getService('Event')->find(-$form->getValue('event_id'))
                );

                if ($event) {
                    $tool = $event->tool;
                }
            }

            $typeId = $form->getValue('event_id');
            $moduleId = $form->getValue('module');
            if ($typeId == HM_Event_EventModel::TYPE_LECTURE) {
                $typeId = HM_Event_EventModel::TYPE_COURSE; // скрываем весь модуль
                $moduleId = $this->getService('CourseItem')->getCourse($moduleId);
            }
            $this->getService('Lesson')->setLessonFreeMode($moduleId, $typeId, $subjectId, HM_Lesson_LessonModel::MODE_FREE_BLOCKED);

            $lesson = $this->getService('Lesson')->update(
                    array(
                        'SHEID' => $form->getValue('lesson_id'),
                        'title' => $form->getValue('title'),
                        'title_translation' => $form->getValue('title_translation'),
                        'CID' => $form->getValue('subject_id'),
                        'typeID' => $form->getValue('event_id'),
                        'vedomost' => $form->getValue('vedomost'),
                        'teacher' => $form->getValue('teacher'),
                        'moderator' => $form->getValue('moderator'),
                        'createID' => $this->getService('User')->getCurrentUserId(),
                        'recommend' => ($form->getValue('formula_penalty', 0)) ? 1 :0,
                        'all' => $form->getValue('all'),
                        'GroupDate' => $form->getValue('GroupDate'),
                        'beginDate' => $form->getValue('beginDate'),
                        'endDate' => $form->getValue('endDate'),
                        'currentDate' => $form->getValue('currentDate'),
                        'beginTime' => $form->getValue('beginTime'),
                        'endTime' => $form->getValue('endTime'),
                        'beginRelative' => ($form->getValue('beginRelative')) ? $form->getValue('beginRelative') : 1,
                        'endRelative' => ($form->getValue('endRelative')) ? $form->getValue('endRelative') : 1,
                        'Condition' => $form->getValue('Condition'),
                        'cond_sheid' => $form->getValue('cond_sheid'),
                        'cond_mark' => ((null !== $form->getValue('cond_mark')) ? $form->getValue('cond_mark') : ''),
                        'cond_progress' => ((null !== $form->getValue('cond_progress')) ? $form->getValue('cond_progress') : 0),
                        'cond_avgbal' => ((null !== $form->getValue('cond_progress')) ? $form->getValue('cond_avgbal') : 0),
                        'cond_sumbal' => ((null !== $form->getValue('cond_sumbal')) ? $form->getValue('cond_sumbal') : 0),
                        'gid' => $form->getValue('subgroups'),
                        'notice' => $form->getValue('notice'),
                        'notice_days' => (int) $form->getValue('notice_days'),
                        'activities' => $activities,
                        'descript' => $form->getValue('descript'),
                        'descript_translation' => $form->getValue('descript_translation'),
                        'tool' => $tool,
                        'allowTutors' => $form->getValue('allowTutors'),
                        'max_ball' => $form->getValue('max_ball', 0),
                        'required' => $form->getValue('required', 0),
						'isCanMarkAlways' => $form->getValue('isCanMarkAlways'),
						'isCanSetMark' 		=> $form->getValue('isCanSetMark'),
						'max_ball_academic' => $form->getValue('max_ball_academic'),
						'max_ball_practic' 	=> $form->getValue('max_ball_practic'),
                    )
            );

            if ($lesson) {
                $this->_postProcess($lesson, $form);

                if ($form->getValue('module')) {
                    $params['module_id'] = $form->getValue('module');
                }

                if ($form->getValue('assign_type')) {
                    $params['assign_type'] = $form->getValue('assign_type');
                } elseif (isset($params['assign_type']) && $params['assign_type']) {
                    unset($params['assign_type']);
                }

                if ($form->getValue('is_hidden', 0)) {
                    $params['is_hidden'] = $form->getValue('is_hidden');
                } elseif (isset($params['is_hidden']) && $params['is_hidden']) {
                    unset($params['is_hidden']);
                }

                if ($form->getValue('formula')) {
                    $params['formula_id'] = $form->getValue('formula');
                } elseif (isset($params['formula_id'])) {
                    unset($params['formula_id']);
                }

                if ($form->getValue('formula_group')) {
                    $params['formula_group_id'] = $form->getValue('formula_group');
                }

                if ($form->getValue('formula_penalty')) {
                    $params['formula_penalty_id'] = $form->getValue('formula_penalty');
                }

                if ($form->getValue('event_id') == HM_Event_EventModel::TYPE_LECTURE) {
                    $params['course_id'] = $moduleId; // кэшируем id уч.модуля, чтоб потом легко найти и удалить
                }

                $lesson->setParams($params);

                $this->getService('Lesson')->update($lesson->getValues());

                $students = $form->getValue('students');

                $groupId = $form->getValue('subgroups');

                //**//
                $group = explode('_', $groupId);

                /* TODO Отписываем людей которые в ручнов выборе, если выбрана группа подгруппа? Помоему туда постоянно поподает 0 */
                if ($group[0] == 'sg' || $group[0] == 's') {
                    $this->getService('Lesson')->unassignStudent($lesson->SHEID, $students);
                    $this->getService('StudyGroupCourse')->removeLessonFromGroups($subjectId, $lesson->SHEID);
                }
                /* Параметр Учебная группа */
                if ($group[0] == 'sg') {
                    $groupId = (int) $group[1];
                    $students = $this->getService('StudyGroup')->getUsers($groupId);
                    /* Добавляем запись что группа подписана на урок */
                    $this->getService('StudyGroupCourse')->addLessonOnGroup($subjectId, $lesson->SHEID, $groupId);
                }
                /* Параметр Подгруппа */
                if ($group[0] == 's') {
                    $groupId = (int) $group[1];
                    if ($groupId > 0) {
                        $students = $this->getService('GroupAssign')->fetchAll(array('gid = ?' => $groupId));

                        $res = array();
                        foreach ($students as $value) {
                            $res[] = $value->mid;
                        }
                        $students = $res;
                    }
                }

                //**//
//
//                if($groupId > 0){
//                    $this->getService('Lesson')->unassignStudent($lesson->SHEID, $students);
//
//                    $students = $this->getService('GroupAssign')->fetchAll(array('gid = ?' => $groupId));
//
//                    $res = array();
//                    foreach($students as $value){
//                        $res[] = $value->mid;
//                    }
//                    $students = $res;
//                }
//


                if (!$form->getValue('switch')) {
                    $students = $lesson->getService()->getAvailableStudents($subjectId);
                }

                /**
                 * тут что-то связано с вебинарами,
                 * принудительная запись модераторов и учителей в студенты - нарушает работу системы
                 * TODO: разобраться для чего это сделано и пофиксить по человечески
                 *
                if (is_array($students)) {
                    if ($form->getValue('moderator') !== null)
                        $students[] = $form->getValue('moderator');
                    $students[] = $form->getValue('teacher');
                    $students = array_unique($students);
                }
                else {
                    $students = array($form->getValue('moderator'), $form->getValue('teacher'));
                }
                 */

				$userVariants = $form->getValue('user_variant', array());
				if($userVariants !== NULL) { 
					$userVariants = array_filter($userVariants); // filter_empty// ???
				}
                //$userVariants = array_filter($form->getValue('user_variant', array())); // filter_empty

                if ($form->getValue('assign_type', HM_Lesson_Task_TaskModel::ASSIGN_TYPE_RANDOM) == HM_Lesson_Task_TaskModel::ASSIGN_TYPE_MANUAL) {
                    $students = array_keys($userVariants);
                }

                //$this->getService('Lesson')->assignStudents($lesson->SHEID, $students);
                if ((($this->_subject->period_restriction_type != HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL)
                        || ($this->_subject->state == HM_Subject_SubjectModel::STATE_ACTUAL ))) {
                    $this->assignStudents($lesson->SHEID, $students, $userVariants);
                }
				
				
				$this->updateExistingBall($lesson, $oldMaxBall);
				$this->checkTutorAssign($subjectId);
				
            }
            if ($this->view->redirectUrl = $form->getValue('redirectUrl')) {
                $this->view->cancelUrl = $form->getValue('cancelUrl');
                return true;
            } else {
                $this->_flashMessenger->addMessage(_('Занятие успешно изменено'));
                if ($checkResult) {
                    $this->_flashMessenger->addMessage(_('Дата проведения занятия была скорректирована так как она выходила за рамки курса'));
                }

                if ($form->getValue('fromList')) {
                    $url = array(
                        'module' => 'lesson',
                        'controller' => 'list',
                        'action' => 'my',
                        'subject_id' => $lesson->CID
                    );
                    if ($form->getValue('fromList') == strval(intval($form->getValue('fromList')))) {
                        $url['user_id'] = $form->getValue('fromList');
                    }
                    $this->_redirector->gotoUrl($this->view->url($url, null, true) . '#lesson_' . $lessonId);
                }

                $this->_redirector->gotoSimple('index', $this->_controller, $this->_module, array('subject_id' => $lesson->CID));
            }
        } else {
            if ($lessonId) {
                if ($lesson) {
                    $values = array(
                        'lesson_id' => $lesson->SHEID,
                        'title' => $lesson->title,
                        'subject_id' => $lesson->CID,
                        'event_id' => $lesson->typeID,
                        'vedomost' => $lesson->vedomost,
                        'teacher' => $lesson->teacher,
                        'moderator' => $lesson->moderator,
                        'recommend' => $lesson->recommend,
                        'all' => $lesson->all,
                        'module' => $lesson->getModuleId(),
                        'formula' => $lesson->getFormulaId(),
                        'formula_group' => $lesson->getFormulaGroupId(),
                        'formula_penalty' => $lesson->getFormulaPenaltyId(),
                        'cond_sheid' => $lesson->cond_sheid,
                        'cond_mark' => $lesson->cond_mark,
                        'cond_progress' => $lesson->cond_progress,
                        'cond_avgbal' => $lesson->cond_avgbal,
                        'cond_sumbal' => $lesson->cond_sumbal,
                        'gid' => $lesson->gid,
                        'notice' => $lesson->notice,
                        'notice_days' => $lesson->notice_days,
                        'descript' => $lesson->descript,
                        'assign_type' => (isset($params['assign_type'])) ? (int) $params['assign_type'] : HM_Lesson_Task_TaskModel::ASSIGN_TYPE_RANDOM,
                        'section_id' => $lesson->section_id,
                        'allowTutors' => $lesson->allowTutors,
                        'max_ball' => $lesson->max_ball,
                        'required' => $lesson->required,
						'isCanMarkAlways' => $lesson->isCanMarkAlways,
						'isCanSetMark' 		=> $lesson->isCanSetMark,
						'max_ball_academic' => $lesson->max_ball_academic,
						'max_ball_practic' => $lesson->max_ball_practic,
                    );

                    if ($lesson->activities && strlen($lesson->activities)) {
                        $values['activities'] = unserialize($lesson->activities);
                    }

                    if ($lesson->cond_sheid) {
                        $values['Condition'] = HM_Lesson_LessonModel::CONDITION_LESSON;
                    }

                    if ($lesson->cond_progress) {
                        $values['Condition'] = HM_Lesson_LessonModel::CONDITION_PROGRESS;
                    }

                    if ($lesson->cond_avgbal) {
                        $values['Condition'] = HM_Lesson_LessonModel::CONDITION_AVGBAL;
                    }

                    if ($lesson->cond_sumbal) {
                        $values['Condition'] = HM_Lesson_LessonModel::CONDITION_SUMBAL;
                    }

                    if ($lesson->gid != 0 && $lesson->gid != -1) {
                        $values['switch'] = 2;
                    } else {
                        $values['switch'] = 1;
                    }

                    switch ($lesson->timetype) {
                        case HM_Lesson_LessonModel::TIMETYPE_RELATIVE:
                            $values['GroupDate'] = HM_Lesson_LessonModel::TIMETYPE_RELATIVE;
                            $values['beginRelative'] = floor($lesson->startday / 24 / 60 / 60);
                            $values['endRelative'] = floor($lesson->stopday / 24 / 60 / 60);
                            break;
                        case HM_Lesson_LessonModel::TIMETYPE_FREE:
                            $values['GroupDate'] = HM_Lesson_LessonModel::TIMETYPE_FREE;
                            break;
                        default:
                            $values['beginDate'] = $lesson->getBeginDate();
                            $values['endDate'] = $lesson->getEndDate();
                            $values['GroupDate'] = HM_Lesson_LessonModel::TIMETYPE_DATES;
                            if ($values['beginDate'] == $values['endDate']) {
                                $values['GroupDate'] = HM_Lesson_LessonModel::TIMETYPE_TIMES;
                                $values['currentDate'] = $values['beginDate'];
                                $values['beginTime'] = $lesson->getBeginTime();
                                $values['endTime'] = $lesson->getEndTime();
                                unset($values['beginDate']);
                                unset($values['endDate']);
                            }
                            break;
                    }

                    switch ($lesson->getType()) {
                        case HM_Event_EventModel::TYPE_DEAN_POLL_FOR_LEADER:
                        case HM_Event_EventModel::TYPE_DEAN_POLL_FOR_TEACHER:
                        case HM_Event_EventModel::TYPE_DEAN_POLL_FOR_STUDENT:
                        case HM_Event_EventModel::TYPE_TASK:
                        case HM_Event_EventModel::TYPE_POLL:
                        case HM_Event_EventModel::TYPE_TEST:
                            $test = $this->getOne($this->getService('Test')->fetchAll(
                                            $this->getService('Test')->quoteInto('lesson_id = ?', $lesson->SHEID)
                                    ));
                            if ($test) {
                                // Набить форму данными теста
                                $values['mode'] = $test->mode;
                                $values['lim'] = $test->lim;
                                $values['qty'] = $test->qty;
                                $values['startlimit'] = $test->startlimit;
                                $values['limitclean'] = $test->limitclean;
                                $values['timelimit'] = $test->timelimit;
                                $values['random'] = $test->random;
                                //$values['adaptive'] = $test->adaptive;
                                $values['questres'] = $test->questres;
                                $values['showurl'] = $test->showurl;
                                $values['endres'] = $test->endres;
                                $values['skip'] = $test->skip;
                                $values['allow_view_url'] = $test->allow_view_url;
                                $values['allow_view_log'] = $test->allow_view_log;
                                $values['comments'] = $test->comments;
                                $values['module'] = $test->test_id;
                                $values['threshold'] = $test->threshold;

                                $theme = $this->getOne($this->getService('TestTheme')->fetchAll(
                                                $this->getService('TestTheme')->quoteInto(
                                                        array('tid = ?', ' AND cid = ?'), array($test->tid, $test->cid)
                                                )
                                        ));

                                if ($theme && count($theme->getQuestionsByThemes())) {
                                    $values['questions'] = HM_Test_TestModel::QUESTIONS_BY_THEMES_SPECIFIED;
                                }

                                if ($test->adaptive) {
                                    $values['questions'] = HM_Test_TestModel::QUESTIONS_ADAPTIVE;
                                }
                            }

                            break;
                    }

                    $form->setDefaults($values);
                    switch ($lesson->getType()) {
                        case HM_Event_EventModel::TYPE_LECTURE:
                            // Инициализация treeSelect
                            if ($form->getSubForm('step2')->getElement('module')) {
                                $parentId = 'course_' . $lesson->CID;
                                $parent = $this->getService('Course')->getParentItem($lesson->getModuleId());
                                if ($parent) {
                                    $parentId = $parent->oid;
                                }
                                $form->getSubForm('step2')->getElement('module')->jQueryParams['itemId'] = $parentId;
                            }
                            break;
                    }
                }
            }
        }
        $this->view->form = $form;
        $this->view->subject = $this->getService('Subject')->getOne($this->getService('Subject')->find($subjectId));
    }

    public function redirectDialogAction() {
        $this->view->redirectUrl = $this->_getParam('redirectUrl');
    }

    public function deleteAction() {
        $subjectId = (int) $this->_getParam('subject_id', 0);
        $lessonId = (int) $this->_getParam('lesson_id', 0);
        $switcher = $this->_getParam('switcher', 0);

        if ($lessonId) {
            $this->getService('Lesson')->delete($lessonId);
        }

        $this->_flashMessenger->addMessage(_('Занятие успешно удалено'));

        $params = array(
            'subject_id' => $subjectId
        );

        if ($switcher) {
            $params['switcher'] = $switcher;
        }

        $this->_redirector->gotoSimple('index', $this->_controller, $this->_module, $params);
    }

    public function deleteByAction() {
        $subjectId = (int) $this->_getParam('subject_id', 0);

        $lessonIds = $this->_request->getParam('postMassIds_grid');
        $lessonIds = explode(',', $lessonIds);

        if (is_array($lessonIds) && count($lessonIds)) {
            foreach ($lessonIds as $id) {
                $this->getService('Lesson')->delete($id);
            }
        }

        $this->_flashMessenger->addMessage(_('Занятия успешно удалены'));
        $this->_redirector->gotoSimple('index', $this->_controller, $this->_module, array('subject_id' => $subjectId));
    }

    #public function updateName($field, $translation='', $id, $type) {
    public function updateName($field, $id, $type) {
		
		$request = Zend_Controller_Front::getInstance()->getRequest();
		$this->_currentLang = $request->getCookie(HM_User_UserService::COOKIE_NAME_LANG);
		
		if($translation != '' && $this->_currentLang == 'eng')
			$field = $translation;
			
        if ($type == HM_Event_EventModel::TYPE_COURSE) {

            $lesson = $this->getService('Lesson')->getOne($this->getService('Lesson')->find($id));

            $courseId = $lesson->getModuleId();

            $course = $this->getService('Course')->getOne($this->getService('Course')->find($courseId));

            if ($course->new_window == 1) {
                $itemId = $this->getService('CourseItemCurrent')->getCurrent($this->getService('User')->getCurrentUserId(), $this->_getParam('subject_id', 0), $courseId);
                if ($itemId != false) {
                    return '<a href="' . $this->view->url(array('module' => 'course', 'controller' => 'item', 'action' => 'view', 'course_id' => $courseId, 'item_id' => $itemId)) . '" target = "_blank">' . $field . '</a>';
                }
            }
        }
        if ($type == HM_Event_EventModel::TYPE_TASK
            && $this->getService('Acl')->inheritsRole(
                $this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_TEACHER
            )
        ) {
            $url = $this->view->url(
                array(
                    'module' => 'lesson',
                    'controller' => 'result',
                    'action' => 'extended',
                    'lesson_id' => $id,
                    'subject_id' => $this->_getParam('subject_id')
                )
            );
            return '<a href="' . $url . '" title="' . _('Просмотр занятия') . '">' . $field . '</a>';
        }

        $target = ($type == HM_Event_EventModel::TYPE_WEBINAR) ? ' target="_blank" ' : '';

//        $lesson = HM_Lesson_LessonModel::factory(array('typeID' => $type));
//        if ( $lesson->isResultInTable() || $type == HM_Event_EventModel::TYPE_TASK) {
//        	// хак для тестов
//        	if ($type == HM_Event_EventModel::TYPE_TEST) {
//        		return '<a href="' . $this->view->url(array('module' => 'subject', 'controller' => 'index', 'action' => 'result', 'lesson_id' =>$id, 'subject_id' => $this->_getParam('subject_id'))) . '">'. $field.'</a>';
//        	}
//
//            return '<a href="' . $this->view->url(array('module' => 'lesson', 'controller' => 'result', 'action' => 'index', 'lesson_id' =>$id, 'subject_id' => $this->_getParam('subject_id'))). '" title="' . _('Просмотр общих результатов') . '">'. $field.'</a>';
//        }
        return '<a href="' . $this->view->url(array('module' => 'lesson', 'controller' => 'execute', 'action' => 'index', 'lesson_id' => $id, 'subject_id' => $this->_getParam('subject_id'))) . '" title="' . _('Просмотр занятия') . '"' . $target . '>' . $field . '</a>';
    }
    
    public function updateRequired($field, $isBrs) {
        if($isBrs){
            return $field == 0 ? _('Нет') :  _('Да');          
        } else {
            return '-';
        }
    }
    
    public function updateMaxBall($field, $isBrs, $max_ball_academic = NULL, $max_ball_practic = NULL) {
        if($isBrs){
			$str = $field;			
			/*
			if(!empty($max_ball_academic) || !empty($max_ball_practic)){
				$str .= ' ('.floatval($max_ball_academic).'/'.floatval($max_ball_practic).')'; 
				if($field != ($max_ball_academic+$max_ball_practic)){
					$str = '<p style="color:red;">'.$str.'</p>';
				}
			}
			*/
            return $str;     
        } else {
            return '-';
        }
    }

    private function _postProcess(HM_Lesson_LessonModel $lesson, Zend_Form $form) {
        switch ($lesson->getType()) {
            case HM_Event_EventModel::TYPE_DEAN_POLL_FOR_LEADER:
            case HM_Event_EventModel::TYPE_DEAN_POLL_FOR_STUDENT:
            case HM_Event_EventModel::TYPE_DEAN_POLL_FOR_TEACHER:
            case HM_Event_EventModel::TYPE_POLL:
                $abstract = $this->getOne($this->getService('Poll')->find($form->getValue('module')));
                $this->_postProcessTest($abstract, $lesson, $form);
                break;
            case HM_Event_EventModel::TYPE_TASK:
                $abstract = $this->getOne($this->getService('Task')->find($form->getValue('module')));
                $this->_postProcessTest($abstract, $lesson, $form);
                break;
            case HM_Event_EventModel::TYPE_TEST:
                $abstract = $this->getOne($this->getService('TestAbstract')->find($form->getValue('module')));
                $this->_postProcessTest($abstract, $lesson, $form);
                break;
			case HM_Event_EventModel::TYPE_LANGUAGE:
                $abstract = $this->getOne($this->getService('Task')->find($form->getValue('module')));
				$this->_postProcessTest($abstract, $lesson, $form);
                break;
            default:
                $this->getService('Test')->deleteBy($this->getService('Test')->quoteInto('lesson_id = ?', $lesson->SHEID));

                $activities = HM_Activity_ActivityModel::getActivityServices();
                if (isset($activities[$lesson->typeID])) {
                    $activityService = HM_Activity_ActivityModel::getActivityService($lesson->typeID);
                    if (strlen($activityService)) {
                        $service = $this->getService($activityService);
                        if ($service instanceof HM_Service_Schedulable_Interface) {
                            $service->onLessonUpdate($lesson, $form);
                        }
                    }
                }
        }
    }

    private function _postProcessTest($abstractTest, HM_Lesson_LessonModel $lesson, Zend_Form $form) {
        if ($abstractTest) {

            $test = $this->getOne($this->getService('Test')->fetchAll(
                            $this->getService('Test')->quoteInto(
                                    array('lesson_id = ?', ' AND test_id = ?'), array($lesson->SHEID, $form->getValue('module'))
                            )
                    ));

            if ($test) {
                // assign values
                $test->test_id = $form->getValue('module');
                $test->title = $lesson->title;
                $test->data = $abstractTest->data;
                $test->mode = $form->getValue('mode');
                $test->lim = $form->getValue('lim');
                $test->qty = $form->getValue('qty');
                $test->startlimit = $form->getValue('startlimit');
                $test->limitclean = $form->getValue('limitclean');
                $test->timelimit = $form->getValue('timelimit');
                $test->random = $form->getValue('random');
                $test->adaptive = (int) ($form->getValue('questions') == HM_Test_TestModel::QUESTIONS_ADAPTIVE);
                $test->questres = $form->getValue('questres');
                $test->showurl = $form->getValue('showurl');
                $test->endres = $form->getValue('endres');
                $test->skip = $form->getValue('skip');
                $test->allow_view_log = $form->getValue('allow_view_log');
                $test->comments = '';
                $test->type = $abstractTest->getTestType();
                $test->threshold = $form->getValue('threshold');

                $test = $this->getService('Test')->update($test->getValues());
            } else {
                $this->getService('Test')->deleteBy(
                        $this->getService('Test')->quoteInto(
                                array('lesson_id = ?'), array($lesson->SHEID)
                        )
                );
                $test = $this->getService('Test')->insert(
                        array(
                            'cid' => $lesson->CID,
                            'datatype' => 1,
                            'sort' => 0,
                            'free' => 0,
                            'rating' => 0,
                            'status' => 1,
                            'last' => 0,
                            'cidowner' => $lesson->CID,
                            'title' => $lesson->title,
                            'data' => $abstractTest->data,
                            'lesson_id' => $lesson->SHEID,
                            'test_id' => $form->getValue('module'),
                            'mode' => $form->getValue('mode'),
                            'lim' => $form->getValue('lim'),
                            'qty' => $form->getValue('qty'),
                            'startlimit' => $form->getValue('startlimit'),
                            'limitclean' => $form->getValue('limitclean'),
                            'timelimit' => $form->getValue('timelimit'),
                            'random' => $form->getValue('random'),
                            'adaptive' => (int) ($form->getValue('questions') == HM_Test_TestModel::QUESTIONS_ADAPTIVE),
                            'questres' => $form->getValue('questres') !== null ? $form->getValue('questres') : 0,
                            'showurl' => $form->getValue('showurl') !== null ? $form->getValue('showurl') : 0,
                            'endres' => $form->getValue('endres') !== null ? $form->getValue('endres') : 0,
                            'skip' => $form->getValue('skip'),
                            'allow_view_log' => $form->getValue('allow_view_log'),
                            'comments' => $form->getValue('comments'),
                            'type' => $abstractTest->getTestType(),
                            'threshold' => $form->getValue('threshold'),
                        )
                );
            }


            if ($test) {

                $this->getService('TestTheme')->deleteBy(
                        $this->getService('TestTheme')->quoteInto(
                                array('tid = ?', ' AND cid = ?'), array($test->tid, $test->cid)
                        )
                );

                if ($form->getValue('questions') == HM_Test_TestModel::QUESTIONS_BY_THEMES_SPECIFIED) {
                    $this->getService('TestTheme')->insert(
                            array(
                                'tid' => $test->tid,
                                'cid' => $test->cid,
                                'questions' => $form->getValue('questions_by_theme')
                            )
                    );
                }

                $form->setDefault('module', $test->tid);
            }
        }
    }

    public function themesAction() {
        $lessonId = (int) $this->_getParam('lesson_id', 0);
        $testId = (int) $this->_getParam('test_id', 0);
        $subjectId = (int) $this->_getParam('subject_id', 0);

        $themes = array(''=>0);//_('Без темы') => 0);
        // Делаем выборку всех тем всех вопросоа теста, в т.ч. и "пустую"
        if ($testId) {
            $test = $this->getOne($this->getService('TestAbstract')->find($testId));
            if ($test) {
                $questions = $test->getQuestionsIds();
                if (count($questions)) {
                    $collection = $this->getService('Question')->fetchAll(
                            $this->getService('Question')->quoteInto('kod IN (?)', $questions), 'qtema'
                    );
                    if (count($collection)) {
                        foreach ($collection as $question) {
                            if (!isset($themes[$question->qtema])) {
                                $themes[$question->qtema] = 0;
                            }
                        }
                    }
                }
            }
        }

        if (count($themes) == 1) {
            $themes = array();
        } else {
            if ($lessonId) {
                $test = $this->getOne($this->getService('Test')->fetchAll(
                                $this->getService('Test')->quoteInto('lesson_id = ?', $lessonId)
                        ));
                if ($test) {
                    $theme = $this->getOne($this->getService('TestTheme')->fetchAll(
                                    $this->getService('TestTheme')->quoteInto(
                                            array('tid = ?', ' AND cid = ?'), array($test->tid, $subjectId)
                                    )
                            ));
                    if ($theme) {
                        $questionsByThemes = $theme->getQuestionsByThemes();
                        if (is_array($questionsByThemes) && count($questionsByThemes)) {
                            foreach ($questionsByThemes as $theme => $count) {
                                $theme = $theme?$theme:''; // В базе "без темы" - это 0
                                if (isset($themes[$theme])) {
                                    $themes[$theme] = $count;
                                }
                            }
                        }
                    }
                }
            }
        }
        $this->view->themes = $themes;
    }

    public function orderSectionAction() {
        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getHelper('viewRenderer')->setNoRender();

        $sectionId = $this->_getParam('section_id', array());
        $materials = $this->_getParam('material', array());
        echo $this->getService('Section')->setLessonsOrder($sectionId, $materials) ? 1 : 0;
    }

    public function myProgressAction()
    {
        $subjectId = (int) $this->_getParam('subject_id', 0);
        $student = $this->getService('User')->getCurrentUserId();
		$serviceLesson 	= $this->getService('Lesson');
		$serviceSubject = $this->getService('Subject');

        $this->view->setHeaderOptions(
            array(
                'pageTitle' => _('Прогресс изучения курса'),
                'panelTitle' => $this->view->getPanelShortname(
                        array(
                            'subject' => $this->_subject,
                            'subjectName' => 'subject',
                        )
                    ),
            )
        );
		
		
		
		if(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole() == HM_Role_RoleModelAbstract::ROLE_STUDENT){
			$this->view->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/rgsu_style.css');
			$this->view->isStudent = true;
			$this->view->isDOT 			= $serviceSubject->isDOT($subjectId);	
		
		
		
			if(!$this->view->isDOT){
				$rating 					= $serviceSubject->getUserBallsSeparately($subjectId, $student);
				$this->view->isMainModule 	= $rating['isMainModule'];
				$this->view->ratingMmedium 	= $rating['medium'];
				$this->view->reasonFail 	= $serviceSubject->getFailPassMessage($this->getService('User')->getCurrentUserId(), $subjectId);
				
				if($rating['isMainModule']){
					# находим причины недопуска по каждому модулю, кроме указанного в параметрах.
					$this->view->reasonFailModule 	= $serviceSubject->getFailPassMessageModule($this->getService('User')->getCurrentUserId(), $subjectId);	
					$this->view->user_id 	= $this->getService('User')->getCurrentUserId();
					$this->view->additional	= array(
						'moduleData' => $this->getService('Subject')->getModuleData($subjectId, array($this->getService('User')->getCurrentUserId())),
					);
					if(!empty($this->view->additional['moduleData']['rating'])){
						foreach($this->view->additional['moduleData']['rating'] as $module_subject_id => $vals){
							if($module_subject_id == $subjectId){ continue; }
							$user_balls 			= $this->getService('LessonJournalResult')->getRatingSeparated($module_subject_id, $this->view->user_id);						
							$this->view->additional['moduleData']['rating'][$module_subject_id]['medium'][$this->view->user_id] = $user_balls['medium'];						
						}
					}
					
					
					$this->view->ratingTotal 	= round($rating['total']);
					$user_balls 				= $this->getService('LessonJournalResult')->getRatingSeparated($subjectId, $this->view->user_id);
					$this->view->ratingMmedium 	= round($user_balls['medium']);
				}				
				
				# Если это главный модуль, то $this->view->ratingMmedium находим, как интегральный.
				/*
				if($serviceSubject->isMainModule($subjectId)){
					$subject 				= $serviceSubject->getById($subjectId);							
					$integrateMediumRating	= $serviceSubject->getIntegrateMediumRating($subject->module_code, $student);
					$this->view->ratingMmedium = $integrateMediumRating;
					
					# получить причины недопуска по доп. модулям
					
					
					
				}
				*/
				
				#if($this->getService('User')->getCurrentUserId() == 57308 && $subjectId == 14367){
				#	echo '.<div style="display:none">';
				#	var_dump(1);
				#	echo '</div>';
				#}
				
				
				if($rating['isMainModule']){
					if(empty($this->view->reasonFail)){
						$sum_balls = round($this->view->ratingTotal + $this->view->additional['moduleData']['integrate'][$this->view->user_id]['medium']);						
						$this->view->ballTotal      = $sum_balls;				
					} else {
						$sum_balls = 0;		
						$this->view->ratingTotal = '';
					}					
					$this->view->fiveBallText	= (!empty($this->view->ratingTotal)) ? $serviceLesson->getTextFiveScaleMark($serviceLesson->getFiveScaleMark($sum_balls), $this->_subject->exam_type) : ('');
					
				} else {
				
				
					# Если есть причины недопуска, и неважно, что по сумме баллов проходит
					if($serviceLesson->isPassMediumRating($serviceLesson->getMaxBallMediumRating($subjectId), $rating['medium'], $this->_subject->isDO) && empty($this->view->reasonFail) ){
						
						#if($this->getService('User')->getCurrentUserId() == 57308 && $subjectId == 14367){
						#	echo '.<div style="display:none">';
						#	var_dump($rating['total'], $serviceLesson->getMaxBallTotalRating($subjectId));
						#	echo '</div>';
						#}
						
						if($serviceLesson->isPassTotalRating($serviceLesson->getMaxBallTotalRating($subjectId), $rating['total'], $this->_subject->isDO, $this->_subject->is_practice)){
							
							
							#if($this->getService('User')->getCurrentUserId() == 57308 && $subjectId == 14367){
							#	echo '.<div style="display:none">';
							#	var_dump(3);
							#	echo '</div>';
							#}
							
							$this->view->ratingTotal 	= $rating['total'];
							$this->view->ballTotal		= $this->view->ratingTotal + $this->view->ratingMmedium; #ceil($rating['medium'] + $rating['total']);
						}
					}
					
					$this->view->fiveBallText	= (!empty($this->view->ratingTotal)) ? $serviceLesson->getTextFiveScaleMark($serviceLesson->getFiveScaleMark($this->view->ballTotal), $this->_subject->exam_type) : ('');
				}
				
			}			
			
		}
		
		


        $strategyService = $this->getService('MarkStrategyFactory')->getStrategy($this->_subject->getMarkType());
        $subjectProgressData = $strategyService->getSubjectProgressData($student, $subjectId);
/*
        $subSelect = $this->getService('LessonAssign')->getSelect();
        $subSelect->from(array('ul' => 'scheduleID'), 'SHEID')
            ->where('MID = ?', $student);
        $lessons = $this->getService('Lesson')->fetchAll(
                array('CID = ?'=>$subjectId, 'SHEID IN ?' => $subSelect)
            );
        foreach ($lessons as $lesson) {
            if ($lesson->required) {
                $section->lessons []= $lesson;
            }
            else {
                $sectionAlternate->lessons []= $lesson;
            }
        }*/

        //$titles = $lessons->getList('SHEID', 'title');

        $mark = $this->getOne($this->getService('SubjectMark')->fetchAll(
            $this->getService('SubjectMark')->quoteInto(
                array('CID = ?', ' AND MID = ?'), array(
                    $subjectId,
                    $this->getService('User')->getCurrentUserId(),
                )
            )
        ));

        $view = $this->view;

        $view->mark = ($mark->mark) ? $mark->mark : -1;

        //$view->titles = $titles;
        $view->sections = $subjectProgressData['sections'];
        unset($subjectProgressData['sections']);
        if ($subjectProgressData['markAllowed'] &&
            ($subjectProgressData['currentScore'] < $this->_subject->threshold)) {
            $subjectProgressData['markAllowed'] = false;
        }
        $view->markDisplay = (boolean) $student;
        //$view->lessons = $lessons;
        $view->subject = $this->_subject;
        $view->subjectProgressData = $subjectProgressData;
//        @todo: Сделать нормальный view и отображать с делением на sections
//        $titles = array();
//        $this->view->sections = $this->getService('Section')->getSectionsLessons($subjectId, $addingWhere, $titles);
//        $this->view->titles = $titles;
//        $this->view->isEditSectionAllowed = $this->getService('Acl')->isCurrentAllowed('mca:lesson:list:edit-section');


        //$this->view->sections = $this->getService('Section')->getSectionsLessons($subjectId, $addingWhere, $titles);
        $view->forStudent = $student;
        $view->subject = $this->_subject;
        $view->isStudentRole = $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_STUDENT);
		
    }

    public function viewTreeAction() {
        $this->view->headScript()->appendFile($this->view->serverUrl('/js/lib/jit/jit.js'));
        $this->view->headLink()->appendStylesheet($this->view->serverUrl('/js/lib/jit/css/base.css'));
        $this->view->headLink()->appendStylesheet($this->view->serverUrl('/js/lib/jit/css/Spacetree.css'));
        $subjectId = $this->_getParam('subject_id', 0);
        $userId = $this->getService('User')->getCurrentUserId();
        $subject = $this->getService('Subject')->find($subjectId)->current();
        $factory = $this->getService('MarkStrategyFactory')->getStrategy($subject->getMarkType());
        $lessonAssigns = $factory->getLessonAssign($userId, $subjectId);
        $childrenArray = array();
        $childrenArray[1] = array(
            'id' => 1,
            'name' => _('Старт'),
            'data' => array(),
            'parent' => 0
        );
        foreach ($lessonAssigns as $lessonAssign) {
            $lesson = $lessonAssign->lessons->current();
            $childrenArray[(int)$lesson->SHEID] = array(
                'id' => (int)$lesson->SHEID,
                'name' => _($lesson->title),
                'data' => array(),
                'parent' => ($lesson->cond_sheid > 0) ? (int)$lesson->cond_sheid : 1
            );
        }

        $rootID = 0;
        foreach ($childrenArray as $id => $node) {
            if ($node['parent']) {
                $childrenArray[$node['parent']]['children'][] =& $childrenArray[$id];
            }
            else {
                $rootID = $id;
            }
        }
        //pr($childrenArray);die;
        $this->view->data = json_encode($childrenArray[1]);
    }
	
	
	/**
	 * - проверяет принадлежность пользователя к тьюторам данного курса.
	 * return bool
	*/	
	public function tutorOnCourse($courseId){
		
		if(!$courseId) {
			return false;
		}
		
		$uid = $this->getService('User')->getCurrentUserId();
		
		if(!$uid) {
			return false;
		}
	
		$select = $this->getService('User')->getSelect();
		$select->from(
			array(
				'Tutors'
			),
			array(
				'total' => new Zend_Db_Expr("COUNT(TID)"),
			)
		);
		
		$select->where(
			$this->quoteInto("MID = ?", $uid)
		);
		
		$select->where(
			$this->quoteInto("CID = ?", $courseId)
		);
		
		$rows = $select->query()->fetch();
		
		if(
			$rows['total'] > 0
			&&
			$this->getService('Acl')->inheritsRole(
				$this->getService('User')->getCurrentUserRole(),
				HM_Role_RoleModelAbstract::ROLE_TUTOR
			)
		){
			return true;
		}
		
		return false;			
	}
	
	
	
	//--это экшен, который работает на рабочем сайте, но не работает на тестовом из за проблем с псевдонимом таблицы lessons
	public function indexAction() { 
        try {
		
		$subjectId = (int) $this->_getParam('subject_id', 0);
		$this->subject_id = $subjectId;
		
		if(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole() == HM_Role_RoleModelAbstract::ROLE_TUTOR ||
           Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole() == HM_Role_RoleModelAbstract::ROLE_TEACHER ){
				$newActionInLessons = $this->getService('Subject')->getNewActionStudent($subjectId);
				$this->view->newActionInLessons = $newActionInLessons;
		}
		
		if(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole() == HM_Role_RoleModelAbstract::ROLE_STUDENT){
			$this->view->isStudent = true;
		}
		
        $subject = $this->getService('Subject')->getOne($this->getService('Subject')->find($subjectId));
        $isBrs = ($subject->mark_type == HM_Mark_StrategyFactory::MARK_BRS);
        
        $this->view->headLink()->appendStylesheet($this->view->serverUrl() . "/css/content-modules/schedule_table.css");

// @tocheck
//         if($this->_subject->access_mode == HM_Subject_SubjectModel::MODE_FREE){
//             $this->_redirector->gotoSimple('card', 'index', 'subject', array('subject_id' => $subjectId));
//         }
        /**
         * Добавлено поле порядок - скрытое.
         * по умолчанию сортировка по порядку, потом уже в zfdatagrid меняется от выбранной пользователем.
         * @author Artem Smirnov <tonakai.personal@gmail.com>
         * @date 17 january 2012
         */
        $switcher = $this->_getParam('switcher', 0);
        if ($switcher && $switcher != 'index') {
            $this->getHelper('viewRenderer')->setNoRender();
            $action = $switcher . 'Action';
            $this->$action();
            $this->view->render('list/' . $switcher . '.tpl');
            return true;
        }
        $select = $this->getService('Lesson')->getSelect();
        $select->from(array('l' => 'lessons'), array(
        
            'lesson_id' => 'l.SHEID',
            'l.SHEID',
            'TypeID2' => 'l.typeID',
            'l.title',            			
            #'l.title_translation',            			
			'required' => 'adf.required',
			'max_ball' => 'adf.max_ball',
            'l.typeID',
            'l.begin',
            'l.end',
            'l.timetype',
            'l.condition',
            'l.cond_sheid',
            'l.cond_mark',
            'l.cond_progress',
            'l.cond_avgbal',
            'l.cond_sumbal',
            'l.isfree',
            'l2.isCanMarkAlways',
            'l2.isCanSetMark',
            'l2.max_ball_academic',
            'l2.max_ball_practic',
            'sort_order' => 'l.order'
                )
        );
        $select->where('l.CID = ?', $subjectId)
                ->where('l.typeID NOT IN (?)', array_keys(HM_Event_EventModel::getExcludedTypes()))
                ->where('l.isfree = ?', HM_Lesson_LessonModel::MODE_PLAN)
                ->order(array('sort_order'));
        if ($this->getService('User')->getCurrentUserRole() != HM_Role_RoleModelAbstract::ROLE_ADMIN) {
            // нужно разобраться и потом раскомментировать
            // этот where() от вебинаров ломает всё расписание
            //$select->where("teacher = " . $this->getService('User')->getCurrentUserId() . ' OR ' . "moderator = " . $this->getService('User')->getCurrentUserId());
            //$select->where('teacher = ?', $this->getService('User')->getCurrentUserId());
        }

		//--$selectAdF отдельно, т.к. поля required и max_ball не работают с пресвдонимом таблицы lessons не тестовом сайте. Вычснить причину.
		$selectAdF = $this->getService('Lesson')->getSelect();
        $selectAdF->from(array('l' => 'schedule'), array(        
            'lesson_id' => 'l.SHEID',
            'required' => 'l.required',
			'max_ball' => 'l.max_ball',
                )
        );
        $selectAdF->where('l.CID = ?', $subjectId)
                ->where('l.typeID NOT IN (?)', array_keys(HM_Event_EventModel::getExcludedTypes()))
                ->where('l.isfree = ?', HM_Lesson_LessonModel::MODE_PLAN);
		
		$select->join(array('adf' => $selectAdF), 'adf.lesson_id = l.SHEID', array());
		$select->join(array('l2' => 'schedule'), 'l2.SHEID = l.SHEID', array());
		
		
		
		if ($this->getService('User')->getCurrentUserRole() == HM_Role_RoleModelAbstract::ROLE_TEACHER) {
			#вывкести назначенных на урок тьюторов
			$assignSelect = $this->getService('Lesson')->getSelect();
			$assignSelect->from('Tutors_lessons', array('LID', 'CID', 'tutors' => new Zend_Db_Expr("GROUP_CONCAT( MID )")));
			$assignSelect->where('LID > 0 AND MID > 0');
			$assignSelect->group(array('LID', 'CID'));
			$select->joinLeft(array('ast' => $assignSelect), 'ast.LID = l.SHEID AND ast.CID='.intval($subjectId), array('tutors' => 'ast.tutors'));
			
		}

		
		if ($this->getService('User')->getCurrentUserRole() == HM_Role_RoleModelAbstract::ROLE_TUTOR) {
			$collection = $this->getService('LessonAssignTutor')->getAssignSubject($this->getService('User')->getCurrentUserId(), $subjectId);
			if(count($collection)){				
				$select->where($this->getService('User')->quoteInto('l.SHEID IN (?)', $collection->getList('LID')));
			}			
		}

        
        if($isBrs){
            $selectSum = clone $select;
            $selectSum->reset(Zend_Db_Select::FROM);
            $selectSum->reset(Zend_Db_Select::COLUMNS);
            $selectSum->reset(Zend_Db_Select::ORDER);            
            $selectSum->from(array('l' => 'schedule'), array(
                    'max_ball_sum' => 'SUM(l.max_ball)',
                )
            );
            $selectSum->where('l.required = 1');            
    //        var_dump($selectSum->__toString());
            $maxBallSum = $selectSum->query()->fetchAll();
            $maxBallSum = $maxBallSum[0]['max_ball_sum'];
        } else {
            $maxBallSum = false;
        }
        
        $grid = $this->getGrid($select, array(
            'sort_order' => array('order' => true, 'hidden' => true),
            'SHEID' => array('hidden' => true),
            'isCanMarkAlways' => array('hidden' => true),
            'isCanSetMark' 		=> array('hidden' => true),
            'TypeID2' => array('hidden' => true),
            'lesson_id' => array('hidden' => true),
            'title' => array('title' => _('Название')),
            #'title_translation' => array('hidden' => true),
            'typeID' => array('title' => _('Тип')),
            'required' => array(
                'title' => _('Обязательное'),
                'callback' => array(
                    'function' => array($this, 'updateRequired'),
                    'params' => array('{{required}}', $isBrs)
                )
            ),
            'max_ball' => array(
                'title' => _('Макс. балл'),
                'callback' => array(
                    'function' => array($this, 'updateMaxBall'),
                    'params' => array('{{max_ball}}', $isBrs, '{{max_ball_academic}}', '{{max_ball_practic}}')
                )
            ),
            'begin' => array('title' => _('Ограничение по времени')),
            'condition' => array('title' => _('Условие')),
            'end' => array('hidden' => true),
            'timetype' => array('hidden' => true),
            'cond_sheid' => array('hidden' => true),
            'cond_mark' => array('hidden' => true),
            'cond_avgbal' => array('hidden' => true),
            'cond_sumbal' => array('hidden' => true),
            'cond_progress' => array('hidden' => true),
            'isfree' => array('hidden' => true),
			'max_ball_academic' => array('hidden' => true),
			'max_ball_practic' 	=> array('hidden' => true),
                ), array(
            'title' => null,
            'typeID' => array('values' => HM_Event_EventModel::getAllTypes(false)),
            'begin' => array('render' => 'DateTimeStamp'),
            'condition' => array('values' => array('0' => _('Нет условия'), '1' => _('Есть условие')))
                )
        );
		
		if ($this->getService('User')->getCurrentUserRole() == HM_Role_RoleModelAbstract::ROLE_TEACHER) {
			$grid->updateColumn('tutors', array(
				'title' 	=> _('Тьюторы'),
				'callback' 	=>	array(
									'function' 	=> array($this, 'updateTutors'),
									'params' 	=> array('{{tutors}}')
								)
			));			
		}
		
		$grid->updateColumn('typeID', array('searchType' => '='));
        $grid->addAction(
                array('module' => 'lesson', 'controller' => 'result', 'action' => 'index', 'preview' => 1), array('lesson_id'), _('Просмотр результатов')
        );
		
		# порядок $grid->addAction не менять. Доступность действия привязано к позиции
		$grid->addAction(
                array('module' => 'lesson', 'controller' => 'test', 'action' => 'result'), array('lesson_id'), _('Выставить оценку вручную')
        );
		
		
		if( $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_TUTOR)
			||
			$this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_TEACHER)
		){
			$grid->addAction(
					array('module' => 'lesson', 'controller' => 'file', 'action' => 'get'), array('lesson_id'), _('Скачать файлы одним архивом')
			);
		}

        $grid->setActionsCallback(
                array('function' => array($this, 'updateActions'),
                    'params' => array('{{TypeID2}}', '{{lesson_id}}')
                )
        );

        $grid->addAction(array(
            'module' => 'lesson',
            'controller' => 'list',
            'action' => 'edit'
                ), array('lesson_id'), $this->view->icon('edit')
        );

        $grid->addAction(array(
            'module' => 'lesson',
            'controller' => 'list',
            'action' => 'delete'
                ), array('lesson_id'), $this->view->icon('delete')
        );
        
        if ($this->getService('User')->getCurrentUserRole() == HM_Role_RoleModelAbstract::ROLE_TEACHER) {
            $grid->addMassAction(array('action' => 'delete-by'), _('Удалить'), _('Вы подтверждаете удаление отмеченных занятий? Если занятие было создано на основе информационного ресурса или учебного модуля, эти материалы вновь станут доступными всем слушателям курса в меню <Материалы курса>.'));
        }
        $grid->updateColumn('typeID', array(
            'callback' =>
            array(
                'function' => array($this, 'getTypeString'),
                'params' => array('{{typeID}}', '{{isCanMarkAlways}}', '{{isCanSetMark}}')
            )
                )
        );

        $grid->updateColumn('begin', array(
            'callback' =>
            array(
                'function' => array($this, 'getDateTimeString'),
                'params' => array('{{begin}}', '{{end}}', '{{timetype}}')
            )
                )
        );

        $grid->updateColumn('title', array(
            'callback' =>
            array(
                'function' => array($this, 'updateName'),
                #'params' => array('{{title}}', '{{title_translation}}', '{{lesson_id}}', '{{typeID}}')
                'params' => array('{{title}}', '{{lesson_id}}', '{{typeID}}')
            )
                )
        );

        $grid->updateColumn('condition', array(
            'callback' =>
            array(
                'function' => array($this, 'getConditionString'),
                'params' => array('{{cond_sheid}}', '{{cond_mark}}', '{{cond_progress}}', '{{cond_avgbal}}', '{{cond_sumbal}}')
            )
                )
        );
		
		if ($this->getService('User')->getCurrentUserRole() == HM_Role_RoleModelAbstract::ROLE_TEACHER) {
			$this->view->isTeacher = true;
			$tutotList = $this->getService('Subject')->getTutotList($subjectId);
			
			$grid->addMassAction(	array('module' => 'assign', 'controller' => 'tutor', 'action' => 'on-lesson'),
									_('Назначить тьютора'),
									_('Вы уверены?')
			);
			$grid->addSubMassActionSelect(	$this->view->url(array('module' => 'assign', 'controller' => 'tutor', 'action' => 'on-lesson')),
											'tutor_ids[]',
											$tutotList			
			);
			
			$grid->addMassAction(	array('module' => 'assign', 'controller' => 'tutor', 'action' => 'unassign-lesson'),
									_('Удалить назначение тьютора'),
									_('Вы уверены?')
			);
			$grid->addSubMassActionSelect(	$this->view->url(array('module' => 'assign', 'controller' => 'tutor', 'action' => 'unassign-lesson')),
											'tutor_ids[]',
											$tutotList			
			);
		}


// exit($select->__toString());

        $this->view->isBrs = $isBrs;
        $this->view->maxBallSum = $maxBallSum;
        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid->deploy();
        $this->getService('EventServerDispatcher')->trigger(
            Es_Service_Dispatcher::EVENT_UNSUBSCRIBE,
            $this,
            array('filter' => $this->getFilterByRequest($this->getRequest()))
        );
        //Zend_Session::namespaceUnset('multiform');
        /*
          $subjectId = (int) $this->_getParam('subject_id', 0);
          $paginator = $this->getService('Lesson')->getPaginator(
          'CID = '.$subjectId.' AND timetype = 0',
          'begin'
          );

          $this->view->paginator = $paginator;
         *
         */
		 } catch (Exception $e) {
			echo 'Выброшено исключение: ',  $e->getMessage(), "\n";
		}
    }
	
	
	/**
	 * обновление выставленных оценок за урок в соответствии с максимальным баллом, указанным в уроке.
	*/
	
	public function updateExistingBall($lesson, $oldMaxBall){	
		if(!$lesson){ return false; }
		
		$subject = $this->getService('Lesson')->getSubjectByLesson($lesson->SHEID);
		if(!$subject){ return false; }
		
		$userBalls 				= $this->getService('LessonAssign')->getLessonUsersScore($lesson->SHEID);
		$newMaxBall 			= $lesson->max_ball;
		$serviceLessonAssign 	= $this->getService('LessonAssign');
		$serviceSubjectMark 	= $this->getService('SubjectMark');
		$serviceLJResult 		= $this->getService('LessonJournalResult');
		foreach($userBalls as $user_id => $curUserBall){
			if(($user_id <= 0) || ($curUserBall <= 0)) 	 { continue; }
			
			if($oldMaxBall > 0){
				$newUserBall = round(   ( ($curUserBall * $newMaxBall) / $oldMaxBall ), 2);			
			} else {
				$newUserBall = 0;	
			}
			
			if($newUserBall > $newMaxBall){	$newUserBall = $newMaxBall; }			
			if($newUserBall == $curUserBall) { continue; }
			
			$data = array(
				'V_STATUS'	=> $newUserBall,
				'updated'	=> date('Y-m-d H:i:s', time()),
			);
		
			if($lesson->typeID == HM_Event_EventModel::TYPE_JOURNAL_PRACTICE){
				# TODO как-то все это нужно унифицировать и перенести в сервисный слой.
				$userMarks 			= $serviceLJResult->getUserMarks($user_id, $lesson->SHEID);
				$user_isBe		 	= 0;
				$user_total_ball 	= 0;
				
				foreach($userMarks as $r){					
					if($r['isBe'] == HM_Lesson_Journal_Result_ResultModel::IS_BE_YES) { $user_isBe++; }
					if($r['mark'] >= 0)												  {
						$user_total_ball += $r['mark'];			
					}
				}	
				
				$ball_weight_academic 	= $serviceLJResult->getWeightAcademic($lesson);
				$ball_weight_practic 	= $serviceLJResult->getWeightPractic($lesson);
				$academic_activity 		= $serviceLJResult->filterBallAcademic(round(($ball_weight_academic * $user_isBe), 2), HM_Lesson_Journal_JournalModel::MAX_BALL_ACADEMIC_ACTIVITY); 		# академическая активность
				$practical_task 		= $serviceLJResult->filterBallPractic(round(($user_total_ball * $ball_weight_practic), 2)); 	# выполнение практического задания	
				
				$data['ball_academic']	= $academic_activity;
				$data['ball_practic']	= $practical_task;
				$data['V_STATUS']		= $practical_task + $academic_activity;				
			}
			
			$res = $serviceLessonAssign->updateWhere($data, array('MID = ?' => $user_id, 'SHEID = ?' => $lesson->SHEID));
			if($res){			
				$serviceSubjectMark->recalculateSubjectMark($subject->subid, $user_id); # обновить итоговую оценку за сессию.					
			}
		}	
	}
	
	/**
	 * @param strimg 'id,id'
	*/
	public function updateTutors($tutors){
		$result = _('Нет');		
		$ids = explode(',', $tutors);
		$ids = array_filter($ids);
		if(empty($ids)) { return $result; }
		
		if($this->subject_id){
			if(!$this->tutorService) { $this->tutorService = $this->getService('Tutor'); }
			$tutorRoles = $this->tutorService->fetchAll($this->tutorService->quoteInto('CID = ?', $this->subject_id))->getList('MID', 'roles');				
			$tutorList	= array();				
		}
		
		if(!$this->userService) { $this->userService = $this->getService('User'); }
        $count  = count($ids);
		$result = ($count > 1) ? array('<p class="total">' . sprintf(_n('тьютор plural', '%s тьютор', $count), $count) . '</p>') : array();
		foreach($ids as $tutor_id){
			if(isset($this->_tutorList[$tutor_id])){				
				$name 	= $this->_tutorList[$tutor_id];				
			} else {					
				$user   = $this->userService->getById($tutor_id);
				if($user){
					$role 	= (isset($tutorRoles[$user->MID])) ? (' ('.HM_Lesson_Assign_Tutor_TutorModel::getRolesName($tutorRoles[$user->MID]).')') : ('');
					$name	= $user->LastName.''.$user->FirstName.' '.$user->Patronymic.$role;
					$this->_tutorList[$tutor_id] = $name;
				}
			}			
			
			if(isset($name) && !empty($name)){
				$result[] = '<p>'.$name.'</p>';
			}			
		}		
		$result = implode('',$result);
		return $result;
	}
	
	/**
	 * назначение тьюторов на занятия в зависимости от роли: лектор, практик, семинарист
	*/
	public function checkTutorAssign($subject_id){
		$tutors = $this->getService('Subject')->getAssignedTutors($subject_id);
		
		if(!count($tutors)){ return false; }
		
		$serviceTutor = $this->getService('LessonAssignTutor');
		foreach($tutors as $t){
			if($serviceTutor->isLaborant($t->MID, $subject_id)){
				$serviceTutor->assignLaborant($subject_id, $t->MID);
			}
			
			if($serviceTutor->isLector($t->MID, $subject_id)){
				$serviceTutor->assignLector($subject_id, $t->MID);
			}
			
			if($serviceTutor->isSeminarian($t->MID, $subject_id)){
				$serviceTutor->assignSeminarian($subject_id, $t->MID);
			}
		}
		return true;
	}

}
