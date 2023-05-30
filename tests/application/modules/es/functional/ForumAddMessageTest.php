<?php
/**
 * Description of ForumAddMessageTest
 *
 * @author slava
 */
class ForumAddMessageTest extends \PHPUnit_Extensions_Selenium2TestCase {
    
    public static $inst = null;
    
    public function setUp() {
        if (static::$inst === null) {
            static::$inst = $this;
            $this->setBrowserUrl("http://els.develop.pr/");
            $this->setBrowser("firefox");
            $this->shareSession(true);
        }
    }
    
    public function testAddMessageToGlobalStream() {
        $this->url("http://els.develop.pr/");
        $this->timeouts()->implicitWait(10000);
        $this->byCssSelector(".login.action-link")->click();
        $this->byId("login")->value("admin");
        $this->byId("password")->value("pass");
        $this->byId("submit")->click();
        $this->timeouts()->implicitWait(20000);
        
        /*
        $portalLink = $this->byXPath('//li[@data-submenu-id="menu-m03"]');
        $this->moveto($portalLink);
        $this->timeouts()->implicitWait(10000);
        $servicesLink = $this->byLinkText('Сервисы информационного взаимодействия Портала');
        $this->moveto($servicesLink);
        $servicesLink->click();
        $this->timeouts()->implicitWait(2000);
        $forumCheckbox = $this->byId("activity-2");
        if ($forumCheckbox->attribute('checked') != 'true') {
            $forumCheckbox->click();
        }
        $this->byId('submit')->click();
         * 
         */
        $servicesGlobalLink = $this->byXPath('//li[@data-submenu-id="menu-m99"]');
        $this->moveto($servicesGlobalLink);
        $this->timeouts()->implicitWait(1000);
        $this->byLinkText('Форум')->click();
        
        $topicLink = $this->byCssSelector('.topic-title a');
        $topicLink->click();
        $this->timeouts()->implicitWait(6000);
        $this->byLinkText('Новое сообщение')->click();
        $input = $this->byCssSelector("div.topic-replyeditor .topic-input input");
        $this->moveto($input);
        $input->click();
        $this->keys('Test Forum Message Title From Selenium2');
        $this->byCssSelector(".topic-replyeditor .mceIframeContainer iframe")->click();
        $this->keys('New Test Froum Message From Selenium2 Server');
        
        $this->waitUntil(function(){}, 10000);
        
        $this->byCssSelector('.topic-replyeditor-buttons #submit')->click();
        
        $this->timeouts()->implicitWait(10000);
        
//        $this->byCssSelector(".hm-roleswitcher-right")->click();
        
        
        
    }
    
}

?>
