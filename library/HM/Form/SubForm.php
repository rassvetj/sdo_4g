<?php
class HM_Form_SubForm extends HM_Form
{
    private $_session = null;
    private $_namespace = 'multiform';

    public function setSession($session)
    {
        $this->_session = $session;
    }

    public function getSession()
    {
        return $_SESSION[$this->_namespace];
        //return $this->_session;
    }

    public function hasPrevValue($subFormName, $name)
    {
        $session = $this->getSession();
        return isset($session[$subFormName][$name]);
    }

    public function getPrevValue($subFormName, $name)
    {
        $session = $this->getSession();
        return $session[$subFormName][$name];
    }

}