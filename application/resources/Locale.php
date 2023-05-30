<?php
class HM_Resource_Locale extends Zend_Application_Resource_Locale
{
    public function init()
    {
        $this->getBootstrap()->bootstrap('config');
        $this->getBootstrap()->bootstrap('db');
        $this->getBootstrap()->bootstrap('container');

        $serviceContainer = $this->getBootstrap()->getResource('container');

        $locale = parent::init();

        $lang = $serviceContainer->getService('User')->getCurrentLang();

        if ($lang && ($lang['locale'] != $locale)) {
            $locale->setLocale($lang['locale']);
            //Zend_Registry::get('translate')->setLocale($locale);
        }

        //Zend_Registry::set('locale', $locale);

        return $locale;
    }
}