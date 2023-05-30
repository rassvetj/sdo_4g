<?php

class HM_Role_State_Complete extends HM_State_Abstract
{
    public function onErrorMessage()
    {
        return '';
    }

    public function isNextStateAvailable()
    {
        return false;
    }

    public function init()
    {
        $this->getProcess()->setStatus(HM_Process_Abstract::PROCESS_STATUS_COMPLETE);
        $this->getService('Claimant')->accept($this->getProcess()->getModel()->SID);
        return true;
    }

    public function onNextState()
    {
        return true;
    }

    public function getActions()
    {
        return array();
    }

    public function getDescription()
    {
        return false;
    }

    public function initMessage()
    {
        return _('Заявка успешно согласована.');
    }

    public function onNextMessage()
    {
        return _('None');
    }

    public function getCompleteMessage()
    {
        return _('Заявка успешно согласована.');
    }

}
