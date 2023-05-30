<?php

class HM_Library_Item_ItemModel extends HM_Module_ModuleModelAbstract
{

    static public function factory($data)
    {
        if (isset($data['type'])) {
            switch($data['type']) {
                case 1:
                    return parent::factory($data,  'HM_Library_Item_BookItemModel');
                    break;
                case 2:
                    return parent::factory($data,  'HM_Library_Item_CdItemModel');
                    break;
                default:
                    return parent::factory($data,  'HM_Library_Item_FileItemModel');
            }
        }
        return parent::factory($data,  'HM_Library_Item_FileItemModel');
    }

    public function getUrl()
    {
        return false;
    }

    public function getScormParams()
    {
        if (strlen($this->scorm_params)) {
            return unserialize($this->scorm_params);
        }
        return array();
    }
}