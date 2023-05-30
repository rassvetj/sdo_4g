<?php

class Webinar_Xml_Service extends Object_Service {

    static protected $_instance;

    public static function getInstance() {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function get($pointId, $offline = false, $resourceId = 0) {
        $xml = new Webinar_Xml($pointId);
        $xml->setOffline($offline);
        $xml->setResourceId($resourceId);
        return $xml->get();
    }

}