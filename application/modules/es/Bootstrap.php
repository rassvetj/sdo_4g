<?php
/**
 * Description of Bootstrap
 *
 * @author slava
 */
class Es_Bootstrap extends \HM_Module_Bootstrap {
    
    public function _initAutoload() {
        $moduleLoader = new Zend_Application_Module_Autoloader(array(
                    'namespace' => 'Es_',
                    'basePath' => APPLICATION_PATH . '/modules/es',
                    'resourceTypes' => array(
                        'service' => array(
                            'namespace' => 'Service',
                            'path' => 'services'
                        ),
                        'entity' => array(
                            'namespace' => 'Entity',
                            'path' => 'entities'
                        ),
                        'exception' => array(
                            'namespace' => 'Exception',
                            'path' => 'exceptions'
                        ),
                        'schema' => array(
                            'namespace' => 'Schema',
                            'path' => 'schema',
                        ),
                        'controller' => array(
                            'namespace' => 'Controller',
                            'path' => 'default/controllers',
                        ),
                        'command' => array(
                            'namespace' => 'Command',
                            'path' => 'commands'
                        )
                    )
            )
        );
        return $moduleLoader;
    }
    
    public function _initConstants() {
        if (preg_match("/^\/es\/.*/i", $_SERVER['REQUEST_URI'])) {
            define('APPLICATION_MODULE', 'ES');
        }
    }
    
    public function _initModulesDirectory() {
        if (APPLICATION_MODULE == 'ES') {
            $front = $this->bootstrap('frontController')->getResource('frontController');
            $config = $this->bootstrap('config')->getResource('config');
            $front->addModuleDirectory(APPLICATION_PATH .'/modules/es');
            $front->setBaseUrl($config->url->base.'es/');
        }
    }

    /*
    public function _initTriggerServices() {
        $serviceContainer = Zend_Registry::get('serviceContainer');
        $dispatcher = $serviceContainer->findTaggedServiceIds('es.trigger');
        foreach ($serviceContainer as $serviceName) {
            var_dump($serviceName);die;
            if ($service instanceof Es_Entity_Trigger) {
                $dispatcher->connect(get_class($service).'::esPushTrigger', $service->triggerPushCallback());
            }
        }
    }
     */
    
}

?>
