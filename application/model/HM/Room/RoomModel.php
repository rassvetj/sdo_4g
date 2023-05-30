<?php
class HM_Room_RoomModel extends HM_Model_Abstract
{
    const STATUS_DISABLED = 0;
    const STATUS_ENABLED  = 1;

    static public function getStatuses()
    {
        return array(
            self::STATUS_DISABLED => _('Недоступна'),
            self::STATUS_ENABLED => _('Доступна')
        );
    }

    static public function getTypes()
    {
        return array(
            0 => _('Лекционная аудитория'),
            1 => _('Семинарская аудитория'),
            2 => _('Учебный класс'),
            3 => _('Лаборатория'),
            4 => _('Рабочее помещение')
        );
    }

    public function getStatus($status = null)
    {
        if (null == $status) {
            $status = $this->status;
        }
        $statuses = self::getStatuses();
        return $statuses[$status];
    }

    public function getType($type = null)
    {
        if (null == $type) {
            $type = $this->type;
        }
        $types = self::getTypes();
        return $types[$type];
    }
}