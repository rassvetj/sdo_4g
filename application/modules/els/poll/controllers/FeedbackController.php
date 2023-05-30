<?php
class Poll_FeedbackController extends HM_Controller_Action
{

    private $_subjects;
    private $_teachers;

    public function indexAction()
    {
        $subjectId = (int) $this->_getParam('subject_id', 0);

        $this->_subjects = $this->getService('Subject')->fetchAllManyToMany('User', 'Teacher');

        $select = $this->getService('PollFeedback')->getSelect();
        $select->from(
                    array('qf' => 'quizzes_feedback'),
                    array(
                        'qf.user_id',
                        'qf.subject_id',
                        'qf.lesson_id',
                        'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
                        //'position' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(CONCAT(p.Position, ', '), p.region), ', '), p.branch_code), p.department)"),
                        //'boss_fio' => new Zend_Db_Expr("''"), // todo
                        'l.title',
                        'l.typeID',
                        'subject_title' => 'qf.subject_name',
                        'qf.begin',
                        'qf.end',
                        'qf.place',
                        'teacher' => 'qf.trainer',
                        'status' => 'qf.status'
                    ))
                ->joinInner(array('p' => 'People'), 'qf.user_id = p.MID', array())
                ->joinLeft(array('l' => 'schedule'), 'qf.lesson_id = l.SHEID', array());

        $grid = $this->getGrid(
            $select,
            array(
                'user_id' => array('hidden' => true),
                'subject_id' => array('hidden' => true),
                'lesson_id' => array('hidden' => true),
                'fio' => array('title' => _('ФИО'), 'decorator' => $this->view->cardLink($this->view->url(array(
					                														'module' => 'user', 
					                														'controller' => 'list', 
					                														'action' => 'view', 
					                														'user_id' => ''), null, true) . '{{user_id}}'
            														) . 
            														'<a href="'.$this->view->url(array(
            																				'module' => 'user', 
            																				'controller' => 'edit', 
            																				'action' => 'card', 
            																				'user_id' => ''), null, true) . '{{user_id}}'.'">'. ' {{fio}}</a>'
            	),
                'title' => array('title' => _('Опрос'), 'decorator' => '<a href="'.$this->view->url(array(
            																				'module' => 'lesson', 
            																				'controller' => 'result', 
            																				'action' => 'poll-by-user'), null, true) .'/user_id/{{user_id}}/lesson_id/{{lesson_id}}">'. ' {{title}}</a>'),
                'typeID' => array('title' => _('Тип опроса')),
                'position' => array('title' => _('Должность, Регион, Филиал, Подразделение')),
                //'boss_fio' => array('title' => _('ФИО руководителя')),
                'subject_title' => array('title' => _('Название курса')),
                'begin' => array('title' => _('Период обучения')),
                'end' => array('hidden' => true),
                'place' => array('title' => _('Место проведения')),
                'teacher' => array('title' => _('ФИО тренера')),
                'status' => array('title' => _('Статус'))
            ),
            array(
                'fio' => null,
                'title' => null,
                'typeID' => array('values' => HM_Event_EventModel::getFeedbackPollTypesShort()),
                'position' => null,
                //'boss_fio' => null,
                'subject_title' => null,
                'begin' => array('render' => 'Date'),
                //'end' => array('render' => 'Date'),
                'place' => null,
                'status' => array('values' => HM_Poll_Feedback_FeedbackModel::getStatuses())
            )
        );

        $grid->updateColumn('begin', array('callback' => array('function' => array($this, 'updatePeriod'), 'params' => array('{{begin}}', '{{end}}'))));

        /*
        $grid->updateColumn('end', array(
            'format' => array(
                'date',
                array('date_format' => Zend_Locale_Format::getDateFormat())
            )
        )
        );
        */
        $grid->updateColumn('typeID', array('callback' => array('function' => array($this, 'updateTypeId'), 'params' => array('{{typeID}}'))));
        $grid->updateColumn('teacher', array('callback' => array('function' => array($this, 'updateTeacher'), 'params' => array('{{subject_id}}'))));
        $grid->updateColumn('status', array('callback' => array('function' => array($this, 'updateStatus'), 'params' => array('{{status}}'))));

        $grid->addAction(array(
            'module' => 'poll',
            'controller' => 'feedback',
            'action' => 'resend'
        ),
            array('user_id', 'lesson_id'),
            _('Отправить уведомление')
        );

        $grid->addAction(array(
            'module' => 'poll',
            'controller' => 'feedback',
            'action' => 'cancel'
        ),
            array('user_id', 'lesson_id'),
            $this->view->icon('cancel')
        );

        //$grid->addMassAction(array('action' => 'cancel-by'), _('Отменить'));

        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid->deploy();

    }

    public function cancelAction()
    {
        $userId    = (int) $this->_getParam('user_id', 0);
        $subjectId = (int) $this->_getParam('subject_id', 0);
        $lessonId  = (int) $this->_getParam('lesson_id', 0);

        $this->getService('Lesson')->unassignStudent($lessonId, $userId);

        $this->_flashMessenger->addMessage(_('Опрос успешно отменён'));
        $this->_redirector->gotoSimple('index', 'feedback', 'poll');
    }

    public function updateTypeId($typeId)
    {
        $types = HM_Event_EventModel::getFeedbackPollTypesShort();
        if (isset($types[$typeId])) {
            return $types[$typeId];
        }
        return _('Опрос');
    }

    public function updatePeriod($begin, $end)
    {
        return sprintf(_('%s по %s'), date('d.m.Y', strtotime($begin)), date('d.m.Y', strtotime($end)));
    }

    public function updateTeacher($subjectId)
    {
        $ret = array();
        if (isset($this->_teachers[$subjectId])) return $this->_teachers[$subjectId];

        if ($subject = $this->_subjects->exists('subid', $subjectId)) {
            $teachers = $subject->getTeachers();
            if (count($teachers)) {
                foreach($teachers as $teacher) {
                    $ret[] = $teacher->getName();
                }
                $this->_teachers[$subjectId] = join(', ', $ret);
            }
        }
        return join(', ', $ret);
    }

    public function updateStatus($status)
    {
        $statuses = HM_Poll_Feedback_FeedbackModel::getStatuses();
        return $statuses[$status];
    }

    public function resendAction()
    {
        $userId    = (int) $this->_getParam('user_id', 0);
        $subjectId = (int) $this->_getParam('subject_id', 0);
        $lessonId  = (int) $this->_getParam('lesson_id', 0);

        $lesson = $this->getOne($this->getService('Lesson')->find($lessonId));
        if ($lesson) {
            $lesson->getService()->assignStudents($lessonId, array($userId), false);
        }

        $this->_flashMessenger->addMessage(_('Уведомление успешно отправлено'));
        $this->_redirector->gotoSimple('index', 'feedback', 'poll');
    }
}