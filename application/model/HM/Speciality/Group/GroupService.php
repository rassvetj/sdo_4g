<?php

class HM_Speciality_Group_GroupService extends HM_Service_Abstract
{
    public function insert($data)
    {
    	if ($data['level'] == 0) {
            $data['updated'] = date('Y-m-d H:i:s');
    	}
        return parent::insert($data);
    }
}