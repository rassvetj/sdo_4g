<?php
class HM_Resource_View extends Zend_Application_Resource_ResourceAbstract
{
    /**
     * @var HM_View_Extended
     */
    protected $_view = null;
    
    public function init()
    {
        if (null === $this->_view) {

            $options = $this->getOptions();

            Zend_Layout::startMvc();
            $layout = Zend_Layout::getMvcInstance();
            //$this->_view = $layout->getView();
            $this->_view = new HM_View_Extended();
            $this->_view->setEncoding(Zend_Registry::get('config')->charset);            
            $this->_view->addHelperPath("ZendX/JQuery/View/Helper", "ZendX_JQuery_View_Helper");
            $this->_view->addHelperPath("HM/View/Helper", "HM_View_Helper");
            $this->_view->addHelperPath(Zend_Registry::get('config')->path->helpers->default, 'HM_View_Helper');
            $this->_view->addHelperPath(Zend_Registry::get('config')->path->infoblocks->default, 'HM_View_Infoblock');

            $this->_view->addScriptPath(APPLICATION_PATH.'/views/extended/');
            $this->_view->addScriptPath(APPLICATION_PATH.'/views/partials/');

            // init overloaded jquery helper
            require_once 'HM/View/Helper/JQuery/Container.php';
            Zend_Registry::set('ZendX_JQuery_View_Helper_JQuery', new HM_View_Helper_JQuery_Container());
            // Load default JS, disabled untill all pages will use Zend
            //$this->_view->headScript()->appendFile( Zend_Registry::get('config')->url->base.'js/lib/jquery/jquery-ui-1.8.5.custom.min.js' );
            //$this->_view->headScript()->appendFile( Zend_Registry::get('config')->url->base.'js/lib/underscore-1.1.2.min.js' );
            //$this->_view->headScript()->appendFile( Zend_Registry::get('config')->url->base.'js/plugins.js' );
            // Load default CSS, disabled untill all pages will use Zend
            //$this->_view->headLink()->appendStylesheet( Zend_Registry::get('config')->url->base.'css/jquery-ui/jquery-ui-1.8.5.custom.css' );
            //$this->_view->headLink()->appendStylesheet( Zend_Registry::get('config')->url->base.'css/common.css' );
            //$this->_view->headLink()->appendStylesheet( Zend_Registry::get('config')->url->base.'css/print.css' );

            $layout->setViewSuffix('tpl');
            //$this->_view->baseUrl = $this->_config->url->base;
            //$this->_view->setBasePath($this->_config->path->views);
            $layout->setView($this->_view);
            $view_renderer = new Zend_Controller_Action_Helper_ViewRenderer();
            $view_renderer
                ->setView($this->_view)
                ->setViewSuffix('tpl');
            Zend_Controller_Action_HelperBroker::addHelper($view_renderer);
            Zend_Controller_Action_HelperBroker::addPrefix('HM_Controller_Action_Helper');
            Zend_Registry::set('view', $this->_view);
        }
    
        return $this->_view;
    }
        
}