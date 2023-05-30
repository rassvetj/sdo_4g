<?php
class HM_StudyGroup_Course_CourseService extends HM_Service_Abstract
{

    public function addCourseOnGroup($courseId, $groupId, $isElective = false)
    {
        if (!in_array($courseId, $this->getGroupCoursesIds($groupId))) {
            $this->insert(array('group_id' => $groupId,'course_id' => $courseId));
        }

        if ($isElective) {
            return true;
        }
        $usersIds = $this->getService('StudyGroup')->getUsers($groupId);
        $users = $this->getService('User')->getUsersByIds($usersIds);

        if ($users) {
            /* Зачисление на курс */
            foreach($users as $user) {
                /* Проверка подписан ли слушатель на этот же курс в другой группе */
                if (!$this->isUserCourseInOtherGroup($groupId, $courseId, $user->MID)) {
                    $this->getService('Subject')->assignStudent($courseId, $user->MID);
                } else {
                    /* ToDO возможно надо будет откорректировать назначение курса (диапазон обучения, сдачи, и тд.) */
                }
            }
        }

        return true;
    }

    public function removeGroupFromCourse($groupId, $courseId, $isElective = false)
    {
        if (!in_array($courseId, $this->getGroupCoursesIds($groupId))) {
            return false;
        }
        /* Удаляем связь группа курс*/
        $this->deleteBy(array('group_id = ?' => $groupId, 'course_id = ?' => $courseId, 'lesson_id = ?' => 0));

        if ($isElective) {
            return true;
        }

        $groupUsers = $this->getService('StudyGroup')->getUsers($groupId);
        /* Отчисление с курса участников группы */
        foreach($groupUsers as $userId) {
            /* Проверка подписан ли слушатель на этот же курс в другой группе */
            if (!$this->isUserCourseInOtherGroup($groupId, $courseId, $userId )) {
                $this->getService('Subject')->unassignStudent($courseId, $userId);
            } else {
                /* ToDO возможно надо будет откорректировать назначение курса (диапазон обучения, сдачи, и тд.) */
            }
        }
        return true;
    }

    public function addLessonOnGroup($courseId, $lessonId, $groupId)
    {
        if (!in_array($lessonId, $this->getLessonsCoursesIds($groupId, $courseId))) {
            $this->insert(array('group_id' => $groupId,'course_id' => $courseId, 'lesson_id' => $lessonId));

            /*

            Сама подписка студентов осуществляется в контроллере, когда создается или меняется задание
            /var/www/sites/local.hyper.com/application/modules/els/lesson/controllers/ListController.php
                protected function addLesson($form)
                protected function assignStudents($lessonId, $students, $taskUserVariants = null)

            $usersIds = $this->getService('StudyGroup')->getUsers($groupId);
            if (is_array($usersIds) && count($usersIds)) {
                $this->getService('Lesson')->assignStudents($lessonId, $usersIds, true, null);
            }*/
        }
        return true;
    }

    public function removeGroupCourses($groupId)
    {
        /* Список пользователей группы */
        $groupUsers = $this->getService('StudyGroup')->getUsers($groupId);
        foreach ($this->getGroupCoursesIds($groupId) as $courseId) {
            /* Отмена зачисление на курс */
            foreach($groupUsers as $user) {
                if (!count($this->isUserCourseInOtherGroup($groupId, $courseId, $user ))) {
                    $this->getService('Subject')->unassignStudent($courseId, $user);
                }
            }
            /* Удаление привязки группа - курс */
            $this->deleteBy(array('group_id = ?' => $groupId, 'course_id = ?' => $courseId, 'lesson_id = ?' => 0));
        }
    }

    public function removeGroupLessons($groupId)
    {
        /* Список пользователей группы */
        $groupUsers = $this->getService('StudyGroup')->getUsers($groupId);
        foreach ($this->getGroupLessonsIds($groupId) as $lessonId) {
            /* Отмена зачисление на задания */
            foreach($groupUsers as $user) {
                if (!count($this->isUserLessonInOtherGroup($groupId, $lessonId, $user ))) {
                    $this->getService('Lesson')->unassignStudent($lessonId, array($user));
                }
            }
            /* Удаление привязки группа - задания */
            $this->deleteBy(array('group_id = ?' => $groupId, 'lesson_id > ?' => 0));
        }
    }

