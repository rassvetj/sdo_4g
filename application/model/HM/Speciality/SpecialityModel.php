<?php

class HM_Speciality_SpecialityModel extends HM_Model_Abstract
{
    public function getGroups()
    {
        $result = array();
        if (isset($this->groups)) {
            $result = $this->groups;
        }
        return $result;
    }

    public function getUsers()
    {
        $result = array();
        if (isset($this->users)) {
            $result = $this->users;
        }
        return $result;
    }

    public function getCourses()
    {
        $result = array();
        if (isset($this->courses)) {
            $result = $this->courses;            
        }
        return $result;
    }

    public function isGroupExists($groupId, $level = null)
    {
        $groups = $this->getGroups();
        if (count($groups)) {
            foreach($groups as $group) {
                if (null !== $level) {
                    if (($groupId == $group->gid) && ($level == $group->level)) return true;                    
                } else {
                    if ($groupId == $group->gid) return true;
                }
            }
        }
        return false;
    }

    public function isUserExists($studentId, $level = null)
    {
        $students = $this->getUsers();
        if (count($students)) {
            foreach($students as $student) {
                if (null !== $level) {
                    if ((($student->mid == $studentId)
                        || ($student->MID == $studentId))
                        && ($student->level == $level)) {
                         return true;
                     }
                } else {
                    if (($student->mid == $studentId)
                        || ($student->MID == $studentId)) {
                        return true;
                    }
                }
            }
        }
        return false;
    }
}