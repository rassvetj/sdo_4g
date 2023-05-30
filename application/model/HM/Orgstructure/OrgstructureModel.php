<?php
class HM_Orgstructure_OrgstructureModel extends HM_Model_Abstract
{
    const TYPE_DEPARTMENT = 0;
    const TYPE_POSITION = 1;

    const EMPLOYEE   = 0;
    const SUPERVISOR = 1;
	
	const PREFIX_STUDENT = 'ST_';
	const PREFIX_TEACHER = 'PR_';

    const DEFAULT_HEAD_STRUCTURE_ITEM_TITLE = 'Компания';

    protected $_primaryName = 'soid';
    
    public function getCardFields()
    {
        return array(
            'getTypeTitle()' => _('Тип'),
            'getOrgPath()' => _('Входит в'),
            'getUserName()'  => _('В должности')
        );
    }
    
    public function getName()
    {
        return $this->name;
    }

    static public function factory($data, $default = 'HM_Orgstructure_OrgstructureModel')
    {
        switch($data['type']) {
            case self::TYPE_POSITION:
                return parent::factory($data, 'HM_Orgstructure_Position_PositionModel');
                break;
            default:
                return parent::factory($data, 'HM_Orgstructure_Unit_UnitModel');
                break;
        }
    }

    static public function getTypes()
    {
        return array(
            self::TYPE_DEPARTMENT => _('Подразделение'),
            self::TYPE_POSITION => _('Штатная единица')
        );
    }

    public function getUser()
    {
        if (isset($this->user) && count($this->user)) {
            return $this->user[0];
        }
        return false;
    }

    public function setUser(HM_User_UserModel $user)
    {
        return $this->user = array($user);
    }

    public function getTypeTitle()
    {
        $types = self::getTypes();
        return $types[$this->type];
    }

    public function getUserName()
    {
        $user = $this->getUser();
        if ($user) {
            return $user->getName();
        }
        return '-';
    }

    public function isPosition()
    {
        return $this->type == self::TYPE_POSITION;
    }
    
    public function getOrgPath()
    {
        $collection = Zend_Registry::get('serviceContainer')->getService('Orgstructure')->fetchAll(array(
            'lft < ?' => $this->lft,        
            'rgt > ?' => $this->rgt,        
            'level < ?' => $this->level,        
        ), 'level');
        
        if (count($collection)) {
            $deps = $collection->getList('level', 'name');
            return implode('<br>', $deps);
        }
        return '';
    }

}