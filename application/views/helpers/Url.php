<?php

class HM_View_Helper_Url extends Zend_View_Helper_Url
{
    const ROUTE  = 'route';

    public function url(array $urlOptions = array(), $name = null, $reset = false, $encode = true)
    {
        $baseUrl = null;

        if(!$name && isset($urlOptions[self::ROUTE])){
            $name = $urlOptions[self::ROUTE];
            unset($urlOptions[self::ROUTE]);
        }

        if (isset($urlOptions['baseUrl'])) {
            $baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();

            $urlOptions['baseUrl'] = Zend_Registry::get('config')->url->base.$urlOptions['baseUrl'];

            Zend_Controller_Front::getInstance()->setBaseUrl($urlOptions['baseUrl']);

            unset($urlOptions['baseUrl']);

        }
        $router = Zend_Controller_Front::getInstance()->getRouter();
        $url = $router->assemble($urlOptions, $name, $reset, $encode);

        if (null !== $baseUrl) {
            Zend_Controller_Front::getInstance()->setBaseUrl($baseUrl);
        }

        return $url;
    }
}