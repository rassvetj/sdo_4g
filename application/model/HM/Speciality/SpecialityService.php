<?php

class HM_Speciality_SpecialityService extends HM_Service_Abstract
{
    public function createGroupAssign($specialityId, $groupId, $level = 0)
    {
        return $this->getService('SpecialityGroup')->insert(
            array(
                'trid'  => $specialityId,
                'level' => $level,
                'gid'   => $groupId
            )
        );
    }

    public function createStudentAssign($specialityId, $studentId, $level = 0)
    {
        return $this->getService('SpecialityAssign')->insert(
            array(
                'trid'  => $specialityId,
                'level' => $level,
                'mid'   => $studentId
            )
        );
    }

    public function assignStudent($specialityId, $studentId, $level = 0)
    {
        $speciality = $this->getOne($this->findDependence(array('Assign', 'CourseAssign'), $specialityId));
        if ($speciality) {
            if (!$speciality->isUserExists($studentId, $level)) {
                $this->createStudentAssign($specialityId, $studentId, $level);
                $courses = $speciality->getCourses();
                if (count($courses)) {
                    foreach($courses as $course) {
                        if ($course->level != $level) continue;
                        $this->getService('Course')->assignStudent($course->cid, $studentId);
                    }
                }
            }
        }
    }

    public function assignGroup($specialityId, $groupId, $level = 0)
    {
        $speciality = $this->getOne($this->findDependence('GroupAssign', $specialityId));
        
        if ($level < 0) {
        	$level = 0;
        }
        
        if ($level > $speciality->number_of_levels) {
        	$level = $speciality->number_of_levels;
        }
        
        if ($speciality) {
            if (!$speciality->isGroupExists($groupId, $level)) {
                $this->unassignGroupStudents($groupId);
                if ($speciality->isGroupExists($groupId)) {
                    $specialityGroupAssign = $this->getOne($this->getService('SpecialityGroup')->fetchAll(sprintf("trid = '%d' AND gid = '%d'", $specialityId, $groupId)));
                    $specialityGroupAssign->level = $level;
                    $this->getService('SpecialityGroup')->update($specialityGroupAssign->getValues());
                } else {
                    $this->createGroupAssign($specialityId, $groupId, $level);
                }
            }

            $specialityGroupAssign = $this->getOne($this->getService('SpecialityGroup')->fetchAll(sprintf("trid = '%d' AND gid = '%d'", $specialityId, $groupId)));
            $groupStudentsAssigns = $this->getService('GroupAssign')->fetchAll($this->quoteInto('gid = ?', $groupId));

            if (count($groupStudentsAssigns)) {
                foreach($groupStudentsAssigns as $assign) {
                    $this->assignStudent($specialityId, $assign->mid, $specialityGroupAssign->level);
                }
            }
            
        }
    }

    public function unassignStudent($specialityId, $studentId, $level = 0)
    {
        $speciality = $this->getOne($this->findDependence(array('Assign', 'CourseAssign'), $specialityId));
        if ($speciality) {
            if ($speciality->isUserExists($studentId, $level)) {
                $this->getService('SpecialityAssign')->deleteBy(sprintf("mid = '%d' AND trid = '%d' AND level = '%d'"));

                $courses = $speciality->getCourses($level);
                if (count($courses)) {
                    foreach($courses as $course) {
                        $this->getService('Course')->unassignStudent($course->cid, $studentId);
                    }
                }
            }
        }
    }

    public function unassignGroupStudents($groupId)
    {
        $specialityGroupAssign = $this->getOne($this->getService('SpecialityGroup')->fetchAll(sprintf("gid = '%d'", $groupId)));
        $groupStudentsAssigns = $this->getService('GroupAssign')->fetchAll($this->quoteInto('gid = ?', $groupId));
        if (count($groupStudentsAssigns)) {
            foreach($groupStudentsAssigns as $assign) {
                $this->unassignStudent($specialityGroupAssign->trid, $assign->mid, $specialityGroupAssign->level);
            }
        }
    }

    public function unassignGroup($groupId)
    {
        $this->unassignGroupStudents($groupId);

        return $this->getService('SpecialityGroup')->deleteBy(sprintf("gid = '%d'", $groupId));

    }
}