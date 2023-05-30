<?php
class HM_Resource_Toolbar extends Zend_Application_Resource_ResourceAbstract
{
    public function init()
    {
        $this->getBootstrap()->bootstrap('config');
        $this->getBootstrap()->bootstrap('frontController');
        $this->getBootstrap()->bootstrap('db');
        $front = $this->getBootstrap()->getResource('frontController');
        if ($this->getBootstrap()->getResource('config')->debug) {
            $options = array(
                  'plugins'  => array('Variables',
                  'Database' => array('adapter' => $this->getBootstrap()->getResource('db')),
                  'File'     => array('basePath' => APPLICATION_PATH),
                  'Memory',
                  'Time',
                  'Registry',
                  'Exception',
                  'Variables')
            );

            $debug = new ZFDebug_Controller_Plugin_Debug($options);
            $front->registerPlugin($debug);
        }
    }

}