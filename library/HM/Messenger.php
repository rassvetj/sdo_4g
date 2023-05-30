<?php

class HM_Messenger implements SplSubject
{

    const SYSTEM_USER_ID = 0;

    const TEMPLATE_REG = 1;
    const TEMPLATE_ASSIGN_ROLE = 2;
    const TEMPLATE_ASSIGN_SUBJECT = 3;
    const TEMPLATE_ASSIGN_COURSE = 4;
    const TEMPLATE_BEFORE_END_TRAINING = 5;
    const TEMPLATE_GRADUATED = 6;
    const TEMPLATE_ORDER = 7;
    const TEMPLATE_ORDER_REGGED = 8;
    const TEMPLATE_ORDER_ACCEPTED = 9;
    const TEMPLATE_ORDER_REJECTED = 10;
    const TEMPLATE_PASS = 11;
    const TEMPLATE_PRIVATE = 12;
    const TEMPLATE_SUBSCRIPTION_UPDATED = 13;
    const TEMPLATE_POLL_STUDENTS = 14;
    const TEMPLATE_POLL_TEACHERS = 15;
    const TEMPLATE_POLL_LEADERS = 16;
    const TEMPLATE_MULTIMESSAGE = 17;
    const TEMPLATE_FORUM_NEW_ANSWER = 18;
    const TEMPLATE_FORUM_NEW_HIDDEN_ANSWER = 19;
    const TEMPLATE_FORUM_NEW_MARK = 20;
    const TEMPLATE_REG_CONFIRM_EMAIL = 21;
    const TEMPLATE_UNBLOCK = 22;
    const TEMPLATE_SUPPORT_MESSAGE = 23;
    const TEMPLATE_SUPPORT_STATUS = 24;
	
    const TEMPLATE_TUTOR_ASSIGN_COURSE = 25;
    const TEMPLATE_MOTIVATION_MESSAGE = 26;

    const DEFAULT_MESSAGE = "[TEXT]";
    const DEFAULT_SUBJECT = "[SUBJECT]";
    const DEFAULT_MESSAGE_DELIMITER = "\n<br/>\n<br/>";

    private $_observers = array();
    private $_templateId = null;
    private $_template   = null;

    private $_replacements  = array();
    private $_message       = null;
    private $_subject       = null;
    private $_senderId      = null;
    private $_sender        = null;
    private $_receiverId    = null;
    private $_receiver      = null;

    private $_roomSubject = null;
    private $_roomSubjectId = null;

    private $_cache = array();
    private $_msgChannels  = array();

    private $_ical = null;

    public function __construct()
    {
        //$this->_view = new Zend_View();
		//$this->_view->setScriptPath(APPLICATION_PATH . '/mails');
    }

    public function assign($values)
    {
    	$this->_replacements = array(
            'URL' => Zend_Registry::get('view')->serverUrl('/')
        );

    	if (is_array($values) && count($values)) {
    		foreach($values as $key => $value)
    		{
                if (strtolower($key) == 'subject_id') {
                    if (!isset($this->_cache['SUBJECT'][$value])) {
                        $this->_cache['SUBJECT'][$value] = $this->getOne($this->getService('Subject')->find($value));
                    }

                    $key = 'COURSE';
                    $value = $this->_cache['SUBJECT'][$value]->name;
                }

                if (strtolower($key) == 'course_id') {
                    if (!isset($this->_cache['COURSE'][$value])) {
                        $this->_cache['COURSE'][$value] = $this->getOne($this->getService('Course')->find($value));
                    }

                    $key = 'COURSE';
                    $value = $this->_cache['COURSE'][$value]->Title;
                }

                if (strtolower($key) == 'lesson_id') {
                    if (!isset($this->_cache['LESSON'][$value])) {
                        $this->_cache['LESSON'][$value] = $this->getOne($this->getService('Lesson')->find($value));
                    }

                    $key = 'LESSON';
                    $value = $this->_cache['LESSON'][$value]->title;
                }


    			$this->_replacements[strtoupper($key)] = $value;
    		}
    	}
    }

