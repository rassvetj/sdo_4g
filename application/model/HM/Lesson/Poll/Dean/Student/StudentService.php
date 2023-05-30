<?php
class HM_Lesson_Poll_Dean_Student_StudentService extends HM_Lesson_Poll_Dean_DeanService
{
        public function assignStudents($lessonId, $students, $unassign = true)
    {
        $lesson = $this->getService('Lesson')->getOne($this->getService('Lesson')->find($lessonId));
        if ($lesson) {

             parent::assignStudents($lessonId, $students, $unassign);

            /**
             * Посылаем уведомление о назначении кураторского опроса
             */
            $this->_sendAssignStudentsMessage($lesson, $students, HM_Messenger::TEMPLATE_POLL_STUDENTS);

            return true;

        }
        return false;
    }

    public function getAvailableStudents($subjectId)
    {
        return $this->getService('Subject')->getAssignedGraduated($subjectId)->getList('MID', 'MID');
    }
}