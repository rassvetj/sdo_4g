<?php

class HM_State_Action_Validator_Roles extends HM_State_Action_Validator
{

    /**
     *  For role validating
     *  @param $params  array with roles
     *  @return mixed
     */
    public function validate($params)
    {
        $currentRole = $this->getService('User')->getCurrentUserRole();
        return $this->getService('Acl')->inheritsRole($currentRole, $params);
    }


}
