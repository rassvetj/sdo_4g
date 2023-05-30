<?php
class HM_View_Helper_RoomType extends Zend_View_Helper_Abstract
{
    public function roomType($type)
    {
        $types = HM_Room_RoomModel::getTypes();
        return $types[$type];
    }
}