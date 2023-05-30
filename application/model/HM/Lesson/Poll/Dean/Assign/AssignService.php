<?php
class HM_Lesson_Poll_Dean_Assign_AssignService extends HM_Service_Abstract
{

    public function assignStudent($lessonId, $studentId, $headId)
    {
        return $this->insert(
            array(
                    'lesson_id'   => $lessonId,
                    'student_mid' => $studentId,
                    'head_mid'    => $headId
            )
        );
    }
    
    public function unassignStudent($lessonId, $studentId)
    {
        return $this->deleteBy(array('lesson_id = ?' => $lessonId, 'student_mid = ?' => $studentId));
    }
    
    
    public function unassignStudents($lessonId)
    {
        return $this->deleteBy(array('lesson_id = ?' => $lessonId));
    }
    
    public function assignStudents($lessonId, $students, $headId, $unassign = true)
    {
        if (is_array($students) && count($students)) {
            $assigns = $this->fetchAll($this->quoteInto('lesson_id = ? AND student_mid > 0', $lessonId));
            if (count($assigns)) {
                foreach($assigns as $assign) {
                    if (in_array($assign->student_mid, $students)) {
                        $key = array_search($assign->student_mid, $students);
                        if (false !== $key) {
                            unset($students[$key]);
                        }
                    } else {
                        if($unassign == true){
                            $this->unassignStudent($lessonId, $assign->student_mid);
                        }
                    }
                }

            }
            
            foreach($students as $studentId) {
                $this->assignStudent($lessonId, $studentId, $headId);
            }
        }
    }
    
    



    
}