<?php

class HM_Role_ClaimantProcess extends HM_Process_Abstract
{


    public function getType()
    {
        return HM_Process_ProcessModel::PROCESS_ORDER;
    }

    static public function getStatuses()
    {
        return array(
            self::PROCESS_STATUS_INIT       => _('Новая'),
            self::PROCESS_STATUS_CONTINUING => _('В работе'),
            self::PROCESS_STATUS_COMPLETE   => _('Принята'),
            self::PROCESS_STATUS_FAILED     => _('Отклонена'),
        );

    }



}