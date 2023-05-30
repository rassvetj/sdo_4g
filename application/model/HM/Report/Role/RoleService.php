<?php
class HM_Report_Role_RoleService extends HM_Service_Abstract
{

    public function assignRole($report_id, $role){
        $report_id = intval($report_id);
        $roles = HM_Role_RoleModelAbstract::getBasicRoles(false, true);

        $result = false;

        if ($report_id <= 0) {
            return false;
        }

        $arrRoles = (is_array($role)) ? $role : (array) $role;

        foreach ($arrRoles as $role) {
            if(!isset($roles[$role])){
                continue;
            }

            $collection = $this->fetchAll($this->quoteInto(array('report_id = ? AND ', 'role = ?'), array($report_id, $role)));
            if (!count($collection)) {
                $this->insert(array(
                    'report_id' => $report_id,
                    'role' => $role
                ));
                $result = true;
            }
        }

        return $result;
    }

    public function removalRole($report_id, $role)
    {
        $report_id = intval($report_id);
        $roles = HM_Role_RoleModelAbstract::getBasicRoles(false);

        $result = false;

        if ($report_id <= 0) {
            return false;
        }

        $arrRoles = (is_array($role)) ? $role : (array) $role;

        foreach ($arrRoles as $role) {
            $this->deleteBy(array(
                    'report_id = ?' => $report_id,
                    'role = ?' => $role
            ));
        }

        return true;
    }

    public function removalAllRoles($report_id)
    {
        $this->deleteBy(array(
                'report_id = ?' => $report_id,
        ));

        return true;
    }

}