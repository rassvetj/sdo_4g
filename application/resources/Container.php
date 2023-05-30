<?php
class HM_Resource_Container extends Zend_Application_Resource_ResourceAbstract
{
    protected $_container;

    public function init()
    {
        $options = $this->getOptions();

        $name = 'Project'.md5(APPLICATION_PATH.APPLICATION_ENV.Zend_Registry::get('config')->debug).'ServiceContainer';
        $filename = APPLICATION_PATH.'/../data/cache/'.$name.'.php';

        if (!Zend_Registry::get('config')->debug && file_exists($filename)) {
            require_once($filename);
            $this->_container = new $name();
        } else {
            $this->_container = new HM_ServiceContainer();
            if (is_array($options['configFiles']) && count($options['configFiles'])) {
                foreach($options['configFiles'] as $configFile) {
                    $this->_loadConfigFile($configFile);
                }
            }

            if (!Zend_Registry::get('config')->debug) {
                $dumper = new sfServiceContainerDumperPhp($this->_container);
                file_put_contents($filename, $dumper->dump(array('class' => $name, 'base_class' => 'HM_ServiceContainerBuilder')));
            }
        }

        $this->_container->setParameter('application_path', APPLICATION_PATH);
        Zend_Registry::set('serviceContainer', $this->_container);
        if (APPLICATION_ENV == 'development') {
            $this->_container->getService('FireBug')->log('Service container initialized...', Zend_Log::INFO);
        }
        return $this->_container;
    }

    protected function _loadConfigFile($file) 
    {
        $suffix = strtolower(pathinfo($file, PATHINFO_EXTENSION));

        switch ($suffix) {
            case 'xml':
                $loader = new sfServiceContainerLoaderFileXml($this->_container);
            break;

            case 'yml':
                $loader = new sfServiceContainerLoaderFileYaml($this->_container);
            break;

            case 'ini':
                $loader = new sfServiceContainerLoaderFileIni($this->_container);
            break;
        }

        $loader->load($file);
    }

}
