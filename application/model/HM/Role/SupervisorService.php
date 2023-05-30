<?php
class HM_Role_SupervisorService extends HM_Service_Abstract
{

    public function assign($mid)
    {
        if($mid > 0){
            $res = $this->fetchAll(array('user_id = ?' => $mid));

            if(count($res) > 0 ){
                return true;
            }else{
                $this->insert(array('user_id' => $mid));
                return true;
            }
        }
        return false;
    }

    // сейчас подчиненными супервайзера являются enduser'ы из его подразделения, без вложенности
    public function isResponsibleFor($userId, $supervisorId = null)
    {
        if (!$supervisorId) {
            $supervisorId = $this->getService('User')->getCurrentUserId();
        }
        $department = $this->getService('Orgstructure')->fetchAllDependence('Sibling', array('mid = ?' => $userId, 'type = ?' => HM_Orgstructure_OrgstructureModel::TYPE_POSITION))->current();
        if (count($department->siblings)) {
            foreach ($department->siblings as $sibling) {
                if ($sibling->mid == $userId) return true;
            }
        }
        return false;
    }
    
    // метод взят из beeline, но логика совсем другая
    public function getSlaves($userId = null)
    {
        if (null === $userId) {
            $userId = $this->getService('User')->getCurrentUserId();
        }
        $user = $this->getOne($this->getService('User')->findDependence('Position', $userId));
        $slaves = array();
        if ($user && count($user->positions)) {
            $position = $user->positions->current();
            $department = $this->getOne($this->getService('Orgstructure')->find($position->owner_soid));
            if ($department) {
                if (count($collection = $this->getService('Orgstructure')->fetchAll(array(
                        'lft > ?' => $department->lft,
                        'rgt < ?' => $department->rgt,
                        'type = ?' => HM_Orgstructure_OrgstructureModel::TYPE_POSITION,
                )))) {
                    $slaves = $collection->getList('mid');
                }
            }
        }
        return $slaves;
    }
    // метод взят из beeline, но логика совсем другая    
    public function filterSelectForSlavesOnly(Zend_Db_Select $select, $where, $supervisorId = null)
    {
        if (null === $supervisorId) $supervisorId = $this->getService('User')->getCurrentUserId();
    
        $slavesIds = $this->getSlaves($supervisorId);
        if (count($slavesIds)) {
            $select->where($where, $slavesIds);
        }
        return $select;
    }
    
}