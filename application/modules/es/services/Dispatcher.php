<?php
/**
 * Description of Dispatcher
 *
 * @author slava
 */
class Es_Service_Dispatcher extends HM_Service_Primitive {
    
    const EVENT_PUSH_PRE = "eventPush.pre";
    const EVENT_PUSH = "eventPush";
    const EVENT_PUSH_POST = "eventPush.post";
    
    const EVENT_PULL_PRE = "eventPull.pre";
    const EVENT_PULL = "eventPull";
    const EVENT_PULL_POST = "eventPull.post";
    
    const EVENT_PULL_STATS_PRE = 'eventPullStats.pre';
    const EVENT_PULL_STATS = 'eventPullStats';
    const EVENT_PULL_STATS_POST = 'eventPullStats.post';
    
    const EVENT_UNSUBSCRIBE_PRE = "eventUnsubscribe.pre";
    const EVENT_UNSUBSCRIBE = "eventUnsubscribe";
    const EVENT_UNSUBSCRIBE_POST = "eventUnsubscribe.post";
    
    const EVENT_PULL_NOTIFIES_PRE = "eventPushNotifies.pre";
    const EVENT_PULL_NOTIFIES = "eventPushNotifies";
    const EVENT_PULL_NOTIFIES_POST = "eventPushNotifies.post";

    const EVENT_GET_TYPES_LIST_PRE = 'eventTypeslistGet.pre';
    const EVENT_GET_TYPES_LIST = 'eventTypeslistGet';
    const EVENT_GET_TYPES_LIST_POST = 'eventTypeslistGet.post';
    
    const EVENT_MAIL_RENDER = 'eventMailRender';
    const EVENT_MAIL_SUBJECT_RENDER = 'eventMailSubjectRender';
    
    const EVENT_REPORT_MAIL_SEND = 'eventReportMailSend';
    const EVENT_REPORT_MAIL_RENDER = 'eventReportMailRender';
    
    const EVENT_UPDATE_NOTIFY_PRE = "updateNotify.pre";
    const EVENT_UPDATE_NOTIFY = "updateNotify";
    const EVENT_UPDATE_NOTIFY_POST = "updateNotify.post";
    
    /**
     *
     * @var sfEventDispatcher
     */
    protected $eventDispatcher = null;
    
    /**
     *
     * @var Es_Service_EventActor
     */
    protected $eventActor = null;
    
    public function __construct($mapperClass = null, $modelClass = null, $adapterClass = null) {
        $this->setEventDispatcher(new sfEventDispatcher);
        return parent::__construct($mapperClass, $modelClass, $adapterClass);
    }
    
    public function attach($name, $callback) {
        return $this->getEventDispatcher()->connect($name, $callback);
    }
    
    public function trigger($name, $subject, $params = null) {
        return $this->getEventDispatcher()->notify(new sfEvent($subject, $name, $params));
    }
    
    /**
     * 
     * @return sfEventDispatcher
     */
    public function getEventDispatcher() {
        return $this->eventDispatcher;
    }

    /**
     * 
     * @param sfEventDispatcher $eventDispatcher
     */
    public function setEventDispatcher(sfEventDispatcher $eventDispatcher) {
        $this->eventDispatcher = $eventDispatcher;
    }
    
    /**
     * 
     * @return Es_Service_EventActor
     */
    public function getEventActor() {
        return $this->eventActor;
    }

    /**
     * 
     * @param Es_Service_EventActor $eventActor
     */
    public function setEventActor(Es_Service_EventActor $eventActor) {
        $this->eventActor = $eventActor;
    }
    
    public function defineActor(Es_Service_Factory $factory, Es_Service_EventActor $eventActor) {
        $storage = $factory->getEventsStorage();
        $storage->setEsEventDispatcher($this);
        $eventActor->setStorage($storage);
        $this->setEventActor($eventActor);
    }
    
    /**
     * define triggers
     */
    public function defineTriggers(Es_Service_Factory $factory) {
        $actor = $this->getEventActor();        
        
        $this->attach(self::EVENT_PUSH, function($ev) use ($actor) {
            $params = $ev->getParameters();
            $eventActivity = $params['event'];
            $actor->push($eventActivity);
        });
        $this->attach(self::EVENT_PUSH_POST,
            $factory->getTriggerCallback('EventTypeNameDefiner')->getCallback()
        );
        $this->attach(self::EVENT_PUSH_POST,
            $factory->getTriggerCallback('MailSender')->getCallback()
        );
        $this->attach(self::EVENT_PUSH_POST, function($ev) use($factory) {
            $params = $ev->getParameters();
            $event = $params['event'];
            /*@var $filter Es_Entity_AbstractFilter */
            if ($event) {
                $filter = $factory->newFilter();
                $filter->setUserId((int)$factory->getService('User')->getCurrentUserId());
                $filter->setEventId($event->getId());
                $factory->getService('EventServerDispatcher')->trigger(
                    Es_Service_Dispatcher::EVENT_UNSUBSCRIBE,
                    $factory,
                    array('filter' => $filter)
                );
            }
        });
        $this->attach(self::EVENT_PULL, function($ev) use ($actor) {
            $params = $ev->getParameters();
            $eventsFilter = $params['filter'];
            $eventsList = $actor->pull($eventsFilter);
            $ev->setReturnValue($eventsList);
        });
        $this->attach(self::EVENT_PULL_STATS, function($ev) use ($actor) {
            $params = $ev->getParameters();
            $statsFilter = $params['filter'];
            $stats = $actor->pullStats($statsFilter);
            $ev->setReturnValue($stats);
        });
        $this->attach(self::EVENT_PULL_NOTIFIES, function($ev) use ($actor) {
            $params = $ev->getParameters();
            $filter = $params['filter'];
            $notifies = $actor->pullNotifies($filter);
            $ev->setReturnValue($notifies);
        });
        $this->attach(self::EVENT_UPDATE_NOTIFY, function($ev) use ($actor) {
            $params = $ev->getParameters();
            $notify = $params['notify'];
            $result = $actor->updateNotify($notify);
            $ev->setReturnValue($result);
        });
        $this->attach(self::EVENT_MAIL_RENDER,
            $factory->getTriggerCallback('MailRenderer')->getCallback()
        );
        $this->attach(self::EVENT_MAIL_RENDER,
            $factory->getTriggerCallback('MailHtmlWrapper')->getCallback()
        );
        $this->attach(self::EVENT_MAIL_SUBJECT_RENDER,
            $factory->getTriggerCallback('MailSubjectRenderer')->getCallback()
        );
        $eventViewCallback = $factory->getTriggerCallback('EventViewTrigger');
        $this->attach(
            self::EVENT_UNSUBSCRIBE,
            $eventViewCallback->getCallback(array('actor' => $actor))
        );
        $this->attach(self::EVENT_GET_TYPES_LIST, function($ev) use ($actor) {
            $typesList = $actor->getEventTypesList();
            $ev->setReturnValue($typesList);
        });
        
        
        $this->attach(self::EVENT_REPORT_MAIL_SEND,
            $factory->getTriggerCallback('ReportMailSender')->getCallback()
        );
        $this->attach(self::EVENT_REPORT_MAIL_RENDER,
            $factory->getTriggerCallback('ReportMailRender')->getCallback()
        );
        $this->attach(self::EVENT_REPORT_MAIL_RENDER,
            $factory->getTriggerCallback('MailHtmlWrapper')->getCallback()    
        );
        
    }
    
}

?>
