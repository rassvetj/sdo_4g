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
     * у кого то домашняя страница     ^^    - страница ошибки?))
     */
    public function errorAction() {
        $this->getResponse()->clearBody();
        $this->getResponse()->clearHeaders();
        $errors = $this->_getParam('error_handler');

        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:

                // 404 error -- controller or action not found
                $this->getResponse()->setHttpResponseCode(404);
                $this->view->message = _('Страница не найдена');
                $this->view->errorType = 404;

                $this->getService('Log')->log(
                    $this->getService('User')->getCurrentUserId(),
                    'Page Not Found',
                    'Fail',
                    Zend_Log::WARN,
                    $_SERVER['REQUEST_URI']
                );


                break;
            default:
                switch(strtolower(get_class($errors->exception))) {
                    case 'hm_permission_exception':
                    	$this->view->message = _($errors->exception->getMessage());

                    	$this->view->errorType = 403;
                    	$this->getResponse()->setHttpResponseCode(403);
                        //$this->_flashMessenger->addMessage($errors->exception->getMessage());
                        //$this->_redirect(Zend_Registry::get('baseUrl').'index/error');
                        break;
                    default:

                        $this->getService('Log')->log(
                            $this->getService('User')->getCurrentUserId(),
                            'Application Error',
                            'Fail',
                            Zend_Log::CRIT,
                            $_SERVER['REQUEST_URI']
                        );

                        if ($this->getRequest()->getActionName() != 'json') {
						    $this->view->message = _('Ошибка приложения');
                        } else {
                            $this->view->message = $errors->exception->getMessage();
                            $this->view->code = $errors->exception->getCode();
                        }
                    	$this->view->errorType = 500;
                    	$this->getResponse()->setHttpResponseCode(500);
                    	break;
                }
                // application error
                //$this->getResponse()->setHttpResponseCode(500);
                //$this->view->message = _('Ошибка приложения');
                break;
        }

        $appendToLog = '';
        if ($errors->exception instanceof Zend_Db_Exception) {
            $trace = $errors->exception->getTrace();
            foreach($trace as $line) {
                if ($line['function'] == 'query' && $line['class'] == 'Zend_Db_Adapter_Abstract') {
                    if ($line['args'][0]) {
                        if (is_string($line['args'][0])) {
                            $appendToLog = sprintf("\nLAST QUERY: %s\n", $line['args'][0]);
                        } else {
                            $appendToLog = sprintf("\nLAST QUERY: %s\n", $line['args'][0]->__toString());
                        }
                    }
                }
            }
        }

        /**
         * var Zend_Db_Profiler
         */
        $profiler = $this->getInvokeArg('bootstrap')->getResource('db')->getProfiler();
        if ($profiler && is_array($queryProfiles = $profiler->getQueryProfiles(null, true))) {
            foreach($queryProfiles as $queryProfile) {
                if (!$queryProfile->hasEnded()) {
                    $appendToLog .= sprintf("\nLAST NOT ENDED PROFILER QUERY: %s\n", $queryProfile->getQuery());
                    $appendToLog .= sprintf("\nLAST NOT ENDED PROFILER QUERY PARAMS: %s\n", var_export($queryProfile->getQueryParams(), true));
                }
            }
        }

        Zend_Registry::get('log_system')->debug($appendToLog . $errors->exception->getMessage() . "\n" . $errors->exception->getTraceAsString());

        if ($this->getRequest()->getActionName() != 'json') {
            $this->view->exception = $errors->exception;
            $this->view->request   = $errors->request;
        }

        //Zend_Registry::get('unmanaged_controller')->setMessage($this->view->message);
    }

    public function jsonAction()
    {
        $this->_helper->ContextSwitch()->addActionContext('json', 'json')->initContext('json');
        $this->errorAction();

    }

}
?>

