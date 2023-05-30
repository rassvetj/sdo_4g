<?php
class HM_Poll_Feedback_FeedbackModel extends HM_Model_Abstract
{
    const STATUS_SENT       = 0;
    const STATUS_CANCELED   = 1;
    const STATUS_INPROGRESS = 2;
    const STATUS_DONE       = 3;

    static public function getStatuses()
    {
        return array(
            self::STATUS_SENT       => _('Отправлено'),
            self::STATUS_CANCELED   => _('Отменено'),
            self::STATUS_INPROGRESS => _('Прерван'),
            self::STATUS_DONE       => _('Заполнен')
        );
    }
}
