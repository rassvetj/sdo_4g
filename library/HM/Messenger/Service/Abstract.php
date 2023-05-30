<?php
abstract class HM_Messenger_Service_Abstract implements SplObserver
{
    private $_serviceContainer = null;
    
    public function setServiceContainer($container)
    {
        $this->_serviceContainer = $container;
    }

    public function getServiceContainer()
    {
        return $this->_serviceContainer;
    }

    public function getService($name)
    {
        return $this->_serviceContainer->getService($name);
    }
}
