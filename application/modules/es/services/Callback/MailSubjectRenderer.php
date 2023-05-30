<?php
/**
 * Description of MailSubjectRenderer
 *
 * @author slava
 */
class Es_Service_Callback_MailSubjectRenderer implements Es_Service_Callback_CallbackBehavior {
   
    public function getCallback(array $params = array()) {
        return function($ev) use($params) {
            $parameters = $ev->getParameters();
            $event = $parameters['event'];
            if ($event->getEventTypeStr() === null) {
                throw new Es_Exception_Runtime('Event string type doesn\'t defined');
            }
            $tplPath = realpath(__DIR__.'/../../data/mail_subject_templates/');
            if (!file_exists($tplPath)) {
                throw new Es_Exception_Runtime('Mail subject templatepath \''.$tplPath.'\' doesn\'t exists');
            }
            $tplName = $event->getEventTypeStr() . '.tpl';
            $tpl = $tplPath . '/' . $tplName;
            if (!file_exists($tpl)) {
                throw new Es_Exception_Runtime('Mail subject template \'' . $tpl . '\' doesn\'t exists');
            }
            $view = new Zend_View();
            $view->setScriptPath($tplPath);
            $view->assign('event', $event);
            $result = $view->render($tplName);
            
            $ev->setReturnValue($result);
        };
    }
    
}

?>
