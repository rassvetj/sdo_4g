<?
class HM_Role_State_Dean extends HM_State_Abstract
{
    public function isNextStateAvailable()
    {
        return true;
    }

    public function init()
    {
        // TODO: Implement init() method.
    }

    public function getActions()
    {
        if($this->getStatus() == HM_State_Abstract::STATE_STATUS_CONTINUING){
            return array(
                new HM_State_Action_Link(array('url' => array('module' => 'order', 'controller' => 'list', 'action' => 'accept', 'claimant_id' => $this->getProcess()->getModel()->SID) , 'title' => _('Согласовать заявку')), array('roles' => array(HM_Role_RoleModelAbstract::ROLE_DEAN)), $this),
                new HM_State_Action_Link(array('url' => array('module' => 'order', 'controller' => 'list', 'action' => 'reject', 'claimant_id' => $this->getProcess()->getModel()->SID), 'title' => _('Отклонить заявку')), array('roles' => array(HM_Role_RoleModelAbstract::ROLE_DEAN)), $this),
            );
        }else{
            return array();
        }

    }

    public function onNextState()
    {
        return true;
    }

    public function getDescription()
    {
        return _('Заявка находится на согласовании у организатора обучения.');
    }

    public function initMessage()
    {
        return;
    }

    public function onNextMessage()
    {
        return _('Заявка успешно согласована.');
    }

    public function getCompleteMessage()
    {
        return _('Заявка успешно согласована.');
    }

    public function onErrorMessage()
    {
        return _('Во время согласования произошла ошибка');
    }


}