<?php

/**
 * ErrorController
 * 
 * @author
 * @version 
 */

class ErrorController extends HM_Controller_Action {
    
    /**
     * The default action - show the home page
     */
    public function errorAction() {
        $this->getResponse()->clearBody();
        $errors = $this->_getParam('error_handler');
        switch ($errors->type) { 
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
        
                // 404 error -- controller or action not found
                $this->getResponse()->setHttpResponseCode(404);
                $this->view->message = _('Страница не найдена');
                break;
            default:                
                switch(strtolower(get_class($errors->exception))) {                    
                    case 'hm_permission_exception':
                        $this->_flashMessenger->addMessage($errors->exception->getMessage());
                        $this->_redirect(Zend_Registry::get('baseUrl').'index/error');
                        break;
                }
                // application error 
                //$this->getResponse()->setHttpResponseCode(500);
                $this->view->message = _('Ошибка приложения');
                break;
        }

        Zend_Registry::get('log_system')->debug($errors->exception->getMessage() . "\n" . $errors->exception->getTraceAsString());
        
        $this->view->exception = $errors->exception;
        $this->view->request   = $errors->request;
        
        //Zend_Registry::get('unmanaged_controller')->setMessage($this->view->message);
    }

}
?>

