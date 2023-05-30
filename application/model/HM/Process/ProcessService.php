<?php

class HM_Process_ProcessService extends HM_Service_Abstract
{
    protected $_processes = null;


    public function prepareProcesses()
    {
        if($this->_processes == null){
            $this->_processes = new HM_Config_Xml(APPLICATION_PATH . '/settings/processes.xml');
        }
    }

    public function getProcessesConfig()
    {
        $this->prepareProcesses();
        return $this->_processes;
    }

    public function getProcess($type)
    {
        $conf = $this->getProcessesConfig();

        foreach($conf as $val){
            if($val->process_type == $type){
                return $val;
            }
        }
        return false;
    }


    public function getProcessStates($type, $all = true)
    {
        $process = $this->getProcess($type);

        $ret = array();
        foreach($process->states->state as $state){
            if($all == true || ($state->visible != "false" || $state->visible == "true"))
                $ret[$state->class] = _($state->name);
        }
        return $ret;
    }

    public function getProccessTypes()
    {
        $conf = $this->getProcessesConfig();

        $res = array();

        foreach($conf as $val){
            $res[$val->process_type] = _($val->name);
        }
        return $res;
    }

    public function getItemId($model)
    {
        $process = $model->getProcess();

        $processType = $process->getType();

        $primary = null;

        switch($processType){
            case HM_Process_ProcessModel::PROCESS_ORDER:
                $primary = 'SID';
                break;
            case HM_Process_ProcessModel::PROCESS_VACANCY:
                $primary = 'at_vacancy_id';
                break;
            case HM_Process_ProcessModel::PROCESS_VACANCY_ASSIGN:
                $primary = 'at_vacancy_candidate_id';
                break;

            default:
                return false;
        }
        return $model->$primary;
    }



    public function initProcess($model)
    {

        $process = $model->getProcess();

        $processType = $process->getType();

        $itemId = $this->getItemId($model);

        $currentState = $this->getService('State')->getCurrentState($processType, $itemId);

        if($currentState){
            $currentProcess = $this->getService('Process')->find($currentState->process_id)->current();

            if($currentProcess){
                $process->init($currentProcess, $currentState, $model);
                return true;
            }
        }

        return false;
    }


    public function startProcess($model, $processId, $params = array())
    {
        $collection = $this->find($processId);

        if(count($collection)){
            
            $process = $collection->current();
            $chain = unserialize($process->chain);

            $this->getService('State')->insert(
                array(
                    'item_id'       => $this->getItemId($model),
                    'process_id'    => $processId,
                    'process_type'  => $process->type,
                    'current_state' => key($chain),
                    'status'        => HM_Process_Abstract::PROCESS_STATUS_INIT,
                    'params'        => serialize($params)

                )
            );
            return true;
        }

        return false;
    }



    public function goToNextState($model)
    {
        $process = $model->getProcess();

        $res = $process->goToNextState();

        if ($res == true) {

            $itemId = $this->getItemId($model);

            $arr = array(
                'state_of_process_id' => $process->getStateId(),
                'current_state' => get_class($process->getCurrentState()),
                'status' => HM_Process_Abstract::PROCESS_STATUS_CONTINUING
            );

            if (substr(get_class($process->getCurrentState()), -9) == '_Complete') {
                $arr['status'] = HM_Process_Abstract::PROCESS_STATUS_COMPLETE;
            }
            $this->getService('State')->update($arr);
        }

    }

    public function goToFail($model)
    {
        $process = $model->getProcess();

        $process->goToFail();

        $arr = array(
            'state_of_process_id' => $process->getStateId(),
            'current_state'       => get_class($process->getCurrentState()) ,
            'status'              => HM_Process_Abstract::PROCESS_STATUS_FAILED
        );
        $this->getService('State')->update($arr);
    }

}