<?php
/**
 * Description of MailHtmlWrapper
 *
 * @author slava
 */
class Es_Service_Callback_MailHtmlWrapper implements Es_Service_Callback_CallbackBehavior {
    
    public function getCallback(array $params = array()) {
        return function($ev) {
            $parameters = $ev->getParameters();
            $mailView = $parameters['mailView'];
            $tplPath = realpath(__DIR__.'/../../data/mail_templates/');
            if (!file_exists($tplPath)) {
                throw new Es_Exception_Runtime('Mail templatepath \''.$tplPath.'\' doesn\'t exists');
            }
            $layoutName = 'layout';
            $layoutFullPath = $tplPath.'/'.$layoutName.'.tpl';
            if (!file_exists($layoutFullPath)) {
                throw new Es_Exception_Runtime('Mail template layout \'' . $layoutFullPath . '\' doesn\'t exists');
            }
            $layout = new Zend_Layout();
            $layout->content = $ev->getReturnValue();
            $layout->setViewSuffix('tpl');
            $layout->setLayoutPath($tplPath);
            $layout->setLayout($layoutName);
            $layout->setView($mailView);
            $result = $layout->render();
            $ev->setReturnValue($result);
        };
    }
    
}

?>
