<?php

class HM_Role_ClaimantModel extends HM_Role_RoleModelAbstract
{
    const TYPE_LMS = 0;
    const TYPE_SAP = 1;

    const STATUS_NEW = 0;
    const STATUS_REJECTED = 1;
    const STATUS_ACCEPTED = 2;

    protected $_primaryName = 'SID';

    static function getTypes()
    {
        return array(
            self::TYPE_LMS => _('СДО'),
            self::TYPE_SAP => _('SAP')
        );
    }

    static function getType($value)
    {
        $types = self::getTypes();
        if ( !array_key_exists($value, $types)) {
            return '<Тип не определен>';
        }
        return $types[$value];
    }

    static function getStatuses()
    {
        return array(
            self::STATUS_NEW => _('Активная'),
            self::STATUS_REJECTED => _('Отклонена'),
            self::STATUS_ACCEPTED => _('Принята')
        );
    }

    static function getStatus($status)
    {
        $statuses = self::getStatuses();
        if ( !array_key_exists($status, $statuses)) {
            return '<Статус не определен>';
        }
        return $statuses[$status];
    }

    public function getSubject()
    {
        if ($this->subject) {
            return $this->subject[0];
        }
    }

    public function getUser()
    {
        if ($this->user) {
            return $this->user[0];
        }
    }


    public function getName()
    {

       return implode(' ', array($this->lastname, $this->firstname, $this->patronymic));

    }

}