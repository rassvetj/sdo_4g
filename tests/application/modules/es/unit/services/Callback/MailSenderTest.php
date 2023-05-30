<?php
function shut() {
    var_dump(error_get_last());
}
//register_shutdown_function('shut');
/**
 * Description of MailSenderTest
 *
 * @author slava
 */
class MailSenderTest extends \PHPUnit_Framework_TestCase {
    
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
    
    public function mailRendererAndMailSubjectRendererDataProvider() {
        return array(
            array(
                'forumAddMessage',
                array(
                    'title' => $title = 'Message forum title',
                    'user_name' => $name = 'David',
                    'text' => $text = 'Hello my dear friends :-)',
                    'created' => $date = date("Y-m-d H:i:s"),
                    'theme' => $theme = 'Празднуем праздник'
                ),
                $name.' оставил(-а) сообщение в тему \''.$theme.'\'',
                $text.' '.$date,
                'Forum'
            ),
            array(
                'forumInternalAddMessage',
                array(
                    'title' => $title = 'Message forum title',
                    'user_name' => $name = 'David',
                    'text' => $text = 'Hello my dear friends :-)',
                    'created' => $date = date("Y-m-d H:i:s"),
                    'theme' => $theme = 'Празднуем праздник',
                    'course_name' => $course = 'the best Subject'
                ),
                $name.' оставил(-а) сообщение в тему \''.$theme.'\' форума в \''.$course.'\'',
                $text.' '.$date,
                'Forum'
            ),
            array(
                'blogAddMessage',
                array(
                    'title' => $title = 'О мой великий пост',
                    'created' => $date = date('Y-m-d H:i:s'),
                    'user_name' => $name = 'Peter Jacobson',
                    'body' => $body = 'Let\'s start our story ... '
                ),
                $name.' оставил(-а) пост \''.$title.'\' в блоге',
                $body.' '.$date,
                'Blog'
            ),
            array(
                'blogInternalAddMessage',
                array(
                    'title' => $title = 'О мой великий пост',
                    'created' => $date = date('Y-m-d H:i:s'),
                    'user_name' => $name = 'Peter Jacobson',
                    'course_name' => $course = 'Super Course',
                    'body' => $body = 'Let\'s start our story ... '
                ),
                $name.' оставил(-а) пост \''.$title.'\' в блоге курса \''.$course.'\'',
                $body.' '.$date,
                'Blog'
            ),
            array(
                'wikiAddPage',
                array(
                    'author' => $name = 'Петя Васечкин',
                    'title' => $title = 'Wellcome to wiki',
                    'created' => $date = date('Y-m-d H:i:s'),
                ),
                $name.' создал(-а) новую страницу wiki в основном канале',
                'Создана новая страница \''.$title."'\n".$date,
                'WikiArticles'
            ),
            array(
                'wikiModifyPage',
                array(
                    'author' => $name = 'Vova Putin',
                    'title' => $title = 'Всё будет зашибись',
                    'changed' => date('Y-m-d H:i:s'),
                ),
                $name.' изменил(-а) содержимое wiki в основном канале',
                'Изменение на странице \''.$title."'\n".$date,
                'WikiArticles'
            ),
            array(
                'wikiInternalAddPage',
                array(
                    'author' => $name = 'Dmitry Medvedev',
                    'title' => $title = 'Да нифига, Вова, зашибись всё не будет!',
                    'created' => $date = date('Y-m-d H:i:s'),
                    'course_name' => $course = 'Nuclear War'
                ),
                $name.' создал(-а) страницу wiki в \''.$course.'\'',
                'Создана новая страница \''.$title.'\''."\n".$date,
                'WikiArticles'
            ),
            array(
                'wikiInternalModifyPage',
                array(
                    'author' => $name = 'Dmitry Gryzlov',
                    'title' => $title = 'Dima, ne spor\' s Vovoi',
                    'changed' => $date = date('Y-m-d H:i:s'),
                    'course_name' => $course = 'Nuclear War'
                ),
                $name.' изменил(-а) содержимое wiki в \''.$course.'\'',
                'Изменение на странице \''.$title.'\''."\n".$date,
                'WikiArticles'
            ),
            array(
                'courseAddMaterial',
                array(
                    'title' => $title = 'New material',
                    'course_name' => $course = 'My course',
                    'author' => $name = 'Serj Shoigu',
                    'created' => $date = date('Y-m-d H:i:s')
                ),
                'В курс \''.$course.'\' добавлены новые материалы',
                $name.' добавил(-а) новые материалы в курс \''.$course.'\''."\n".$date,
                'Resource'
            ),
            array(
                'courseAttachLesson',
                array(
                    'author' => $name = 'John McLafflin',
                    'course_name' => $course = 'Super Mega Course',
                    'date' => $date = date('Y-m-d H:i:s'),
                ),
                'В курсе \''.$course.'\' назначено новое задание',
                $name.' назначил(-а) новое задание в курс \''.$course.'\''."\n".$date,
                'LessonAssign'
            ),
            array(
                'courseTaskComplete',
                array(
                    'user_name' => $name = 'Iosif Djugashvili',
                    'course_name' => $course = 'To Get the Reihstag',
                    'lesson_name' => $title = 'Kill Gitler',
                    'date' => $date = date('Y-m-d H:i:s')
                ),
                'В курсе \''.$course.'\' выполнено задание',
                $name.' выполнил(-а) задание \''.$title.'\' в курсе \''.$course.'\''."\n".$date,
                'Interview'
            ),
            array(
                'courseTaskScoreTriggered',
                array(
                    'course_name' => $course = 'Super Course',
                    'lesson_title' => $lesson = 'Super Task',
                    'date' => $date = date('Y-d-m H:i:s'),
                ),
                'В курсе \''.$course.'\' выставлена оценка за занятие',
                'Вам выставлена оценка за занятие \''.$lesson.'\' в курсе \''.$course.'\''."\n".$date,
                'LessonAssign'
            ),
            array(
                'courseScoreTriggered',
                array(
                    'course_name' => $course = 'Постигаем ELS',
                    'date' => $date = date('Y-m-d H:i:s')
                ),
                'Выставлена итоговая оценка за курс \''.$course.'\'',
                'Вам выставлена итоговая оценка за курс \''.$course.'\''."\n".$date,
                'SubjectMark'
            )
        );
    }
    
