<?php

abstract class HM_Service_Standalone_Abstract
{
    protected $_serviceContainer = null;

    public function setServiceContainer($serviceContainer)
    {
        $this->_serviceContainer = $serviceContainer;
    }

    public function getServiceContainer()
    {
        return $this->_serviceContainer;
    }

    /**
     * @param string $name
     * @return HM_Service_Abstract
     */
    public function getService($name)
    {
        $service = $this->getServiceContainer()->getService($name);
        if (method_exists($service, 'getServiceContainer')) {
            if (null == $service->getServiceContainer()) {
                $service->setServiceContainer($this->getServiceContainer());
            }
        }
        return $service;
    }

    /**
     * @param  $collection
     * @return bool | HM_Model_Abstract
     */
    public function getOne($collection)
    {
        if (count($collection)) {
            return $collection->current();
        }
        return false;
    }

    public function getDateTime($time = null)
    {
        if (null == $time) {
            $time = time();
        }
        return date('Y-m-d H:i:s', $time);
    }

    public function quoteInto($where, $args)
    {
        if (is_array($where)) {
            $quotedWhere = '';
            foreach($where as $key => $w) {
                $quotedWhere .= $this->getService('User')->quoteInto($w, $args[$key]);
            }
            return $quotedWhere;
        } else {
            return $this->getService('User')->quoteInto($where, $args);
        }
    }


}