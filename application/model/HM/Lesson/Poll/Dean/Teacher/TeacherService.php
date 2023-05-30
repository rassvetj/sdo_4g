<?php
class HM_Lesson_Poll_Dean_Teacher_TeacherService extends HM_Lesson_Poll_Dean_DeanService
{
    public function assignStudents($lessonId, $students, $unassign = true)
    {
        $lesson = $this->getService('Lesson')->getOne($this->getService('Lesson')->find($lessonId));
        if ($lesson) {
            $students = $this->getService('Subject')->getAssignedTeachers($lesson->CID)->getList('MID', 'MID');

            if (count($students)) {
                $this->getService('LessonAssign')->deleteBy(
                    $this->quoteInto(
                        array('SHEID = ?', ' AND MID IN (?)'),
                        array($lessonId, $students)
                    )
                );
            }

            parent::assignStudents($lessonId, $students, $unassign);

            /**
             * Посылаем уведомление о назначении кураторского опроса
             */
            $this->_sendAssignStudentsMessage($lesson, $students, HM_Messenger::TEMPLATE_POLL_TEACHERS);

            return true;

        }
        return false;
    }

    public function getAvailableStudents($subjectId)
    {
        return $this->getService('Subject')->getAssignedGraduated($subjectId)->getList('MID', 'MID');
    }
}