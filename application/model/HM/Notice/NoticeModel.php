<?php
class HM_Notice_NoticeModel extends HM_Model_Abstract
{
	const USER = 0;
    const ADMIN  = 1;

    const TEMPLATE_SENDALL = 17;
    
	static public function getReceivers()
    {
        return array(
            self::USER => _('Пользователь'),
            self::ADMIN => _('Администрация')
        );
    }

}