<?php

class HM_Speciality_Assign_AssignService extends HM_Service_Abstract
{
    public function insert($data)
    {
        $data['started'] = $data['changed'] = time();
        return parent::insert($data);
    }
}