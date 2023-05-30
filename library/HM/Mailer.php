<?php
/**
 * Description of Mailer
 *
 * @author slava
 */
class HM_Mailer extends Zend_Mail {
    
    protected $transporter = null;
    
    public function init() {
        $config = Zend_Registry::get('config');
        var_dump($config->mailer);die;
    }
    
    /**
     * 
     * @return Zend_Mail_Transport_Abstract
     */
    public function getTransporter() {
        return $this->transporter;
    }

    /**
     * 
     * @param Zend_Mail_Transport_Abstract $transporter
     */
    public function setTransporter(Zend_Mail_Transport_Abstract $transporter) {
        $this->transporter = $transporter;
    }
    
}

?>
