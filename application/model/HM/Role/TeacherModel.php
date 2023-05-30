<?php

class HM_Role_TeacherModel extends HM_Role_RoleModelAbstract
{
    public function getCourse()
    {
        if (isset($this->courses) && count($this->courses)) {
            return $this->courses[0];
        }
        return false;
    }

    public function getUser()
    {
        if (isset($this->teachers) && count($this->teachers)) {
            return $this->teachers[0];
        }

        return false;
    }
}