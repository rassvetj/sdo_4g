<?php

class HM_Forum_Message_MessageMapper extends HM_Mapper_Abstract
{
    public function fetchMessagesList($where = null, Zend_Config $config = null){
        if(is_null($config)) $config = new Zend_Config(array());
        $collection = $this->fetchAll($where);
        
        // Сортировка сообщений по разделам        
        $sections = array();
        foreach($collection as $msg){
            if(!isset($sections[$msg->section_id])) $sections[$msg->section_id] = array();
            $sections[$msg->section_id][$msg->message_id] = $msg;
        }
        
        // Древовидная структура
        if($config->as_tree) foreach($sections as &$section) $this->_messagesTree(&$section);
        
        // Сортировка по времени создания
        if($config->order_by_time) foreach($sections as &$section) $this->_timeSort(&$section, $config->order_reverse);
        
        return $sections;
    }
    
    protected function _messagesTree($collection){
        $unset = array();
        foreach($collection as $message){
            if($message->answer_to > 0){
                if (!is_a($collection[$message->answer_to], 'HM_Forum_Message_MessageModel')) continue;
                $collection[$message->answer_to]->addAnswer($message);
                $unset[] = $message->message_id;
            }
        }
        foreach($unset as $key) unset($collection[$key]);
        
        return $collection;
    }
    
    protected function _timeSort($collection, $reverse = null){
        $timeSorted = array();
        foreach($collection as $message){
            $timeSorted[strtotime($message->created)] = $message;
            $message->sortAnswersByTime($reverse);
        }
        $reverse ? krsort($timeSorted) : ksort($timeSorted);
        
        $collection = array();
        foreach($timeSorted as $message) $collection[$message->message_id] = $message;
        
        return $collection;
    }
    
}