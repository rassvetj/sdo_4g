<?php

abstract class HM_Process_Abstract
{

    const PROCESS_STATUS_INIT       = 0;
    const PROCESS_STATUS_CONTINUING = 1;
    const PROCESS_STATUS_COMPLETE   = 2;
    const PROCESS_STATUS_FAILED     = 3;


    protected $_states    = array();

    protected $_status    = null;

    protected $_processId = null;

    protected $_stateId   = null;

    protected $_model     = null;


    abstract static public function getStatuses();



    public function init($processModel, $stateModel, $model)
    {

        $this->_processId = $processModel->process_id;
        $this->_stateId   = $stateModel->state_of_process_id;

        $this->_model     = $model;

        $chain  = unserialize($processModel->chain);
        $params = unserialize($stateModel->params);

        $status = HM_State_Abstract::STATE_STATUS_PASSED;

        if ($chain === false) return;

        foreach($chain as $key => $val){
            $temp = new $key($this);
            if($key == $stateModel->current_state){
                $temp->setStatus(($stateModel->status == HM_Process_Abstract::PROCESS_STATUS_FAILED)? HM_State_Abstract::STATE_STATUS_FAILED : HM_State_Abstract::STATE_STATUS_CONTINUING);
                $status = HM_State_Abstract::STATE_STATUS_WAITING;
                $temp->setParams($params[$key]);
                $temp->setNextState($val);
            }else{
                $temp->setStatus($status);
                $temp->setParams($params[$key]);
                $temp->setNextState($val);
            }
            $this->_states[] = $temp;
        }

    }


    public function setStatus($status)
    {
        $this->_status = $status;
        $this->updateData();
    }

    public function getStatus()
    {
        return $this->_status;
    }


    public function getStates()
    {
        return $this->_states;
    }


    abstract public function getType();

    public function getCurrentState()
    {
        $states = $this->getStates();
        foreach($states as $state){
            if($state->getStatus() == HM_State_Abstract::STATE_STATUS_CONTINUING){
                return $state;
            }
        }
        return false;
    }



    public function goToNextState()
    {
        $currentState = $this->getCurrentState();
        if($currentState){
            $nextClass = $currentState->getNextState();
            $nextState = null;

            $explode = explode('_', $nextClass);

            $states = $this->getStates();
            foreach($states as $state){
                if($state instanceof $nextClass){
                    $nextState = $state;
                }
            }

            if($nextState == null){
                $nextState = new $nextClass($this);
                $nextState->setStatus(HM_State_Abstract::STATE_STATUS_WAITING);
                $nextState->setParams(array());
            }


            if($currentState->isNextStateAvailable()){
                if($currentState->onNextState()){

                    $nextState->init();

                    $currentState->setStatus(HM_State_Abstract::STATE_STATUS_PASSED);
                    $nextState->setStatus(HM_State_Abstract::STATE_STATUS_CONTINUING);

                    $this->setStatus(self::PROCESS_STATUS_CONTINUING);

                    $this->updateData();
                    return $currentState->onNextMessage();
                }
            } else{
                return $currentState->onErrorMessage();
            }
        }


        return false;
    }


    public function goToFail($params)
    {
        $currentState = $this->getCurrentState();
        $class = get_class($currentState);
        $fail = explode('_', $class);
        $fail[count($fail) - 1] = 'Fail';
        $failClass = implode('_', $fail);
        $failState = new $failClass($this);
        //$currentState->setStatus(HM_State_Abstract::STATE_STATUS_PASSED);
        $failState->setParams($params);
        //$failState->setStatus(HM_State_Abstract::STATE_STATUS_CONTINUING);
        $failState->init();

        $this->updateData();

        return $failState;
    }

    public function getProcessId()
    {
        return $this->_processId;
    }

    public function getStateId()
    {
        return $this->_stateId;
    }

    public function updateData()
    {
        $services = Zend_Registry::get('serviceContainer');

        $params = array();

        foreach($this->getStates() as $state){
            $params[get_class($state)] = $state->getParams();
        }




        if($this->getCurrentState()){
            $services->getService('State')->update(
                array(
                    'state_of_process_id' => $this->getStateId(),
                    'current_state'       => get_class($this->getCurrentState()),
                    'status'              => $this->getStatus(),
                    'params'              => $params
                )
            );
        }else{
            $services->getService('State')->update(
                array(
                    'state_of_process_id' => $this->getStateId(),
                    //'current_state'       => get_class($this->getCurrentState()),
                    'status'              => $this->getStatus(),
                    'params'              => $params
                )
            );
        }

    }

    public function getModel()
    {
        return $this->_model;
    }


}
