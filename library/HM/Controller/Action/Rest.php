<?php

class HM_Controller_Action_Rest extends Zend_Rest_Controller {

    const EVENT_GET_REQUEST_PRE = "restGetRequest.pre";
    const EVENT_UPDATE_REQUEST_PRE = "restUpdateRequest.pre";
    const EVENT_INIT_REQUEST = "restRequestInit";

    /**
     * @var HM_Acl
     */
    protected $_acl;

    /**
     * @var HM_Controller_Request_Http
     */
    protected $_request;

    /**
     * MUST BE OVERRIDEN
     * @var HM_Service_Abstract
     */
    protected $_defaultService = null;
    protected $_orderFieldName = 'name';

    /**
     * @var HM_Collection
     */
    protected $_collection = null;

    /**
     * @var HM_Model_Abstract
     */
    protected $_item = null;

    /**
     *
     * @var sfEventDispatcher 
     */
    protected $eventDispatcher = null;

    public function __construct(\Zend_Controller_Request_Abstract $request, \Zend_Controller_Response_Abstract $response, array $invokeArgs = array()) {
        /* @var $eventDispatcher sfEventDispatcher */
        $eventDispatcher = Zend_Registry::get('serviceContainer')->getService('EventDispatcher');
        $eventDispatcher->connect(self::EVENT_GET_REQUEST_PRE, $this->getRequestInitializer());
        $eventDispatcher->connect(self::EVENT_INIT_REQUEST, $this->pluginRegistrator());
        $eventDispatcher->connect(self::EVENT_INIT_REQUEST, $this->contextSwitcher());
        $eventDispatcher->connect(self::EVENT_UPDATE_REQUEST_PRE, $this->updateRequestInitializer());
        $this->setEventDispatcher($eventDispatcher);
        parent::__construct($request, $response, $invokeArgs);
    }

    public function init() {
        $this->_acl = $this->getService('Acl');
        $params = array('helper' => $this->_helper);
        $this->getEventDispatcher()->notify(new sfEvent($this, self::EVENT_INIT_REQUEST, $params));
        $this->getEventDispatcher()->notify(new sfEvent($this, self::EVENT_GET_REQUEST_PRE));
        $this->getEventDispatcher()->notify(new sfEvent($this, self::EVENT_UPDATE_REQUEST_PRE));
    }

    protected function getRequestInitializer() {
        return function($ev) {
                    $subject = $ev->getSubject();
                    if ($subject->getRequest()->getParam('action', '') == 'get') {
                        $params = $subject->_getAllParams();

                        if (!isset($params['id'])) {
                            unset($params['action']);
                            unset($params['controller']);
                            unset($params['module']);
                            foreach ($params as $id => $sub) {
                                if (is_numeric($id)) {

                                    if (false !== strstr($sub, '-')) {
                                        $parts = explode('-', $sub);
                                        $count = 0;
                                        foreach ($parts as $part) {
                                            if ($count > 0) {
                                                $parts[$count] = strtoupper(substr($part, 0, 1)) . substr($part, 1);
                                            }
                                            $count++;
                                        }

                                        $sub = join('', $parts);
                                    }

                                    $subject->_setParam($id, null);
                                    $subject->_setParam('id', $id);
                                    $subject->_setParam('sub', $sub);
                                }
                            }
                        }

                        $params = $subject->_getAllParams();
                        if (!isset($params['id'])) {
                            $subject->_setParam('action', 'index');
                            $subject->_request->setActionName('index');
                        }
                    }
                };
    }

    protected function updateRequestInitializer() {
        return function($ev) {
                    $subject = $ev->getSubject();
                    if ($subject->getRequest()->isPost() || $subject->getRequest()->isPut() || $subject->getRequest()->isDelete()) {
                        $contentType = $subject->getRequest()->getHeader('Content-Type');

                        if (strlen($contentType) && (false !== strstr($contentType, 'json'))) {
                            $body = $subject->getRequest()->getRawBody();
                            if (strlen($body)) {
                                if ($values = Zend_Json::decode($body)) {
                                    if (is_array($values) && count($values)) {
                                        $subject->getRequest()->setPost($values);
                                    }
                                }
                            }
                        }
                    }
                };
    }

    protected function contextSwitcher() {
        return function($ev) {
                    /*@var $ev seEvent */
                    $params = $ev->getParameters();
                    $helper = $params['helper'];
                    $helper->ContextSwitch()->setAutoJsonSerialization(true)->addActionContext('index', 'json')->initContext('json');
                    $helper->ContextSwitch()->setAutoJsonSerialization(true)->addActionContext('get', 'json')->initContext('json');
                    $helper->ContextSwitch()->setAutoJsonSerialization(true)->addActionContext('post', 'json')->initContext('json');
                    $helper->ContextSwitch()->setAutoJsonSerialization(true)->addActionContext('put', 'json')->initContext('json');
                    $helper->ContextSwitch()->setAutoJsonSerialization(true)->addActionContext('delete', 'json')->initContext('json');
                };
    }

    protected function pluginRegistrator() {
        return function($ev) {
                    Zend_Controller_Front::getInstance()->registerPlugin(
                            new Zend_Controller_Plugin_ErrorHandler(
                                    array(
                                        'module' => 'default',
                                        'controller' => 'error',
                                        'action' => 'json'
                                    )
                            )
                    );
                };
    }

    protected function pluginUnregistrator() {
        return function($ev) {
                    Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
                };
    }

    public function indexAction() {
        $this->_collection = $this->_defaultService->fetchAll(null, $this->_orderFieldName);

        $this->view->assign($this->_collection->asArray());
    }

    public function getAction() {
        $id = (int) $this->_getParam('id', 0);

        if ($id) {
            if ($subject = $this->_getParam('sub', false)) {
                if (method_exists($this, $subject)) {
                    $result = $this->{$subject}();
                    if ($result) {
                        $this->view->assign($result);
                    }
                }
            } else {
                $this->_item = $this->getOne($this->_defaultService->find($id));
                if ($this->_item) {
                    $this->view->assign($this->_item->getValues());
                }
            }
        }
    }

    public function postAction() {
        
    }

    public function putAction() {
        
    }

    public function deleteAction() {
        
    }

    /**
     * @param  $name
     * @return HM_Service_Abstract
     */
    public function getService($name) {
        return $this->_helper->ServiceContainer($name);
    }

    protected function setDefaultService($service) {
        $this->_defaultService = $service;
    }

    public function getOne(HM_Collection $collection) {
        if (($collection instanceof HM_Collection_Abstract) && count($collection)) {
            return $collection->current();
        }
        return false;
    }

    public function quoteInto($where, $args) {
        return $this->getService('User')->quoteInto($where, $args);
    }

    public function postDispatch() {
        if ($this->getRequest()->isXmlHttpRequest()
                || $this->_getParam('ajax', false)) {
            $headers = $this->getResponse()->getHeaders();
            $hasHeader = false;
            foreach ($headers as $key => $header) {
                if ('content-type' == strtolower($header['name'])) {
                    $hasHeader = true;
                    break;
                }
            }

            if (!$hasHeader) {
                $this->getResponse()->setHeader('Content-type', 'text/html; charset=' . Zend_Registry::get('config')->charset, true);
            }
        }
    }

    /**
     * 
     * @return sfEventDispatcher
     */
    public function getEventDispatcher() {
        return $this->eventDispatcher;
    }

    /**
     * 
     * @param sfEventDispatcher $eventDispatcher
     */
    public function setEventDispatcher(sfEventDispatcher $eventDispatcher) {
        $this->eventDispatcher = $eventDispatcher;
    }

}