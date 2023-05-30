<?php

class HM_Test_Feedback_FeedbackModel extends HM_Model_Abstract
{
    // Типы фидбеков
    const TYPE_TEST     = 0;
    const TYPE_QUESTION = 1;
    const TYPE_ANSWER   = 2;
    
    // Типы событий
    const EVENT_INVALID = 0;
    const EVENT_VALID   = 1;
    const EVENT_ANY     = 2;


    static function getEventId($event)
    {
        $events = array(
            'invalid' => self::EVENT_INVALID,
            'valid'   => self::EVENT_VALID,
            'any'     => self::EVENT_ANY
        );


        if($event == ''){
            return self::EVENT_INVALID;
        }else{
            return $events[trim($event)];
        }

    }

    static public function getTreshold($treshold){
        list($lft, $rgt) = explode(',', $treshold);
        $op = $lft[0];
        $lft = intval(substr($lft, 1));

        if($op == '('){
            $lft = $lft +1;
        }

        $op = substr($rgt, -1);
        $rgt = intval(substr($rgt, 0, strlen($rgt) - 1 ));
        if($op == '('){
            $rgt = $rgt - 1;
        }
        return array('min' => $lft, 'max' => $rgt);

    }

    static function getAnswerValues($values){
        $values = json_decode($values);
        return $values;
    }
    
    static public function factory($data, $default = 'HM_Test_Feedback_FeedbackModel')
    {
        
        if(isset($data['type'])){
            switch($data['type']){
                case self::TYPE_TEST:
                    $default = 'HM_Test_Feedback_Test_TestModel';
                    break;
                case self::TYPE_QUESTION:
                    $default = 'HM_Test_Feedback_Question_QuestionModel';
                    break;
                case self::TYPE_ANSWER:
                    $default = 'HM_Test_Feedback_Answer_AnswerModel';
                    break;
            }
        }
        return parent::factory($data, $default);
    }
    
    
    
    
    
    
    
    
    
    
}