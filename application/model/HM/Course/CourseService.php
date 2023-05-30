<?php
class HM_Course_CourseService extends HM_Service_Abstract implements Es_Entity_Trigger
{
    
    protected $listeners = array();
    
    public function getListeners() {
        return $this->listeners;
    }

    public function setListeners(array $listeners) {
        $this->listeners = $listeners;
    }

    public function delete($courseId)
    {
        // Удаляем структура
        $this->getService('CourseItem')->deleteBy($this->quoteInto('cid = ?', $courseId));

        //удаляем метки
        $this->getService('TagRef')->deleteBy($this->quoteInto(array('item_id=?',' AND item_type=?'),
                                                               array($courseId,HM_Tag_Ref_RefModel::TYPE_COURSE)));

        // Удаляем модули из library
        $collection = $this->getService('Library')->fetchAll($this->quoteInto('cid = ?', $courseId));
        if (count($collection)) {
            foreach($collection as $item) {
                $this->getService('Library')->delete($item->bid);
            }
        }
        
        // Удаляем связки из subjects_courses
        $this->getService('SubjectCourse')->deleteBy(
            $this->quoteInto('course_id = ?', (int) $courseId));
        
        $result = parent::delete($courseId);
        if ($result == true)
        {
            $this->rmrf($_SERVER['DOCUMENT_ROOT'] . "/COURSES/course" . $courseId . "/*");
            rmdir($_SERVER['DOCUMENT_ROOT'] . "/COURSES/course" . $courseId);
        }
        return $result;

    }

    public function create_dirs($id)
    {

        $create = array();
        $ret = 0;

        if (! is_dir($_SERVER['DOCUMENT_ROOT'] . "/COURSES/course" . $id))
            if (! @mkdir($_SERVER['DOCUMENT_ROOT'] . "/COURSES/course" . $id, 0700))
                $ret = 1;
        if (! @chmod($_SERVER['DOCUMENT_ROOT'] . "/COURSES/course" . $id, 0775))
            $ret = 2;
        if (! is_dir($_SERVER['DOCUMENT_ROOT'] . "/COURSES/course" . $id . "/TESTS"))
            if (! @mkdir($_SERVER['DOCUMENT_ROOT'] . "/COURSES/course" . $id . "/TESTS", 0700))
                $ret = 1;
        if (! @chmod($_SERVER['DOCUMENT_ROOT'] . "/COURSES/course" . $id . "/TESTS", 0775))
            $ret = 2;
        if (! is_dir($_SERVER['DOCUMENT_ROOT'] . "/COURSES/course" . $id . "/webcam_room_" . $id))
            if (! @mkdir($_SERVER['DOCUMENT_ROOT'] . "/COURSES/course" . $id . "/webcam_room_" . $id, 0700))
                $ret = 1;
        if (! @chmod($_SERVER['DOCUMENT_ROOT'] . "/COURSES/course" . $id . "/webcam_room_" . $id, 0775))
            $ret = 2;
        if (! is_dir($_SERVER['DOCUMENT_ROOT'] . "/COURSES/course" . $id . "/mods"))
            if (! @mkdir($_SERVER['DOCUMENT_ROOT'] . "/COURSES/course" . $id . "/mods", 0700))
                $ret = 1;
        if (! @chmod($_SERVER['DOCUMENT_ROOT'] . "/COURSES/course" . $id . "/mods", 0775))
            $ret = 2;
        if (! is_dir($_SERVER['DOCUMENT_ROOT'] . "/COURSES/course" . $id . "/TESTS_ANW"))
            if (! @mkdir($_SERVER['DOCUMENT_ROOT'] . "/COURSES/course" . $id . "/TESTS_ANW", 0700))
                $ret = 1;
        if (! @chmod($_SERVER['DOCUMENT_ROOT'] . "/COURSES/course" . $id . "/TESTS_ANW", 0775))
            $ret = 2;
        if ($ret == 2)
            $ret = 0;

        return $ret;
    }


    public function insert($data){

        $result = parent::insert($data);

        $this->getService('CourseItem')->insert(
                                                array('title' => _('Пустой элемент'),
                                                      'cid'   => $result->CID,
                                                      'level' => 0,
                                                      'prev_ref' => -1
                                                )
        );

        if($result){
            $error = $this->create_dirs($result->CID);
        }
        return ($error) ? $error : $result;

    }

    public function rmrf($dir)
    {

        foreach ( glob($dir) as $file )
        {
            if (is_dir($file))
            {
                $this->rmrf("$file/*");
                rmdir($file);
            } else
            {
                unlink($file);
            }
        }
    }

