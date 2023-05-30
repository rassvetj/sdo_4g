<?php
class HM_Faq_FaqService extends HM_Service_Abstract
{
    public function publish($faqId)
    {
        return $this->update(
            array(
                'faq_id' => $faqId,
                'published' => HM_Faq_FaqModel::STATUS_PUBLISHED
            )
        );
    }

    public function unpublish($faqId)
    {
        return $this->update(
            array(
                'faq_id' => $faqId,
                'published' => HM_Faq_FaqModel::STATUS_UNPUBLISHED
            )
        );
    }

}