<?php
/**
 * Description of RestError
 *
 * @author slava
 */
abstract class HM_Controller_RestError extends Zend_Controller_Action {

    public function errorAction() {
        $exception = $this->_getParam('error_handler')->exception;
        $response = array();
        switch ($exception->getCode()) { 
            case 404://not found
                $response['error'] = 'not found';
                break;
            case 403://forbidden
                $response['error'] = 'forbidden';
            default:
                $response['error'] = 'bad request';
                break;
        }
        Zend_Registry::get('log_system')->debug($exception->getMessage() . "\n" . $exception->getTraceAsString());
        return $this->_helper->json($response);
    }
    
    public function jsonAction() {
        $this->_helper->ContextSwitch()->addActionContext('json', 'json')->initContext('json');
        $this->errorAction();
    }
    
}

?>