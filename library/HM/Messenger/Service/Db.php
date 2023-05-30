<?php

class HM_Messenger_Service_Db extends HM_Messenger_Service_Abstract
{
    public function update(SplSubject $message)
    {
        list($roomSubject, $roomSubjectId) = $message->getRoom();
        return $this->getService('Message')->insert(
            array(
                'subject' => (string) $roomSubject,
                'subject_id' => $roomSubjectId,
                'from' => $message->getSenderId(),
                'to' => $message->getReceiverId(),
                'message' => $message->getMessage()
            )
        );
    }
}
