<?php
class Mailer
{
    private $_mailer;
    private $_event;
    private $_view;

    public function __construct($module){// todo: auto-detect module

        $config = Zend_Registry::get('config');
        $this->_mailer = new Zend_Mail();
        $this->_mailer->setFrom($config->path->from_email, $config->path->from_name);

        $user = Library::getAuth($module)->getIdentity();
        $this->_mailer->addTo($user->email, $user->first_name  . ' ' . $user->last_name);

        $this->_view = new Zend_View();
        $this->_view->setScriptPath($config->path->mail->$module);
        $this->_view->user = $user;
    }

    public function setData($data){
        $this->_view->data = $data;
    }

    public function __call($event, $arguments){
        $this->_event = substr(strtolower($event), 2);
        $subject = $this->_setSubject();
        $body = $this->_setBody();
        try {
            $this->_mailer->send();
        } catch (Exception $e) {
            Zend_Registry::get('log_system')->log("E-Mail sending failed.", Zend_Log::ERR);
        }
        Zend_Registry::get('log_mail')->log("<b>{$subject}</b><br>{$body}<br><hr><br>", Zend_Log::INFO);
    }

    private function _setSubject(){
        $subjects = array();
        if (isset($subjects[$this->_event])) $subject = $subjects[$this->_event];
        else $subject = 'eLearning Server';
        $this->_mailer->setSubject($subject);
        return $subject;
    }

    private function _setBody() {
        $body = $this->_view->render("{$this->_event}.tpl");
        $this->_mailer->setBodyHtml($body);
        return $body;
    }
}