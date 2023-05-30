<?php

abstract class HM_State_Abstract
{

    const STATE_STATUS_PASSED     = 0;
    const STATE_STATUS_CONTINUING = 1;
    const STATE_STATUS_WAITING    = 2;
    const STATE_STATUS_FAILED     = 3;


    const ACTION_TYPE_LINK   = 0;
    const ACTION_TYPE_SELECT = 1;


    //protected $_state = null;

    protected $_params = array();

    protected $_status = null;

    protected $_nextState = null;


    protected $_process   = null;


    public function __construct($process)
    {
        $this->_process = $process;
    }


    public function setParams($params)
    {
        $this->_params = $params;
    }

    public function getParams()
    {
        return $this->_params;
    }

    public function setStatus($status)
    {
        $this->_status = $status;
    }

    public function getStatus()
    {
        return $this->_status;
    }

    public function getClass()
    {
        $classes = $this->getClasses();
        return $classes[$this->getStatus()];
    }

    public function getClasses()
    {
        return array(
            HM_State_Abstract::STATE_STATUS_PASSED     => 'success',
            HM_State_Abstract::STATE_STATUS_CONTINUING => 'complete',
            HM_State_Abstract::STATE_STATUS_WAITING    => 'freeze',
            HM_State_Abstract::STATE_STATUS_FAILED     => 'failed'

        );
    }



    public function setNextState($nextState)
    {
        $this->_nextState = $nextState;
    }

    public function getNextState()
    {
        return $this->_nextState;
    }


    abstract public function isNextStateAvailable();
    abstract public function init();
    abstract public function onNextState();
    abstract public function getActions();
    abstract public function getDescription();
    abstract public function initMessage();
    abstract public function onNextMessage();
    abstract public function onErrorMessage();
    abstract public function getCompleteMessage();

    public function getAchievedStateMessage() 
    {
        return '';
    }

    public function getCurrentStateMessage() 
    {
        return '';
    }


    public function getProcess()
    {
        return $this->_process;
    }


    public function getService($name)
    {
        return Zend_Registry::get('serviceContainer')->getService($name);
    }

    public function getResultMessage()
    {
        return ''; // #11251
    }

}
