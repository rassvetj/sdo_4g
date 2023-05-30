<?php
class HM_Library_Item_FileItemModel extends HM_Library_Item_ItemModel
{
    const CONTENT_AICC = 'AICC';

    public function getUrl()
    {
        return Zend_Registry::get('config')->url->base.'library'.$this->filename;
    }

    public function getContentType()
    {
        return $this->content;
    }
}