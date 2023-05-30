<?php

class HM_Role_State_Validator_Student extends HM_State_Action_Validator
{
    public function validate($params)
    {
        $userId = $this->getService('User')->getCurrentUserId();

        $model = $this->getState()->getProcess()->getModel();

        if($model->MID == $userId){
            return true;
        }else{
            return false;
        }
    }

}
