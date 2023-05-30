<?php
class HM_Bookshelf_BookshelfModel extends HM_Model_Abstract
{
    const PUBLIC_STATUS_NO  = 0;
    const PUBLIC_STATUS_YES = 1;

    public static function getPublicStatusList()
    {
    	return array(
			self::PUBLIC_STATUS_NO  => _('Нет'),
			self::PUBLIC_STATUS_YES => _('Да'),
    	);
    }

    public function getInvertIsPublic()
    {
        return $this->isPublic == self::PUBLIC_STATUS_YES 
               ? self::PUBLIC_STATUS_NO
               : self::PUBLIC_STATUS_YES;
    }

}