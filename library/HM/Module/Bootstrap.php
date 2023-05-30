<?php

/**
 * Description of Bootstrap
 *
 * @author slava
 */
class HM_Module_Bootstrap extends Zend_Application_Module_Bootstrap {
    
    public function _initConfig() {
        /*@var $config HM_Config_Ini */
        $config = Zend_Registry::get('config');
        $class = get_class($this);
        $moduleBootstrapClassNameparts = explode("_", $class);
        $modulePath = APPLICATION_PATH."/modules/".strtolower($moduleBootstrapClassNameparts[0]);
        $moduleConfig = new HM_Config_Ini($modulePath . '/config/config.ini', APPLICATION_ENV, true, $modulePath . '/settings/config.dev.ini');
        $config->merge($moduleConfig);
        Zend_Registry::set('config', $config);
        return $config;
    }
    
    public function _initServices() {
        $class = get_class($this);
        $moduleBootstrapClassNameparts = explode("_", $class);
        $modulePath = APPLICATION_PATH."/modules/".strtolower($moduleBootstrapClassNameparts[0]);
        $servicesFile = $modulePath."/config/services.xml";
        $container = Zend_Registry::get('serviceContainer');
        $loader = new sfServiceContainerLoaderFileXml($container);
        $loader->load($servicesFile);
        Zend_Registry::set('serviceContainer', $container);
    }
    
}

?>
