<?php
class Rest_LessonsController extends HM_Controller_Action_RestOauth
{
    /*
    * @var HM_Lesson_LessonService
    */
    protected $_defaultService = null;

    protected $_debug = false;

    public function init()
    {
        parent::init();
        $this->_defaultService = $this->getService('Lesson');
    }

    private function _getResourceUrl($lesson)
    {
        $resourceUrl = '';
        if ($lesson->typeID == HM_Event_EventModel::TYPE_RESOURCE) {
            $resource = $this->getOne($this->getService('Resource')->find($lesson->getModuleId()));
            if ($resource) {
                switch($resource->type) {
                    case HM_Resource_ResourceModel::TYPE_FILESET:
                        $path = explode('public', Zend_Registry::get('config')->path->upload->public_resource);
                        $pathToFile  = (isset($path[1])) ? $path[1] : '/upload/resources/';
                        $pathToFile .=  $resource->resource_id . '/' . $resource->url;
                        $protocol    = ($this->_request->isSecure())? 'https' : 'http';
                        $host        = $this->_request->getHttpHost();
                        $resourceUrl = $protocol . '://' .$host . $pathToFile;
                        break;
                    case HM_Resource_ResourceModel::TYPE_URL:
                        if (strpos($resource->url, 'http://') !== 0) {
                            $resource->url = 'http://' . $resource->url;
                        }
                        $resourceUrl = $resource->url;
                        break;
                    case HM_Resource_ResourceModel::TYPE_EXTERNAL:
                        $source = array(
                            'module'      => 'resource',
                            'controller'  => 'index',
                            'action'      => 'data',
                            'resource_id' => $resource->resource_id
                        );
                        $resourceUrl = $this->view->serverUrl($this->view->url($source, null, true));
                        break;
                }
            }
        }

        return $resourceUrl;
    }

    public function indexAction()
    {
        $type = (int) $this->_getParam('type', 0);
        $teacherId = (int) $this->_getParam('teacher_id', 0);
        $resourceId = (int) $this->_getParam('resource_id', 0);
        $userId = (int) $this->_getParam('user_id', 0);

        $whereConditions = $whereData = array();

        if ($type) {
            $whereConditions[] = 'typeID = ?';
            $whereData[] = $type;
        }

        if ($teacherId) {
            $whereConditions[] = 'teacher = ?';
            $whereData[] = $teacherId;
        }

        if ($resourceId) {
            $whereConditions[] = 'params LIKE ?';
            $whereData[] = sprintf('module_id=%d;%', $resourceId);
        }

        if ($userId) {
            $whereConditions[] = 'Assign.MID = ?';
            $whereData[] = $userId;
        }

        if (count($whereConditions)) {
            for($i=1; $i < count($whereConditions); $i++) {
                $whereConditions[$i] = ' AND '.$whereConditions[$i];
            }
        }

        $where = null;
        if (count($whereConditions)) {
            $where = $this->_defaultService->quoteInto($whereConditions, $whereData);
        }

        if ($userId) {
            $collection = $this->_defaultService->fetchAllJoinInner(
                'Assign',
                $where
            );
        } else {
            $collection = $this->_defaultService->fetchAll(
                $where
            );
        }

        $lessons = array();
        if (count($collection)) {

            if ($userId <= 0) {
                $userId = $this->_server->authorizedUserId();
            }

            $students = $this->getService('Student')->fetchAll(
                $this->getService('Student')->quoteInto(
                    array('MID = ?', ' AND CID IN (?)'),
                    array($userId, $collection->getList('CID'))
                )
            );

            foreach($collection as $lesson) {

                $registered = null;

                if ($lesson->isTimeFree()) {
                    $begin = $end = HM_Lesson_LessonModel::DATE_UNLIMITED;
                } else {
                    if ($student = $students->exists('CID', $lesson->CID)) {
                        $registered = $student->time_registered;
                    }

                    $begin = strip_tags($lesson->getBeginDatetime($registered));
                    $end = strip_tags($lesson->getEndDatetime($registered));
                }

                $resourceUrl = $this->_getResourceUrl($lesson);

                $lessons[] = array(
                    'lesson_id' => $lesson->SHEID,
                    'title' => $lesson->title,
                    'type' => $lesson->typeID,
                    'begin' => $begin,
                    'end' => $end,
                    'teacher_id' => $lesson->teacher,
                    'resource_id' => $lesson->getModuleId(),
                    'resource_url' => $resourceUrl // todo
                );

            }
        }

        $this->view->assign($lessons);

    }

