<?php
class HM_Lang_LangService
{
    public function countLanguages()
    {
        if (isset(Zend_Registry::get('config')->languages)) {
            return count(Zend_Registry::get('config')->languages->toArray());
        }
        return 0;
    }
}