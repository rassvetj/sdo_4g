<?php

class HM_State_StateService extends HM_Service_Abstract
{

    public function getStatesTypes($type)
    {
        $process = $this->getService('Process')->getProcess($type);
        $res = array();
        foreach($process->states->state as $val){
            $res[$val->class] = _($val->name);
        }

        return $res;
    }



    public function setStates($model)
    {

        $class = get_class($model);

        switch($class){
            case 'HM_Role_ClaimantModel':
                $type = HM_Process_ProcessModel::PROCESS_ORDER;

                $currentState = $this->fetchAll(array('item_type = ?' => $type, 'item_id = ?' => $model->SID ));

                $params = unserialize($currentState->params);

                $process =  $this->getService('Process')->fetchAll(array('process_id = ?' => $currentState->process_id));
                $states = unserialize($process->chain);

                $statesArr = array();

                $current = false;
                foreach($states as $key => $val){
                    if($current == false && $currentState->current_state == $key){

                         $obj = new $key($params[$key]);
                         $obj->setStatus(HM_State_Abstract::STATE_STATUS_CURRENT);
                         $statesArr[] = $obj;
                        $current = true;
                    }elseif($current == false){
                        $obj = new $key($params[$key]);
                        $obj->setStatus(HM_State_Abstract::STATE_STATUS_WAIT);
                        $statesArr[] = $obj;
                    }else{
                        $obj = new $key($params[$key]);
                        $obj->setStatus(HM_State_Abstract::STATE_STATUS_SUCCESS);
                        $statesArr[] = $obj;
                    }


                }
                $model->setStates($statesArr);


            break;


        }





    }



   public function getCurrentState($processType, $itemId)
   {
        return $this->getOne(
            $this->fetchAll(
                array(
                    'process_type = ?' => $processType,
                    'item_id = ?' => $itemId
                )
            )
        );


   }


}