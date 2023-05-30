<?php
/**
 * Description of ReportMailRender
 *
 * @author slava
 */
class Es_Service_Callback_ReportMailRender implements Es_Service_Callback_CallbackBehavior {
    
    public function getCallback(array $params = array()) {
        return function($ev) {
            $parameters = $ev->getParameters();
            $filter = $parameters['filter'];
            $subject = $ev->getSubject();
            $eventListPullEventResult = $subject->getService('EventServerDispatcher')->trigger(
                Es_Service_Dispatcher::EVENT_PULL,
                $subject,
                array('filter' => $filter)
            );
            $eventList = $eventListPullEventResult->getReturnValue();
            
            $notifies = array();
            $discussions = array();
            $messages = array();
            /*@var $event Es_Entity_AbstractEvent|Es_Entity_Event */
            foreach ($eventList as $event) {
                $eventSubjectRenderEventResult = $subject->getService('EventServerDispatcher')->trigger(
                        Es_Service_Dispatcher::EVENT_MAIL_SUBJECT_RENDER,
                        $subject,
                        array('event' => $event)
                );
                $subjectContent = $eventSubjectRenderEventResult->getReturnValue();
                $subjectContent .= ' '.date('Y-m-d H:i:s');
                switch ($event->getGroupType()->getId()) {
                    case Es_Entity_AbstractGroupType::GROUP_TYPE_PERSONAL_MESSAGE;
                        $messages[] = $subjectContent;
                        break;
                    case Es_Entity_AbstractGroupType::GROUP_TYPE_DISCUSSION;
                        $discussions[] = $subjectContent;
                        break;
                    case Es_Entity_AbstractGroupType::GROUP_TYPE_NOTIFICATION:
                        $notifies[] = $subjectContent;
                        break;
                }
            }
            
            $tplPath = realpath(__DIR__.'/../../data/mail_templates/');
            if (!file_exists($tplPath)) {
                throw new Es_Exception_Runtime('Mail templatepath \''.$tplPath.'\' doesn\'t exists');
            }
            $tplName = 'report_template.tpl';
            $tpl = $tplPath . '/' . $tplName;
            if (!file_exists($tpl)) {
                throw new Es_Exception_Runtime('Mail template \'' . $tpl . '\' doesn\'t exists');
            }
            
            $ev->offsetSet('emptyEventsList', false);
            if (sizeof($notifies) === 0 && sizeof($discussions) === 0 && sizeof($messages) === 0) {
                $ev->offsetSet('emptyEventsList', true);
            }
            
            $view = new Zend_View();
            $view->setScriptPath($tplPath);
            $view->assign('notifies', $notifies);
            $view->assign('discussions', $discussions);
            $view->assign('messages', $messages);
            $view->assign('filter', $filter);
            $mailContent = $view->render($tplName);
            $ev->offsetSet('mailView', $view);
            $ev->setReturnValue($mailContent);
        };
    }
    
}

?>
