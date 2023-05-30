<?php

abstract class HM_State_Action_Validator
{
    protected $_state = null;

    public function __construct($state)
    {
        $this->_state = $state;
    }

    public function getState()
    {
        return $this->_state;
    }

    abstract public function validate($params);

    public function getService($name)
    {
        return Zend_Registry::get('serviceContainer')->getService($name);
    }

}
