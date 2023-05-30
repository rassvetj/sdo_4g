<?php
class HM_Faq_FaqModel extends HM_Model_Abstract
{
    const STATUS_UNPUBLISHED = 0;
    const STATUS_PUBLISHED = 1;

    static public function getStatuses()
    {
        return array(
            self::STATUS_UNPUBLISHED => _('Не опубликован'),
            self::STATUS_PUBLISHED => _('Опубликован')
        );
    }
}