<?php
require_once APPLICATION_PATH . '/views/infoblocks/ScreenForm.php';

class HM_View_Infoblock_FreeAccessToSubjectBlock extends HM_View_Infoblock_ScreenForm
{
    const MAX_ITEMS = 10;

    protected $id = 'freeAccessToSubject';

    public function freeAccessToSubjectBlock($title = null, $attribs = null, $options = null)
    {

        $subject = $options['subject'];
        $services = Zend_Registry::get('serviceContainer');

        $coursesArr = $resourcesArr = $materials = array();
        if ($courses = $services->getService('Course')->fetchAllDependenceJoinInner('SubjectAssign', 'SubjectAssign.subject_id = '. $subject->subid)) {
            foreach ($courses as $course) {
                $coursesArr[$course->CID] = $course;
            }
        }

        if ($resources = $services->getService('Resource')->fetchAllDependenceJoinInner('SubjectAssign', 'SubjectAssign.subject_id = '. $subject->subid)) {
            foreach ($resources as $resource) {
                $resourcesArr[$resource->resource_id] = $resource;
            }
        }

        /** @var HM_User_UserService $userService */
        $userService = $this->getService('User');

        /** @var HM_Lesson_LessonService $lessonService */
        $lessonService = $this->getService('Lesson');
        $isSupervisor = $this->getService('Acl')->inheritsRole(
            $userService->getCurrentUserRole(),
            HM_Role_RoleModelAbstract::ROLE_SUPERVISOR
        );
        if ($isSupervisor) {
            $lessonAssigns = $lessonService->fetchAll($lessonService->quoteInto(array(
                    ' CID = ?',
                    ' AND isfree = ?',
                ), array(
                    $subject->subid,
                    HM_Lesson_LessonModel::MODE_FREE
                )),
                array('createDate'),
                self::MAX_ITEMS
            );
        } else {
            $lessonAssigns = $services->getService('LessonAssign')->fetchAllDependenceJoinInner('Lesson', $lessonService->quoteInto(array(
                    'self.MID = ?',
                    ' AND Lesson.CID = ?',
                    ' AND Lesson.isfree = ?',
                ), array(
                    $userService->getCurrentUserId(),
                    $subject->subid,
                    HM_Lesson_LessonModel::MODE_FREE
                )),
                array('launched DESC'),//, 'Lesson.createDate'),
                self::MAX_ITEMS
            );
        }

        foreach ($lessonAssigns as $lessonAssign) {
            $lesson = ($isSupervisor) ? $lessonAssign : $lessonAssign->lessons->current();
            $moduleId = $lesson->getModuleId();
            $arr = ($lesson->typeID == HM_Event_EventModel::TYPE_COURSE) ? $coursesArr : $resourcesArr;
            if (isset($arr[$moduleId])) {
                $lesson->material = $arr[$moduleId];
            } else {
                continue;
            }
            $materials[] = $lesson;

        }

        $this->view->materials = $materials;
        $this->view->subject = $subject;

		$content = $this->view->render('freeAccessToSubjectBlock.tpl');

        if ($title == null) return $content;
        return parent::screenForm($title, $content, $attribs);
    }
}