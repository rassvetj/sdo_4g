<?php
class HM_Role_Custom_CustomService extends HM_Service_Abstract
{
    public function getList()
    {
        $roles = array();
        $collection = $this->fetchAll();

        if (count($collection)) {
            foreach($collection as $role) {
                $roles[HM_Role_Custom_CustomModel::PREFIX.$role->pmid] = $role->name;
            }
        }

        return $roles;
    }

    public function getBasicRole($profileId)
    {
        $profile = $this->getOne($this->find($profileId));
        if ($profile) {
            return $profile->type;
        }
        return HM_Role_RoleModelAbstract::ROLE_GUEST;
    }
}