	public function assignClaimant($courseId, $studentId)
    {
        $course = $this->getOne($this->findDependence(array('Student', 'Claimant'), $courseId));
        if ($course) {
            if (!$course->isClaimant($studentId) && !$course->isStudent($studentId)) {
                return $this->getService('Claimant')->insert(
                    array(
                        'MID' => $studentId,
                        'CID' => $courseId,
                        'Teacher' => 0
                    )
                );
            }
        }
    }

    public function getParentItem($itemId)
    {
        return $this->getService('CourseItem')->getParent($itemId);
    }

    public function getChildrenLevelItems($courseId, $parent = -1)
    {
        return $this->getService('CourseItem')->getChildrenLevel($courseId, $parent);
    }


    public function isTeacher($courseId, $userId){

        $parentSubjects = $this->getService('SubjectCourse')->getCourseParent($courseId);

        $res = false;

        foreach($parentSubjects as $val){
            if($this->getService('Subject')->isTeacher($val->subject_id, $userId)){
                $res = true;
            }
        }
        return $res;
    }

    public function isStudent($courseId, $userId){
        $parentSubjects = $this->getService('SubjectCourse')->getCourseParent($courseId);

        $res = false;

        foreach($parentSubjects as $val){
            if($this->getService('Subject')->isStudent($val->subject_id, $userId)){
                $res = true;
                break;
            }
        }
        return $res;

    }

    public function getDevelopers($courseId){
        $res = $this->getService('User')->fetchAllDependenceJoinInner('Developer', 'Developer.cid = '. (int)$this->CID);
        $result =array();
        if($res){
            foreach($res as $val){
                $result[] = $val->getName() ;
            }
            return $result;
        }

        return false;
    }

    public function publish($courseId)
    {
        $course = $this->getOne($this->getService('Course')->fetchAll(sprintf('cid = %d', $courseId)));
        if ($course) {
            $errors = array();
            if (!in_array($course->Status, array(HM_Course_CourseModel::STATUS_DEVELOPED, HM_Course_CourseModel::STATUS_ARCHIVED))) {
                $errors[] = _('курс уже опубликован');
            }
            if (!strlen($course->Title)) {
                $errors[] = _('не заполнено название курса');
            }
            //if (!strlen($course->Description)) {
            //    $errors[] = _('не заполнено описание курса');
            //}

            if (!count($errors)) {
                $data = array(
                    'CID' => $courseId,
                    'Status' => HM_Course_CourseModel::STATUS_ACTIVE
                );

                $this->getService('Course')->update($data);
                return true;
            }

            throw new HM_Exception(sprintf(_('Курс %s не опубликован. %s.'), $course->Title, join(', ', $errors)));
        }

        return false;

    }

    public function copyDir($strDirSource, $strDirDest, $strtolower = false) {
        $ret = $failures = array();
        if (substr($strDirDest, -1) != '/') {
            $strDirDest .= '/';
        }
        if ($handle = opendir($strDirSource)) {
            while (false !== ($file = readdir($handle))) {
                $strLowerFile = ($strtolower) ? strtolower($file) : $file;
                if (is_dir($strDirSource."/".$file)) {
                    if (($file != "..") && ($file != ".")) {
                        if (!mkdir($strDirDest.$strLowerFile,0775)) {
                            throw new HM_Exception(sprintf(_('Невозможно создать каталог %s'), $strDirDest.$strLowerFile));
                        }
                        if (!chmod($strDirDest.$strLowerFile,0775)) {
                            throw new HM_Exception(sprintf(_('Невозможно установить права на каталог %s'), $strDirDest.$strLowerFile));
                        }

                        $ret = array_merge($ret, $this->copyDir($strDirSource."/".$file, $strDirDest.$strLowerFile."/", $strtolower));
                    }
                }
                else {
                    if (!copy($strDirSource."/".$file, $strDirDest.$strLowerFile)) {
                        $failures[] = $strDirDest.$strLowerFile;
                    }
                    $ret[] = $strDirDest.$strLowerFile;
                }
            }
            closedir($handle);
        } else {
            throw new HM_Exception(sprintf(_('Невозможно прочитать каталог %s'), $strDirSource));
        }

        if (count($failures)) {
            throw new HM_Exception(sprintf(_('Невозможно скопировать файл(ы): %s'), implode(', ', $failures)));
        }
        return $ret;
    }
    
    public function emptyDir($dir)
    {
        return $this->rmrf($dir . '/*');
    }

    public function removeDir($dir)
    {
        $this->rmrf($dir . '/*');
        rmdir($dir);
        return true;
    }

