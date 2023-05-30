<?php
require_once 'Zend/Controller/Request/Http.php';

class HM_Controller_Request_Http extends Zend_Controller_Request_Http
{

    protected $_parseMethodOverride = true;
    
    private $_validHTTPMethods = array('GET', 'POST', 'PUT', 'DELETE', 'OPTIONS', 'HEAD', 'TRACE', 'CONNECT');
    
    public function enableMethodOverride()
    {
        $this->_parseMethodOverride = true;
    }
    
    public function disableMethodOverride()
    {
        $this->_parseMethodOverride = false;
    }

    public function getMethod()
    {
        if ($this->_parseMethodOverride) {
            $override = $this->getHeader('X_HTTP_METHOD_OVERRIDE');
        }
        if (isset($override) and $override and in_array($override, $this->_validHTTPMethods) === TRUE) {
            return $override;
        } else {
            return parent::getMethod();
        }
    }

    private $_cgiDefinedHeaders = array('CONTENT_LENGTH', 'CONTENT_TYPE');

    public function getHeader($header)
    {
        $guess = parent::getHeader($header);

        if ($guess === FALSE) {
            $temp = strtoupper(str_replace('-', '_', $header));
            if (in_array($temp, $this->_cgiDefinedHeaders) === TRUE and !empty($_SERVER[$temp])) {
                return $_SERVER[$temp];
            }
        }

        return $guess;
    }

}