    public function removeLessonFromGroups($courseId, $lessonId)
    {
        foreach ($this->getGroupsByLesson($lessonId) as $groupId) {
            /* Удаляем связь группа занятие*/
            $this->deleteBy(array('group_id = ?' => $groupId, 'course_id = ?' => $courseId, 'lesson_id = ?' => $lessonId));
        }
    }

    public function isUserCourseInOtherGroup($groupId, $courseId, $userId )
    {
        /* проверка или пользователь записан на этот же курс в другой группе */
        $select = $this->getSelect();
        $select->from(
            array('sgc' => 'study_groups_courses'),
            array('sgc.*')
            )
            ->joinLeft(
                array('g' => 'study_groups_users'),
                'g.group_id = sgc.group_id',
                array()
            )
            ->where('g.user_id = ?', $userId)
            ->where('sgc.group_id != ?', $groupId)
            ->where('sgc.course_id = ?', $courseId)
            ->where('sgc.lesson_id = ?', 0);
        return $select->query()->fetchAll();
    }

    public function isUserLessonInOtherGroup($groupId, $lessinId, $userId )
    {
        /* проверка или пользователь записан на этот же курс в другой группе */
        $select = $this->getSelect();
        $select->from(
            array('sgc' => 'study_groups_courses'),
            array('sgc.*')
        )
            ->joinLeft(
                array('g' => 'study_groups_users'),
                'g.group_id = sgc.group_id',
                array()
            )
            ->where('g.user_id = ?', $userId)
            ->where('sgc.group_id != ?', $groupId)
            ->where('sgc.lesson_id = ?', $lessinId);
        return $select->query()->fetchAll();
    }

    public function getGroupCoursesIds($groupId)
    {
        $output = array();
        foreach($this->fetchAll($this->quoteInto('group_id = ? AND lesson_id = 0',$groupId)) as $id) {
            $output []= $id->course_id;
        }
        return $output;
    }

    public function getGroupLessonsIds($groupId)
    {
        $output = array();
        foreach($this->fetchAll($this->quoteInto('group_id = ? AND lesson_id > 0',$groupId)) as $id) {
            $output []= $id->lesson_id;
        }
        return $output;
    }

    public function getGroupsByLesson($lessonId)
    {
        $output = array();
        foreach($this->fetchAll($this->quoteInto('lesson_id = ?',$lessonId)) as $id) {
            $output []= $id->group_id;
        }
        return $output;
    }

    public function getLessonsCoursesIds($groupId, $courseId)
    {
        $output = array();
        foreach($this->fetchAll(array('group_id = ?' => $groupId, 'course_id = ?' => $courseId, 'lesson_id > ?' => 0)) as $id) {
            $output []= $id->lesson_id;
        }
        return $output;
    }

    public function getCourseGroups($courseId)
    {      
        $courses = $this->fetchAll($this->quoteInto('course_id = ? AND lesson_id = 0', $courseId));
        if ($courses->getList('group_id')) {
            return $this->getService('StudyGroup')->fetchAll(array('group_id IN (?)' => $courses->getList('group_id')));
        }
    }


    public function assignUser($groupId, $userId)
    {

        $programmCourses = $this->getService('StudyGroupProgramm')->assignUser($groupId, $userId);
        foreach($this->getGroupCoursesIds($groupId) as $courseId) {
            /* Нельзя зачислить повторно курс если он в составе программы */
            if (!in_array($courseId, $programmCourses)) {
                $this->getService('Subject')->assignStudent($courseId, $userId);
            }
        }
        foreach($this->getGroupLessonsIds($groupId) as $lessonId) {
            $this->getService('Lesson')->assignStudent($lessonId, $userId, null);
        }

    }

    public function unassignUser($groupId, $userId)
    {
        /* Удаление c курсов группы */
        foreach($this->getGroupCoursesIds($groupId) as $courseId) {
            if (!$this->isUserCourseInOtherGroup($groupId, $courseId, $userId )) {
                $this->getService('Subject')->unassignStudent($courseId, $userId);
                /* Удаление пользователя из подгрупп в курсе */
                $this->getService('GroupAssign')->deleteBy(array('mid=?' => $userId, 'cid=?' => $courseId));
            }
        }

        /* Удаление c занятий группы */
        foreach($this->getGroupLessonsIds($groupId) as $lessonId) {
            if (!$this->isUserLessonInOtherGroup($groupId, $lessonId, $userId )) {
                $this->getService('Lesson')->unassignStudent($lessonId, $userId);
            }
        }

        $this->getService('StudyGroupProgramm')->unassignUser($groupId, $userId);

    }
}