<?php
class HM_Group_GroupService extends HM_Service_Abstract
{

    public function assignStudents($groupId, $students)
    {
        $deletedStudents = array();
        $collection = $this->getService('GroupAssign')->fetchAll($this->quoteInto('gid = ?', $groupId));
        if (count($collection)) {
            $deletedStudents = $collection->getList('mid', 'mid');
        }

        $specialityAssign = false;
        $collection = $this->getService('SpecialityGroup')->fetchAll($this->quoteInto('gid = ?', $groupId));
        if (count($collection)) {
            $specialityAssign = $collection->current();
        }


        $this->getService('GroupAssign')->deleteBy($this->quoteInto('gid = ?', $groupId));

        if (is_array($students) && count($students)) {
            foreach($students as $studentId) {
                if (isset($deletedStudents[$studentId])) {
                    unset($deletedStudents[$studentId]);
                }

                $this->getService('GroupAssign')->insert(
                    array(
                        'mid' => (int) $studentId,
                        'cid' => -1,
                        'gid' => $groupId
                    )
                );
            }
        }

        if (count($deletedStudents) && $specialityAssign) {
            foreach($deletedStudents as $studentId) {
                $this->getService('Speciality')->unassignStudent(
                    $specialityAssign->trid,
                    $studentId,
                    $specialityAssign->level
                );
            }
        }

    }

    public function update($data)
    {
        if(isset($data['students'])){ 
            $this->assignStudents($data['gid'], (isset($data['students']) ? $data['students'] : null));
        }
        
        if (isset($data['students'])) {
            unset($data['students']);
        }

        return parent::update($data);
    }

    public function delete($id)
    {
        $this->getService('GroupAssign')->deleteBy($this->quoteInto('gid = ?', $id));
        $this->getService('SpecialityGroup')->deleteBy($this->quoteInto('gid = ?', $id));
        return parent::delete($id);
    }
}