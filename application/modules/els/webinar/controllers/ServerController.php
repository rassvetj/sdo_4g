<?php

class Webinar_ServerController extends HM_Controller_Action {
    public function indexAction() 
    {
        $server = new Zend_Amf_Server();
        $server->setProduction(false);
        include APPLICATION_PATH . "/../library/HM/Webinar/Server.php";
        $server->setClass('Webinar_Server');

        $response = $server->handle();
        echo $response;

        die();
    }
    
    public function debugAction() 
    {
        include APPLICATION_PATH . "/../library/HM/Webinar/Server.php";
        //pr(Zend_registry::get('serviceContainer')->getService('WebinarFiles')->getFilesForLesson(281));
        pr(Webinar_Server::getPlan('webinar_7'));
        //pr(Webinar_Server::setPlan('webinar_7', array($stdObject)));
        die();
        //pr(Webinar_Server::getUserList('webinar_7'));
        //$this->getService('WebinarUser')->ping(266,501);
        
        //pr($this->getService('WebinarFiles')->getFilesForLesson(281));
        
        //pr(Webinar_User_Service::getInstance()->pingUser(266,501));
        //pr(Webinar_Server::setCurrentItem(266, 155));
        //pr(Webinar_Server::getCurrentItem(281));
        //pr(Webinar_Server::recordStart(987, 'http://kzt:8080/temp/test.txt'));
        //pr(Webinar_Service::getInstance()->isUserAllowed(987,1));
        //pr(Webinar_Server::getRecords(987));
        
        //pr(Webinar_Server::addChatMessage(281, iconv(Zend_Registry::get('config')->charset, Zend_Registry::get('config')->webinar->charset, 'Это тест!')));
        //pr(Webinar_Server::getChatMessages(281));
       // die();
    }
    
    public function xmlAction()
    {
    	header("Content-Type: text/xml");
    	$pointId = (int) $this->_getParam('pointId', 0);
    	echo Webinar_Xml_Service::getInstance()->get($pointId);
    	die();
    }
}