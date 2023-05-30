<?php
class HM_Controller_Action_Helper_ServiceContainer extends Zend_Controller_Action_Helper_Abstract
{
    protected $_container;

    public function init()
    {
        $this->_container = $this->getActionController()->getInvokeArg('bootstrap')->getResource('container');     
    }

    public function direct($name)
    {
        if($this->_container->hasService($name)) {
            $service = $this->_container->getService($name);
            if (method_exists($service, 'getServiceContainer')) {
                if (null == $service->getServiceContainer()) {
                    $service->setServiceContainer($this->_container);
                }
            }
            return $service;
        }
        else if($this->_container->hasParameter($name)) {
            return $this->_container->getParameter($name);
        }
        return null;
    }

    public function preDispatch()
    {
        $actionController = $this->getActionController();

        $r = new Zend_Reflection_Class($actionController);
        $properties = $r->getProperties();

        foreach($properties as $property) {
            if($property->getDeclaringClass()->getName() == get_class($actionController)) {
                if (false !== $property->getDocComment()) {
                    if($property->getDocComment()->hasTag('Inject')) {
                        $injectTag = $property->getDocComment()->getTag('Inject');
                        $serviceName = $injectTag->getDescription();
                        if(empty($serviceName)) {
                            $serviceName = $this->_formatServiceName($property->getName());
                        }
                        if($this->_container->hasService($serviceName)) {
                            if (method_exists($property, 'setAccessible')) {
                                $property->setAccessible(true);
                            }
                            $property->setValue($actionController, $this->_container->getService($serviceName));
                        }
                    }
                }
            }
        }

    }

    protected function _formatServiceName($serviceName)
    {
        if(strpos($serviceName, '_') === 0) {
            $serviceName = substr($serviceName, 1);
        }
        return $serviceName;
    }


    public function getContainer()
    {
        return $this->_container;
    }
}