    public function getAction()
    {
        $id = (int) $this->_getParam('id', 0);
        if ($id > 0) {

            if ($subject = $this->_getParam('sub', false)) {
                if (method_exists($this, $subject)) {
                    $result = $this->{$subject}();
                    if ($result) {
                        $this->view->assign($result);
                    }
                }
            } else {
                $lesson = $this->getOne(
                    $this->getService('Lesson')->find($id)
                );

                if ($lesson) {

                    if ($this->_debug) {
                        $userId = 0;
                    } else {
                        $userId = $this->_server->authorizedUserId();
                    }

                    $registered = null;

                    $student = $this->getOne($this->getService('Student')->fetchAll(
                        $this->getService('Student')->quoteInto(
                            array('MID = ?', ' AND CID = ?'),
                            array($userId, $lesson->CID)
                        )
                    ));

                    if ($student) {
                        $registered = $student->time_registered;
                    }

                    if ($lesson->isTimeFree()) {
                        $begin = $end = HM_Lesson_LessonModel::DATE_UNLIMITED;
                    } else {
                        $begin = strip_tags($lesson->getBeginDatetime($registered));
                        $end = strip_tags($lesson->getEndDatetime($registered));
                    }

                    $result = array(
                        'lesson_id' => $lesson->SHEID,
                        'title' => $lesson->title,
                        'begin' => $begin,
                        'end' => $end,
                        'teacher_id' => $lesson->teacher,
                        'resource_id' => $lesson->getModuleId(),
                        'resource_url' => $this->_getResourceUrl($lesson),
                        'students' => array()
                    );

                    $assigns = $this->getService('LessonAssign')->fetchAllDependence(
                        'User',
                        $this->getService('LessonAssign')->quoteInto('SHEID = ?', $lesson->SHEID)
                    );

                    if (count($assigns)) {
                        foreach($assigns as $assign) {
                            if (isset($assign->users)) {
                                $user = $assign->users->current();
                                if ($user) {
                                    $result['students'][] = array(
                                        'user_id' => $user->MID,
                                        'lastname' => $user->LastName,
                                        'firstname' => $user->FirstName,
                                        'patronymic' => $user->Patronymic,
                                        'photo_url' => $user->getPhoto(),
                                        'result' => array('score' => array('scaled' => $assign->V_STATUS, 'completion' => true))
                                    );
                                }
                            }
                        }
                    }

                    $this->view->assign(
                        $result
                    );
                }
            }
        }

    }

    public function putAction()
    {
        $lessonId = (int) $this->_getParam('id', 0);
        $userId = (int) $this->_getParam('user_id', 0);
        $result = $this->_getParam('result', false);

        if ($lessonId && $userId && $result) {
            $lessonAssign = $this->getOne($this->getService('LessonAssign')->fetchAll(
                $this->getService('LessonAssign')->quoteInto(
                    array('MID = ?', ' AND SHEID = ?'),
                    array($userId, $lessonId)
                )
            ));

            if ($lessonAssign) {
                $lessonAssign->V_STATUS = $result;
                $lessonAssign = $this->getService('LessonAssign')->update(
                    $lessonAssign->getValues()
                );

                if ($lessonAssign) {
                    $this->view->assign(array(
                        'lesson_id' => $lessonAssign->SHEID,
                        'user_id' => $lessonAssign->MID,
                        'result' => array(
                            'score' => array(
                                'scaled' => $lessonAssign->V_STATUS,
                                'completion' => true
                            )
                        )
                    ));
                }
            }
        }
    }

}
