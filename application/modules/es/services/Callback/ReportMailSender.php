<?php

/**
 * Description of MailSenderByFilter
 *
 * @author slava
 */
class Es_Service_Callback_ReportMailSender implements Es_Service_Callback_CallbackBehavior {
    
    public function getCallback(array $params = array()) {
        return function($ev) {
            
            $parameters = $ev->getParameters();
            $subject = $ev->getSubject();
            /*@var $filter Es_Entity_AbstractFilter */
            $filter = $parameters['filter'];
            $reportMailRenderEventResult = $subject->getService('EventServerDispatcher')->trigger(
                    Es_Service_Dispatcher::EVENT_REPORT_MAIL_RENDER,
                    $subject,
                    array('filter' => $filter)
            );
            
            $params = $reportMailRenderEventResult->getParameters();
            if (array_key_exists('emptyEventsList', $params) && !$params['emptyEventsList']) {
                $content = $reportMailRenderEventResult->getReturnValue();
                $mailSubject = 'Новые события в системе';

                $user = $subject->getService('User')->getById($filter->getUserId());
                $mail = new Zend_Mail('UTF-8');
                $mail->setBodyHtml($content);
                $mail->setSubject($mailSubject);
                $mail->setType(Zend_Mime::MULTIPART_ALTERNATIVE);
                $mail->addTo($user->EMail);
                try {
                    $mail->send();
                } catch (\Exception $ex) {
                    /**
                     * @todo implement error logging or set error info to sfEvent object and handle that later
                     */
                }
            }
        };
    }
    
}

?>
