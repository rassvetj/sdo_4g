<?php

class HM_Webinar_Files_FilesModel extends HM_Model_Abstract
{
    public function getUrl()
    {
        $parts = pathinfo($this->name);
        if (isset($parts['extension'])) {
            return Zend_Registry::get('config')->path->upload->files.$this->file_id.'.'.$parts['extension'];
        }
        return false;
    }

    static public function getImgExtensions()
    {
        return array('jpeg', 'jpg', 'bmp', 'png', 'gif');
    }
}