<?php
/**
 * Description of DispatcherTest
 *
 * @author slava
 */
class EventActorTest extends PHPUnit_Framework_TestCase {
    
    protected static $bootstrap = null;
    protected static $application = null;
    protected static $eventServerDispacther = null;
    
    protected function setUp() {
        parent::setUp();
    }

    public static function setUpBeforeClass() {
        static::$application = new Zend_Application(APPLICATION_ENV,
            APPLICATION_PATH .DIRECTORY_SEPARATOR.'settings'.DIRECTORY_SEPARATOR.'config.ini');
        static::$application->bootstrap();
        static::$eventServerDispacther = Zend_Registry::get('serviceContainer')->getService('EventServerDispatcher');
        parent::setUpBeforeClass();
    }

    protected function tearDown() {
        parent::tearDown();
    }
    
    /**
     * @covers Es_Service_EventActor::pullNotifies
     */
    public function testPullNotifies() {
        $filter = new Es_Entity_Filter();
        $filter->setUserId(1);
        $event = static::$eventServerDispacther->trigger(
            Es_Service_Dispatcher::EVENT_PULL_NOTIFIES,
            static::$eventServerDispacther->getEventActor(),
            array('filter' => $filter)
        );
        $notifies = $event->getReturnValue();
        $this->assertInstanceOf('sfEvent', $event);
        $this->assertInstanceOf('Es_Entity_NotifiesList', $notifies);
        if ($notifies->count() > 0) {
            $first = $notifies->current();
            $this->assertInstanceOf('Es_Entity_AbstractNotify', $first);
        }
    }
    
    public function testUpdateNotify() {
        $notifyType = new Es_Entity_NotifyType;
        $notifyType->setId(1);
        $eventType = new Es_Entity_EventType;
        $eventType->setId(3);
        $notify = new Es_Entity_Notify;
        $notify->setIsActive(true);
        $notify->setEventType($eventType);
        $notify->setNotifyType($notifyType);
        $notify->setUserId(1);
        $ev = static::$eventServerDispacther->trigger(
            Es_Service_Dispatcher::EVENT_UPDATE_NOTIFY,
            static::$eventServerDispacther->getEventActor(),
            array('notify' => $notify)
        );
        $result = $ev->getReturnValue();
        $this->assertTrue(is_bool($result));
    }
    
    /**
     * @covers Es_Service_EventActor::push()
     * @group eventPush
     */
    public function testPushEvent() {
        $event = new Es_Entity_Event();
        $event->subjectId(20);
        $event->setParam('TEST', 'test data field value');
        $microtimeParts = explode(' ', microtime());
        $time = floatval($microtimeParts[1])+floatval($microtimeParts[0]);
        $event->setCreateTime($time);
        $event->setEventType(1);
        $event->setEventTypeStr('forumAddMessage');
        $event->setUserListGetter(static::$eventServerDispacther->getService('Forum'));
        $group = new Es_Entity_EventGroup;
        $group->setType('TEST_EVENT_GROUP');
        $group->setTriggerInstanceId(20);
        $group->setData(json_encode(array(
            'group_data_field' => 'field value'
        )));
        $event->setGroup($group);
        static::$eventServerDispacther->trigger(
            Es_Service_Dispatcher::EVENT_PUSH,
            static::$eventServerDispacther->getService('Forum'),
            array('event' => $event)
        );
    }

    public static function tearDownAfterClass() {
        parent::tearDownAfterClass();
    }

    
}

?>
