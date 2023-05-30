<?php
class HM_Role_TeacherService extends HM_Service_Abstract
{
    public function isUserExists($subjectId, $userId)
    {
        $collection = $this->fetchAll( array('CID = ?' => $subjectId, 'MID = ?' => $userId)
            //$this->quoteInto(array('CID = ?', 'MID = ?'), array($subjectId, $userId))
        );
        return count($collection);
    }

    public function getSubjects($userId = null)
    {
        if (null === $userId) {
            $userId = $this->getService('User')->getCurrentUserId();
        }
        $collection = $this->fetchAll(array('MID = ?' => $userId));
        if (count($collection)) {
            $list = $collection->getList('CID','MID');
            return $this->getService('Subject')->fetchAll(array('subid IN(?)' => array_keys($list)), 'name');
        }
        return NULL;
        
        /*
        return $this->getService('Subject')->fetchAllDependenceJoinInner(
            'Teacher',
            $this->quoteInto('Teacher.MID = ?', $userId),
            'self.name'
        );*/
    }
}