    public function assignValue($key, $value)
    {
		$this->_replacements[strtoupper($key)] = $value;
    }

    public function attach(SplObserver $obs)
    {
        $id = spl_object_hash($obs);
        $this->_observers[$id] = $obs;
    }

    public function detach(SplObserver $obs)
    {
        $id = spl_object_hash($obs);
        unset($this->_observers[$id]);
    }

    public function notify()
    {

        try{
            if($this->_template && $this->_template->enabled == false)
                return;
        } catch (Exception $e) {

        }

        foreach($this->_observers as $observer)
        {
            /*в services.xml к HM_Messenger attach HM_Message_MessageESTrigger должен быть перед
                attach HM_Messenger_Service_Mail. Если событие попадает в Ленту, то в зависимости от настроек уведомлений
                ES сам отправит письмо и HM_Messenger_Service_Mail не должен отрабатывать
            */
            $result = $observer->update($this);
            if (($observer instanceof HM_Message_MessageESTrigger) && $result) {
                break;
            }
        }
    }

    public function getService($name)
    {
        return Zend_Registry::get('serviceContainer')->getService($name);
    }

    public function getOne($collection)
    {
        if ($collection && count($collection)) {
            return $collection->current();
        }
        return false;
    }

    public function setTemplate($templateId)
    {
        $this->_templateId = $templateId;
        $this->_template = $this->getOne($this->getService('Notice')->fetchAll($this->getService('Notice')->quoteInto('type = ?', $templateId)));

    }
    
    public function getTemplateId()
    {
        return $this->_templateId;
    }

    public function replace($text)
    {
        if (is_array($this->_replacements) && count($this->_replacements)) {
            foreach($this->_replacements as $key => $value)
            {
                $text = str_replace('['.$key.']', $value, $text);
            }
        }

        return $text;
    }

    public function send($senderId, $receiverId = 0, $replacements = null)
    {
        $this->_preprocessData($senderId, $receiverId, $replacements);
        $this->notify();
    }

    private function _preprocessData($senderId, $receiverId = 0, $replacements = null)
    {
        if (!$this->_template) {
            throw new HM_Messenger_Exception(sprintf(_('Шаблон системных сообщений #%d не найден.'), $this->_templateId));
        }

        $this->_message = self::DEFAULT_MESSAGE;
        $this->_subject = self::DEFAULT_SUBJECT;

        $this->_receiverId = $receiverId;
        $this->_receiver = null;
        $this->_senderId = $senderId;
        $this->_sender = null;

        if (null !== $replacements) {
            $this->assign($replacements);
        }

        if (strlen($this->_template->title)) {
            $this->_subject = $this->_template->title;
        }

        if (strlen($this->_template->message)) {
            $this->_message = $this->_template->message;
        }

        $this->_subject = $this->replace($this->_subject);
        $this->_message = $this->replace($this->_message);
    }

    public function getSenderId()
    {
        return $this->_senderId;
    }

    public function getSender()
    {
        if (null === $this->_sender)
        {
            $this->_sender = $this->getOne($this->getService('User')->find($this->getSenderId()));
        }

        if (!$this->_sender) {
            $this->_sender = $this->getDefaultUser();
        }

        return $this->_sender;
    }

    public function getReceiverId()
    {
        return $this->_receiverId;
    }

    public function getReceiver()
    {
        if (null === $this->_receiver)
        {
            $this->_receiver = $this->getOne($this->getService('User')->find($this->getReceiverId()));
        }

        if (!$this->_receiver) {
            $this->_receiver = $this->getDefaultUser();
        }

        return $this->_receiver;
    }

    public function getDefaultUser()
    {
        $user = new HM_User_UserModel(
            array(
                'EMail' => $this->getService('Option')->getOption('dekanEMail'),
                'FirstName' => $this->getService('Option')->getOption('dekanName'),
                'LastName' => '',
                'Patronymic' => '',
                'MID' => 0
            )
        );

        return $user;
    }

    public function getMessage()
    {
        return $this->_message;
    }

    public function getSubject()
    {
        return $this->_subject;
    }

