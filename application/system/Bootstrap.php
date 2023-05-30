<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    private $_config = null;

    public function __construct($application) {
		if(HARDCODE_WITHOUT_SESSION !== true){
			$this->_initSession(); // хардкор патамушта нужно инициализировать zend-сессии до require 1.php
		}
        parent::__construct($application);
    }

    protected function _initRequest()
    {
        $front = $this->bootstrap('frontController')->getResource('frontController');
        $front->setRequest(new HM_Controller_Request_Http());
    }

    protected function _initSession() {

        if (!$_SESSION) {
            if (isset($_POST['sessid']) && strlen($_POST['sessid'])) {
                session_id($_POST['sessid']);
            }

            Zend_Registry::set('session_redirector', new Zend_Session_Namespace('redirector'));

            Zend_Registry::set('session_namespace_default', new Zend_Session_Namespace('default'));

            $session_namespace_unmanaged = new Zend_Session_Namespace('unmanaged');
            $session_namespace_unmanaged->s = array();
            if (!empty($_SESSION['s'])) $session_namespace_unmanaged->s = $_SESSION['s'];
            Zend_Registry::set('session_namespace_unmanaged', $session_namespace_unmanaged);
        }
        
    }

    protected function _initLoader() {
        $loader = Zend_Loader_Autoloader::getInstance();
        $loader->setFallbackAutoloader(true);
        //Zend_Registry::set('loader', $loader);
        return $loader;
    }

    protected function _initConfig() {
        /**
         * @todo put dev config file instead of last null parameter after HM_Config_Ini refactoring!
         */
        $config = new HM_Config_Ini(APPLICATION_PATH . '/settings/config.ini', APPLICATION_ENV, true, null);
        $this->_config = $config;
        Zend_Registry::set('config', $config);
        return $config;
    }

/*    protected function _initLanguage()
    {
        $locale = $this->bootstrap('locale')->getResource('locale');

        if ($locale != 'ru_RU') {
            Zend_Registry::get('translate')->setLocale($locale); // override unmanaged locale
        }
        Zend_Registry::set('locale', $locale);
    }*/

    protected function _initDefaultTimeZone()
    {
        date_default_timezone_set(Zend_Registry::get('config')->timezone->default);
    }

    protected function _initLog() {
        $logs = array(
            'log_system' => $this->_config->path->log->system . date('Y-m') . '.txt',
            'log_mail' => $this->_config->path->log->mail . date('Y-m') . '.html',
            'log_security' => $this->_config->path->log->security . date('Y-m') . '.txt'
        );
    
        foreach ($logs as $name => $path) {
            if (!file_exists($path)) {
                @touch($path);
                @chmod($path, 0666);
            }
            $writer = new Zend_Log_Writer_Stream($path);
            $log = new Zend_Log($writer);
            Zend_Registry::set($name, $log);

        }
        
    }

    protected function _initFileTransferAdapter() {
        $adapter = new Zend_File_Transfer_Adapter_Http();
        $adapter->setDestination($this->_config->path->upload->temp);
        //Zend_Registry::set('upload_adapter', $adapter);
        return $adapter;
    }

    protected function _initActionHelperBroker()
    {
        Zend_Controller_Action_HelperBroker::addPrefix('HM_Controller_Action_Helper');
        //Zend_Controller_Action_HelperBroker::addHelper(new HM_Controller_Action_Helper_ServiceContainer());
    }

    protected function _initModulesDirectory()
    {
        $front = $this->bootstrap('frontController')->getResource('frontController');
        $config = $this->bootstrap('config')->getResource('config');
        switch(APPLICATION_MODULE) {
            case 'AT':
                $front->addModuleDirectory(APPLICATION_PATH .'/modules/at');
                $front->setBaseUrl($config->url->base.'at/');
                break;
            case 'CMS':
                $front->addModuleDirectory(APPLICATION_PATH .'/modules/cms');
                $front->setBaseUrl($config->url->base.'cms/');
                break;
            case 'WRAPPER':
                $front->addModuleDirectory(APPLICATION_PATH .'/modules/els');
                $front->setBaseUrl($config->url->base.'wrapper/');
                break;
            default:
                $front->addModuleDirectory(APPLICATION_PATH .'/modules/els');
                $front->setBaseUrl($config->url->base);
                break;
        }

    }

    protected function _initNavigation()
    {
        $container = $this->bootstrap('container')->getResource('container');
        $this->bootstrap('db');

        Zend_View_Helper_Navigation_HelperAbstract::setDefaultAcl($container->getService('Acl'));
        Zend_View_Helper_Navigation_HelperAbstract::setDefaultRole($container->getService('User')->getCurrentUserRole());

    }

    protected function _initOracleAdapter()
    {

        $db = $this->bootstrap('db')->getResource('db');
        $this->bootstrap('toolbar');

        $adapter = strtolower(get_class($db));
        $adapter = str_replace("zend_db_adapter_", "", $adapter);
        $adapter = str_replace("hm_db_adapter_", "", $adapter);
        if (in_array($adapter, array('pdo_oci', 'oracle'))) {
            $db->query("ALTER SESSION SET NLS_DATE_FORMAT = 'YYYY-MM-DD HH24:MI:SS'");
        }

        $config = Zend_Registry::get('config');
        if($config->cache->db == true){

            $frontendOptions = array(
        		'automatic_serialization' => true
            );

            $backendOptions  = array(
        		'cache_dir'                => APPLICATION_PATH . '/../data/cache/'
        		);

        		$cache = Zend_Cache::factory(
            	'Core',
                'File',
        		$frontendOptions,
        		$backendOptions
        		);

        		// Next, set the cache to be used with all table objects
        		Zend_Db_Table_Abstract::setDefaultMetadataCache($cache);

        }



    }

    protected function _initRouter()
    {

        $config = $this->bootstrap('config')->getResource('config');
        $router = new Zend_Controller_Router_Rewrite();
        $router->addConfig($config, 'routes');
        $front = $this->bootstrap('frontController')->getResource('frontController');

        if (defined('APPLICATION_MODULE') && (APPLICATION_MODULE == 'ELS') && (false !== strstr($_SERVER['REQUEST_URI'], '/rest/'))/**&& ($front->getRequest()->getModuleName() == 'rest')*/) {
            $router->addRoute('rest', new Zend_Rest_Route($front, array(), array('rest')));
        }

        $front->setRouter($router);
        return $router;

    }

    protected function _initMailer()
    {
        $config = $this->bootstrap('config')->getResource('config');
        $transport = $config->mailer->transport;
        $mailTransportName = 'Zend_Mail_Transport_'.ucfirst($transport);
        if (!class_exists($mailTransportName)) {
            throw new RuntimeException('Mail transport name doesn\'t define');
        }
        Zend_Mail::setDefaultTransport(
            new $mailTransportName($config->mailer->params->toArray())
        );
        Zend_Mail::setDefaultFrom($config->mailer->default->email, $config->mailer->default->name);
    }

    protected function _initCache()
    {
        $config = $this->bootstrap('config')->getResource('config');
        $cache = Zend_Cache::factory('Core', 'File', $config->cache->frontend->toArray(), $config->cache->backend->toArray());
        Zend_Registry::set('cache', $cache);

        return $cache;
    }

    protected function _initExtensions()
    {
        $container = $this->bootstrap('container')->getResource('container');
        $container->getService('Extension')->init();

        $event = new sfEvent($this, HM_Extension_ExtensionService::EVENT_AFTER_INIT_EXTENSIONS);
        $container->getService('EventDispatcher')->notify($event);
    }

}