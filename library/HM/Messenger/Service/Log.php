<?php

class HM_Messenger_Service_Log extends HM_Messenger_Service_Abstract
{
    public function update(SplSubject $message)
    {
        Zend_Registry::get('log_mail')->debug(
            sprintf(
                "\nFROM ID: %d\nFROM EMAIL: %s\nTO ID: %d\nTO EMAIL: %s\nSUBJECT: %s\nMESSAGE: %s\n",
                $message->getSenderId(),
                $message->getSender()->EMail,
                $message->getReceiverId(),
                $message->getReceiver()->EMail,
                $message->getSubject(),
                $message->getMessage()
            )
        );
    }
}