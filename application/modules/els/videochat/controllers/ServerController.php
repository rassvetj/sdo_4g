<?php

class Videochat_ServerController extends HM_Controller_Action {
    public function indexAction() {
        $server = new Zend_Amf_Server();
        $server->setProduction(false);
        $server->setClass('Webinar_Server');
        
        $response = $server->handle();
        echo $response;
        die();
    }
    
    public function debugAction() {
        //pr(Webinar_Server::getPlan(987));
        //pr(Webinar_Server::getUserList(987));
        //pr(Webinar_User_Service::getInstance()->pingUser(1,1));
        //pr(Webinar_Server::setCurrentItem(987,49));
        //pr(Webinar_Server::getCurrentItem(987));
        //pr(Webinar_Server::recordStart(987, 'http://kzt:8080/temp/test.txt'));
        //pr(Webinar_Service::getInstance()->isUserAllowed(987,1));
        //pr(Webinar_Server::getRecords(987));
        //pr(Webinar_Server::addChatMessage(987, iconv(Zend_Registry::get('config')->common->charset, Zend_Registry::get('config')->webinar->charset, 'Это тест!')));
        //pr(Webinar_Server::getChatMessages(987));
        die();
    }
    
    public function xmlAction()
    {
    	header("Content-Type: text/xml");
    	$pointId = (int) $this->_getParam('pointId', 0);
    	echo Webinar_Xml_Service::getInstance()->get($pointId);
    	die();
    }
}