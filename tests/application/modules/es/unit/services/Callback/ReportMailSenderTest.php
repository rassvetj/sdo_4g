<?php
/**
 * Description of ReportMailSender
 *
 * @author slava
 */
class ReportMailSenderTest extends \PHPUnit_Framework_TestCase {
    
    protected static $bootstrap = null;
    protected static $application = null;
    protected static $eventServerDispacther = null;
    
    public static function setUpBeforeClass() {
        static::$application = new Zend_Application(APPLICATION_ENV,
            APPLICATION_PATH .DIRECTORY_SEPARATOR.'settings'.DIRECTORY_SEPARATOR.'config.ini');
        static::$application->bootstrap();
        static::$eventServerDispacther = Zend_Registry::get('serviceContainer')->getService('EventServerDispatcher');
        parent::setUpBeforeClass();
    }
    
    public function setUp() {
        parent::setUp();
    }
    
    public function testReportMailSenderCallback() {
        
        $filter = new Es_Entity_Filter();
        $filter->setUserId(1);
        $filter->setToTime(time());
        $filter->setFromTime($filter->getToTime() - (86400*4));
        $filter->setTypes(array(
            'forumAddMessage',
            'blogAddMessage',
            'wikiAddPage',
            'wikiModifyPage',
            'forumInternalAddMessage',
            'blogInternalAddMessage',
            'wikiInternalAddPage',
            'wikiInternalModifyPage',
            'courseAddMaterial',
            'courseAttachLesson',
            'courseScoreTriggered',
            'courseTaskComplete',
            'commentAdd',
            'commentInternalAdd',
            'courseTaskScoreTriggered',
            'personalMessageSend'
        ));
        static::$eventServerDispacther->trigger(
            Es_Service_Dispatcher::EVENT_REPORT_MAIL_SEND,
            static::$eventServerDispacther,
            array('filter' => $filter)
        );
        
    }
    
}

?>
