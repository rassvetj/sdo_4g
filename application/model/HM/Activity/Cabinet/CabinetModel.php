<?php
class HM_Activity_Cabinet_CabinetModel extends HM_Model_Abstract
{
    public function getActivityName()
    {
        return $this->activity_name;
    }

    public function getActivitySubjectName()
    {
        return empty($this->subject_name) ? null : $this->subject_name;
    }

    public function getActivitySubjectId()
    {
        return $this->subject_id;
    }

    public function getActivityLessonId()
    {
        return $this->lesson_id;
    }
}