<?php
/**
 * Description of MailRenderer
 *
 * @author slava
 */
class Es_Service_Callback_MailRenderer implements Es_Service_Callback_CallbackBehavior {
    
    public function getCallback(array $params = array()) {
        return function ($ev) {
            $parameters = $ev->getParameters();
            $event = $parameters['event'];
            $serviceAwareSubject = $ev->getSubject();
            if ($event->getEventTypeStr() === null) {
                throw new Es_Exception_Runtime('Event string type doesn\'t defined');
            }
            $tplPath = realpath(__DIR__.'/../../data/mail_templates/');
            if (!file_exists($tplPath)) {
                throw new Es_Exception_Runtime('Mail templatepath \''.$tplPath.'\' doesn\'t exists');
            }
            $tplName = $event->getEventTypeStr() . '.tpl';
            $tpl = $tplPath . '/' . $tplName;
            if (!file_exists($tpl)) {
                throw new Es_Exception_Runtime('Mail template \'' . $tpl . '\' doesn\'t exists');
            }
            $view = new Zend_View();
            $view->setScriptPath($tplPath);
            $view->assign('description', $event->getParams());
            $ev->offsetSet('mailView', $view);
            $ev->setReturnValue($view->render($tplName));
        };
    }
    
}

?>
