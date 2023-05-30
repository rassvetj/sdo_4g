<?php

class HM_Role_State_Fail extends HM_State_Abstract
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
        $params = $this->getParams();
        $this->getProcess()->setStatus(HM_Process_Abstract::PROCESS_STATUS_FAILED);
        $this->getService('Claimant')->reject($this->getProcess()->getModel()->SID, $params['message']);
        return true;
    }

    public function onNextState()
    {
        return false;
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
        return _('Заявка отклонена.');
    }

    public function onNextMessage()
    {
        return false;
    }

    public function getCompleteMessage()
    {
        return _('Заявка отклонена.');
    }

}
