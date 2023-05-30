<?php
class HM_Controller_Plugin_Loader extends Zend_Controller_Plugin_Abstract
{
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $resourceLoader = new Zend_Loader_Autoloader_Resource(array(
            'basePath'  => Zend_Controller_Front::getInstance()->getModuleDirectory(),
            'namespace' => 'HM',
        ));
        $resourceLoader->addResourceType('acl', 'acls/', 'Acl')
                       ->addResourceType('form', 'forms/', 'Form');
    }
}
