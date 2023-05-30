<?php
class HM_View_Helper_ReceiverType extends Zend_View_Helper_Abstract
{
    public function receiverType($type)
    {
        $types = HM_Notice_NoticeModel::getReceivers();
        return $types[$type];
    }
}