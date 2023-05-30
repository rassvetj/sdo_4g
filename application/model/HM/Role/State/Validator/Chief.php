<?php

class HM_Role_State_Validator_Chief  extends HM_State_Action_Validator
{
    public function validate($params)
    {
        $order = $this->getState()->getProcess()->getModel();

        $userId = $this->getService('User')->getCurrentUserId();
        $orderUser = $this->getService('User')->find($order->MID)->current();

        if($orderUser && $orderUser->head_mid == $userId){
            return true;
        }

        return false;
    }

}