    public function emulate($courseId, $emulateMode)
    {
        $course = $this->getOne($this->find($courseId));
        if ($course) {

            if ($course->emulate == $emulateMode) {
                return true;
            }

            $emulatePath = $course->getEmulatePath($emulateMode);

            if (file_exists($emulatePath) && is_dir($emulatePath)) {
                $this->rmrf($emulatePath);
                rmdir($emulatePath);
            }

            try {
            /*if (!mkdir($emulatePath, 0700)) {
                    throw new HM_Exception(sprintf(_("Невозможно создать каталог %s"), $emulatePath));
                }

                if (!chmod($emulatePath, 0775)) {
                    throw new HM_Exception(sprintf(_("Невозможно установить права на каталог %s"), $emulatePath));
            }*/

            //$this->copyDir($course->getPath(), $emulatePath);
            if (!rename($course->getPath(), $emulatePath)) {
                throw new HM_Exception(sprintf(_("Невозможно переименовать в %s"), $emulatePath));
            }

            $collection = $this->getService('Library')->fetchAll(
                $this->quoteInto('cid = ?', $courseId)
            );

            $this->getMapper()->getAdapter()->getAdapter()->beginTransaction();

            $emulatePathPiece = $course->getEmulatePathPiece($emulateMode);
            if (count($collection)) {
                foreach($collection as $item) {
                    $item->filename = preg_replace('/emulate-ie[0-9]+\//', '', $item->filename);
                    if ($emulatePathPiece) {
                        $item->filename = str_replace('COURSES/', 'COURSES/'.$emulatePathPiece.'/', $item->filename);
                    }
                    $this->getService('Library')->update($item->getValues());
                }
            }

            $this->getService('Course')->update(array('CID' => $courseId, 'emulate' => $emulateMode));

            $this->getMapper()->getAdapter()->getAdapter()->commit();

            } catch(HM_Exception $e) {
                $this->rmrf($emulatePath);
                throw $e;
                //return false;
            } catch(Zend_Db_Exception $e) {
                $this->getMapper()->getAdapter()->rollBack();
                $this->rmrf($emulatePath);
                rmdir($emulatePath);
                throw $e;
            }

        }
    }

    public function createLesson($subjectId, $courseId, $isfree = HM_Lesson_LessonModel::MODE_FREE, $section = false, $order = false)
    {
        if (empty($section)) {
            $section = $this->getService('Section')->getDefaultSection($subjectId);
            if (empty($order)) {
                $currentOrder = $this->getService('Section')->getCurrentOrder($section);
                $order = ++$currentOrder;
            }
        }

        $lessons = $this->getService('Lesson')->fetchAll(
            $this->getService('Lesson')->quoteInto(
                array('typeID = ?', " AND params LIKE ?", ' AND CID = ?'),
                array(HM_Event_EventModel::TYPE_COURSE, '%module_id='.$courseId.';%', $subjectId)
            )
        );

        if (!count($lessons)) {
            $course = $this->getOne($this->getService('Course')->find($courseId));
            if ($course) {
                $values = array(
                    'title' => $course->Title,
                    'descript' => $course->Description,
                    'begin' => date('Y-m-d 00:00:00'),
                    'end' => date('Y-m-d 23:59:00'),
                    'createID' => 1,
                    'createDate' => date('Y-m-d H:i:s'),
                    'typeID' => HM_Event_EventModel::TYPE_COURSE,
                    'vedomost' => 1,
                    'CID' => $subjectId,
                    'startday' => 0,
                    'stopday' => 0,
                    'timetype' => 2,
                    'isgroup' => 0,
                    'teacher' => 0,
                    'params' => 'module_id='.(int) $course->CID.';',
                    'all' => 1,
                    'cond_sheid' => '',
                    'cond_mark' => '',
                    'cond_progress' => 0,
                    'cond_avgbal' => 0,
                    'cond_sumbal' => 0,
                    'cond_operation' => 0,
                    'isfree' => $isfree,
                    'section_id' => $section->section_id,
                    'order' => $order,
                );
                $lesson = $this->getService('Lesson')->insert($values);

                $students = $lesson->getService()->getAvailableStudents($subjectId);
                if (is_array($students) && count($students)) {
                    $this->getService('Lesson')->assignStudents($lesson->SHEID, $students);
                }
                $this->getService('EventDispatcher')->notify(
                    new sfEvent($this, __CLASS__.'::esPushTrigger', array('lesson' => $lesson))
                );
            }
        }
    }


