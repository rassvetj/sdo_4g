<?php
class HM_Lesson_Poll_Dean_Leader_LeaderService extends HM_Lesson_Poll_Dean_DeanService
{
    public function assignStudents($lessonId, $students, $unassign = true)
    {
        $lesson = $this->getService('Lesson')->getOne($this->getService('Lesson')->find($lessonId));
        if ($lesson && count($students)) {
            $collection = $this->getService('User')->fetchAll($this->quoteInto('MID IN (?)', $students));
            $students = $leaders = array();
            if (count($collection)) {
                foreach($collection as $user) {
                    if ($user->head_mid > 0) {
                        $students[$user->head_mid] = $user->head_mid;
                        $leaders[$user->head_mid][$user->MID] = $user->MID;
                    }
                }

                if (count($leaders)) {
                    foreach($leaders as $leaderId => $slaves) {
                        $this->getService('LessonDeanPollAssign')->assignStudents($lessonId, $slaves, $leaderId, $unassign);
                    }
                }
            }

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
            $this->_sendAssignStudentsMessage($lesson, $students, HM_Messenger::TEMPLATE_POLL_LEADERS, $leaders);

            return true;

        }
        return false;
    }
    
    public function isStudentAssigned($studentId, $lessonId)
    {
        
        $res = $this->getService('LessonDeanPollAssign')->fetchAll(array('student_mid = ?' => $studentId, 'lesson_id = ?' => $lessonId));
        
        if(count($res) > 0)
            return true;
        else 
            return false;
    }

    public function getAvailableStudents($subjectId)
    {
        return $this->getService('Subject')->getAssignedGraduated($subjectId)->getList('MID', 'MID');
    }
    
}