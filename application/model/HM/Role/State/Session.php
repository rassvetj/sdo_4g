<?
class HM_Role_State_Session extends HM_State_Abstract
{
    public function isNextStateAvailable()
    {
        $params  = $this->getParams();

        if($params['subject'] > 0){
            return true;
        }

        return false;
    }

    public function init()
    {
        // TODO: Implement init() method.
    }

    public function onNextState()
    {
        $params = $this->getParams();
        $this->getService('Claimant')->update(
            array(
                'SID' => $this->getProcess()->getModel()->SID,
                'CID' => $params['subject']
            )
        );

        return true;
    }


    public function getActions()
    {
        if($this->getStatus() == HM_State_Abstract::STATE_STATUS_CONTINUING){
            $params = $this->getParams();
            $value = $params['subject'];
            $sessions = $this->getService('Subject')->fetchAll(array('base_id = ?' => $this->getProcess()->getModel()->base_subject, 'base = ?' => HM_Subject_SubjectModel::BASETYPE_SESSION));

            $courses = $sessions->getList('subid', 'name');

            return array(
                array(
                    new HM_State_Action_Link(array('url' => array('module' => 'subject', 'controller' => 'list', 'action' => 'new', 'base' => HM_Subject_SubjectModel::BASETYPE_SESSION, 'subid' => $this->getProcess()->getModel()->base_subject), 'title' => _('Создать учебную сессию'), 'subject_id' => 0), array('roles' => array(HM_Role_RoleModelAbstract::ROLE_DEAN)), $this),
                ),
                array(
                    new HM_State_Action_Select(array('name' => 'subject', 'value' => $value, 'values' => $courses, 'text' => _('или выберите'), 'decorating' => HM_State_Action::DONT_DECORATE), array('roles' => array(HM_Role_RoleModelAbstract::ROLE_DEAN)), $this),
                ),
                array(
                    new HM_State_Action_Link(array('url' => array('module' => 'order', 'controller' => 'list', 'action' => 'accept', 'claimant_id' => $this->getProcess()->getModel()->SID) , 'title' => _('Согласовать заявку')), array('roles' => array(HM_Role_RoleModelAbstract::ROLE_DEAN)), $this),
                    new HM_State_Action_Link(array('url' => array('module' => 'order', 'controller' => 'list', 'action' => 'reject', 'claimant_id' => $this->getProcess()->getModel()->SID), 'title' => _('Отклонить заявку')), array('roles' => array(HM_Role_RoleModelAbstract::ROLE_DEAN)), $this),
                )
            );
        }else{
            return array();
        }
    }

    public function getDescription()
    {
        return _('Заявка находится в стадии формирования учебной сессии и может быть принята только после выбора конкретной учебной сессии.');
    }

    public function initMessage()
    {
        return '';
    }

    public function onNextMessage()
    {
        return _('Заявка принята.');
    }

    public function getCompleteMessage()
    {
        $params = $this->getParams();

        $subject = $this->getService('Subject')->find($params['subject'])->current();

        if($subject){
            $name = $subject->name;
        }

        return _('Заявка принята');
    }

    public function onErrorMessage()
    {
        return _('Заявка не принята.');
    }


}