    public function clearLesson($subject, $courseId)
    {
        if ($subject == null) {
            $lessons = $this->getService('Lesson')->fetchAll(
                $this->getService('Lesson')->quoteInto(
                    array('(typeID = ?', ' AND params LIKE ?) OR ', '(typeID = ?', ' AND params LIKE ?)'),
                    array(
                        HM_Event_EventModel::TYPE_COURSE,
                        '%module_id='.$courseId.';%',
                        HM_Event_EventModel::TYPE_LECTURE,
                        '%course_id='.$courseId.';%',
            )));
        } else {
            $lessons = $this->getService('Lesson')->fetchAll(
                $this->getService('Lesson')->quoteInto(
                    array('((typeID = ?', ' AND params LIKE ?) OR ', '(typeID = ?', ' AND params LIKE ?))', ' AND CID = ?'),
                    array(
                        HM_Event_EventModel::TYPE_COURSE,
                        '%module_id='.$courseId.';%',
                        HM_Event_EventModel::TYPE_LECTURE, // нужно подчистить также и все занятия с типом "раздел уч.модуля"
                        '%course_id='.$courseId.';%',
                        $subject->subid,
             )));
        }

        if (count($lessons)) {
            $subjectNew = null;
            foreach($lessons as $lesson) {
                $subjectNew = $this->getService('Subject')->getOne($this->getService('Subject')->find($lesson->CID));
                $this->getService('Lesson')->deleteBy(array('SHEID = ?' => $lesson->SHEID, 'isfree IN (?)' => new Zend_Db_Expr(implode(',', array(HM_Lesson_LessonModel::MODE_FREE, HM_Lesson_LessonModel::MODE_FREE_BLOCKED)))));
                $this->getService('Lesson')->updateWhere(array('params' => ''), array('SHEID = ?' => $lesson->SHEID, 'isfree = ?' => HM_Lesson_LessonModel::MODE_PLAN));

                $params = $lesson->getParams();
                if (!empty($params['course_id'])) {

                }
            }
        }

    }

    public function getDefaults()
    {
        return array(
            'Description' => '',
            'Status' => HM_Course_CourseModel::STATUS_STUDYONLY,
            'provider' => 0,
            'developStatus' => 0,
            'lastUpdateDate' => $this->getDateTime(),
            'createDate' => $this->getDateTime(),
            'TypeDes' => 0,
            'chain' => 0,
            'did' => 0,
            'sequence' => 0,
            'is_module_need_check' => 0,
            'has_tree' => 0,
            'new_window' => 0,
            'emulate' => HM_Course_CourseModel::EMULATE_IE_NONE ,
            'longtime' => 0,
            'format' => HM_Course_CourseModel::FORMAT_FREE,
            'author' => $this->getService('User')->getCurrentUserId()
        );
    }
    
    public function createEvent(\HM_Model_Abstract $model) {
        $event = $this->getService('ESFactory')->newEvent($model, null, $this);
        $currentUser = $this->getService('User')->getCurrentUser();
        $event->setParam('author_id', $currentUser->getPrimaryKey());
        $event->setParam('author_name', $currentUser->getName());
        $authorAvatar = '/'.ltrim($currentUser->getPhoto(), '/');
        $event->setParam('author_avatar', $authorAvatar);
        return $event;
    }

    public function getRelatedUserList($event) {
        return $this->getListeners();
    }

    public function triggerPushCallback() {
        return function($ev) {
            $parameters = $ev->getParameters();
            $course = $parameters['lesson'];
            /*@var $service HM_Course_CourseService */
            $service = $ev->getSubject();
            /*@var $event Es_Entity_AbstractEvent */
            $event = $service->createEvent($course);
            $event->setEventType(Es_Entity_AbstractEvent::EVENT_TYPE_COURSE_ADD_MATERIAL);
            $event->setParam('title', $course->title);
            $event->setParam('created', $course->createDate);


            $subject = $service->getService('Subject')->find((int)$course->CID)->current();

            $assigUsers = $service->getService('Subject')->getAssignedUsers($subject->getPrimaryKey());
            $userIds = array();
            foreach ($assigUsers as $user) {
                $userIds[] = $user->getPrimaryKey();
            }
            $service->setListeners($userIds);
            
            $event->setParam('course_name', $subject->name);
            $event->setParam('course_id', $subject->getPrimaryKey());
            
            $eventGroup = $service->getService('ESFactory')->eventGroup(
                    HM_Subject_SubjectService::EVENT_GROUP_NAME_PREFIX, (int)$subject->getPrimaryKey()            
            );
            $eventGroup->setData(json_encode(array(
                'course_name' => $event->getParam('course_name'),
                'course_id' => $event->getParam('course_id')
            )));
            $event->setGroup($eventGroup);
            $esService = $service->getService('EventServerDispatcher');
            $esService->trigger(
                Es_Service_Dispatcher::EVENT_PUSH,
                $service,
                array('event' => $event)
            );
        };
    }

}