    public function setRoom($subject, $subjectId)
    {
        $this->_roomSubject = $subject;
        $this->_roomSubjectId = $subjectId;
    }

    /**
     * @return array
     */
    public function getRoom()
    {
        return array($this->_roomSubject, $this->_roomSubjectId);
    }

    public function setOptions($templateId, $values = array(), $subject = '', $subjectId = 0)
    {
        $this->setTemplate($templateId);
        $this->assign($values);
        $this->setRoom($subject, $subjectId);
    }

    /**
     * Добавление сообщения в очередь.
     * @param int $senderId
     * @param int $receiverId
     * @param $templateId
     * @param array $values
     * @param string $subject
     * @param int $subjectId
     */
    public function addMessageToChannel($senderId, $receiverId = 0, $templateId, $values = array(), $subject = '', $subjectId = 0)
    {
        $this->setOptions($templateId, $values, $subject, $subjectId);
        $this->_preprocessData($senderId, $receiverId);

        $classOptions = $this->__toArray();
        $key          = $classOptions['templateId'] .  '_' . $classOptions['receiverId'];

        if( isset($this->_msgChannels[$key]) ) {
            $this->_msgChannels[$key]['messages'][] = $classOptions['message'];
        } else {
            $this->_msgChannels[$key]               = $classOptions;
            $this->_msgChannels[$key]['messages'][] = $classOptions['message'];
        }


    }

    /**
     * Отправка очереди сообщений
     * @param $type указывается, если нужно отправить сообщения только данного типа.
     */
    public function sendAllFromChannels($type = null)
    {
        foreach ($this->_msgChannels as $channelKey=>$messageItem) {
            if ($type !== null && $type != $messageItem['templateId']) continue;

            $messageItem['message'] = implode(self::DEFAULT_MESSAGE_DELIMITER, array_unique($messageItem['messages']));
            $this->__fromArray($messageItem);
            $this->setTemplate(self::TEMPLATE_MULTIMESSAGE);
            $this->_template->title = $messageItem['template']->title;
            $this->notify();
            unset($this->_msgChannels[$channelKey]);
        }
    }

    public function __toArray()
    {
        return array('message'       => $this->_message,
                     'receiver'      => $this->_receiver,
                     'receiverId'    => $this->_receiverId,
                     'replacements'  => $this->_replacements,
                     'roomSubject'   => $this->_roomSubject,
                     'roomSubjectId' => $this->_roomSubjectId,
                     'sender'        => $this->_sender,
                     'senderId'      => $this->_senderId,
                     'template'      => $this->_template,
                     'templateId'    => $this->_templateId,
                     'subject'       => $this->_subject);
    }

    private function __fromArray($dataArray)
    {

        $this->_message       = (isset($dataArray['message']))?       $dataArray['message']      : NULL;
        $this->_receiver      = (isset($dataArray['receiver']))?      $dataArray['receiver']     : NULL;
        $this->_receiverId    = (isset($dataArray['receiverId']))?    $dataArray['receiverId']   : NULL;
        $this->_replacements  = (isset($dataArray['replacements']))?  $dataArray['replacements'] : NULL;
        $this->_roomSubject   = (isset($dataArray['roomSubject'])) ?  $dataArray['roomSubject']  : NULL;
        $this->_roomSubjectId = (isset($dataArray['roomSubjectId']))? $dataArray['roomSubjectId']: NULL;
        $this->_sender        = (isset($dataArray['sender']))?        $dataArray['sender']       : NULL;
        $this->_senderId      = (isset($dataArray['senderId']))?      $dataArray['senderId']     : NULL;
        $this->_template      = (isset($dataArray['template']))?      $dataArray['template']     : NULL;
        $this->_templateId    = (isset($dataArray['templateId']))?    $dataArray['templateId']   : NULL;
        $this->_subject       = (isset($dataArray['subject']))?       $dataArray['subject']      : NULL;
    }

    public function setIcal(HM_Ical_Calendar $ical)
    {
        $this->_ical = $ical;
    }

    public function getIcal()
    {
        return $this->_ical;
    }

}