    /**
     * @dataProvider mailRendererAndMailSubjectRendererDataProvider
     * @covers Es_Service_Callback_MailRenderer::getCallback()
     * @covers Es_Service_Callback_MailSubjectRenderer::getCallback()
     * @group renderer
     */
    public function testMailRendererAndMailSubjectRendererCallbacks($eventType, $params, $subject, $expectedMessageText, $service) {
        
        $service = self::$eventServerDispacther->getService($service);
        $event = new Es_Entity_Event;
        $event->setParams($params);
        $event->setUserListGetter($service);
        $event->setEventTypeStr($eventType);
        
        $mailSubjectRenderEvent = self::$eventServerDispacther->trigger(
            Es_Service_Dispatcher::EVENT_MAIL_SUBJECT_RENDER,
            self::$eventServerDispacther->getEventActor(),
            array('event' => $event)
        );
        $mailSubject = $mailSubjectRenderEvent->getReturnValue();
        $this->assertEquals($subject, $mailSubject);
        
        $mailBodyRenderEvent = self::$eventServerDispacther->trigger(
            Es_Service_Dispatcher::EVENT_MAIL_RENDER,
            self::$eventServerDispacther->getEventActor(),
            array('event' => $event)
        );
        $mailBody = $mailBodyRenderEvent->getReturnValue();
        $this->assertStringStartsWith($expectedMessageText, trim(strip_tags($mailBody)));
        
        
    }
    
    public function testGetCallback() {
        $forumService = static::$eventServerDispacther->getService('LessonAssign');
        $event = new Es_Entity_Event;
        $event->subjectId(208);
        $event->setUserListGetter($forumService);
        $event->setParam('title', "message forum title");
//        $event->setEventTypeStr('forumAddMessage');
        $event->setEventType(15);
        $event->setParam('user_id', 5);
        $event->setParam('user_name', "David");
        $event->setParam('text', "message text forum");
        $event->setParam('created', time());
        $event->setParam('user_name', 'Вася Пупкин');
        $event->setParam('theme', 'Празднуем праздник');
        static::$eventServerDispacther->trigger(
            Es_Service_Dispatcher::EVENT_PUSH_POST,
            static::$eventServerDispacther->getEventActor(),
            array('event' => $event)
        );
    }
    
    public function emailProvider() {
        return array(
            array('slava@tutrinov.pro', true),
            array('', false),
            array('vyacheslav.tutrinov@hypermethod.com', true),
            array('user2@gmail.com', true),
            array('ertgserthgsrtg@', false)
        );
    }
    
    /**
     * @dataProvider emailProvider
     * @group validator
     */
    public function testZendEmailValidator($email, $ecpected) {
        $validator = new Zend_Validate_EmailAddress;
        $this->assertEquals($ecpected, $validator->isValid($email));
    }
    
    public function tearDown() {
        parent::tearDown();
    }
    
    public static function tearDownAfterClass() {
        parent::tearDownAfterClass();
    }
    
}

?>
