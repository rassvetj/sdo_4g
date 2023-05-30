<?php
class HM_Test_Feedback_FeedbackService extends HM_Service_Abstract
{

    public function getFeedback($status, $questionArray, $testId)
    {

        $this->getService('')->fetchAll(array('test_id =?' => $testId, 'question_id = ?' => $questionArray['QUESTION_ID']));


    }

    public function getFeedbackForAnswers($status, $answerArray, $question, $testId, $j = 0)
    {

        $msg = array();
        switch($question->qtype){
            case HM_Question_QuestionModel::TYPE_FILLINGAPS:
                preg_match('#form\[[0-9]\]\[otvet\]\[([0-9])\]#iU', $answerArray['ANSWER_NAME'], $match);
                $answerId = intval($match[1]);
                $feedbacks = $this->fetchAll(
                    array(
                        'test_id = ?'       => $testId,
                        'question_id = ?'   => $question->kod,
                        'answer_id = ?'     => $j,
                        'show_event IN (?)' => array($status, HM_Test_Feedback_FeedbackModel::EVENT_ANY),
                        'show_on_values = ?'   => 'a:0:{}'
                    )
                );


                $forValues = $this->fetchAll(
                    array(
                        'test_id = ?'       => $testId,
                        'question_id = ?'   => $question->kod,
                        'answer_id = ?'     => $j,
                        'show_on_values != ?'   => 'a:0:{}'
                    )
                );

                foreach($feedbacks as $feedback){
                    $msg[] = $feedback->text;
                }
                foreach($forValues as $forValue){
                    $temp = unserialize($forValue->show_on_values);

                    $answers = explode(',|', $answerArray['ANSWER_VAL']);

                    if(array_search($temp[0], $answers) !== false){
                        $msg[] = $forValue->text;
                    }
                }



                break;
            case HM_Question_QuestionModel::TYPE_CONFORMITY:
            case HM_Question_QuestionModel::TYPE_CLASS:
                preg_match('#form\[[0-9]\]\[([0-9])\]#iU', $answerArray['ANSWER_NAME'], $match);
                $answerId = intval($match[1]);
                $feedbacks = $this->fetchAll(
                    array(
                        'test_id = ?'       => $testId,
                        'question_id = ?'   => $question->kod,
                        'answer_id = ?'     => $answerId,
                        'show_event IN (?)' => array($status, HM_Test_Feedback_FeedbackModel::EVENT_ANY)
                    )
                );

                foreach($feedbacks as $feedback){
                    $msg[] = $feedback->text;
                }


            case HM_Question_QuestionModel::TYPE_ONE:
            default:
                $feedbacks = $this->fetchAll(
                    array(
                        'test_id = ?'       => $testId,
                        'question_id = ?'   => $question->kod,
                        'answer_id = ?'     => $answerArray['ANSWER_VAL'],
                        'show_event IN (?)' => array($status, HM_Test_Feedback_FeedbackModel::EVENT_ANY)
                    )
                );

                foreach($feedbacks as $feedback){
                    $msg[] = $feedback->text;
                }
                break;

        }

        return $msg;

    }


    public function getFeedbackForQuestion($treshold, $question, $testId)
    {
        $feedbacks = $this->fetchAll(
            array(
                'test_id = ?'       => $testId,
                'question_id = ?'   => $question,
                'type = ?' => HM_Test_Feedback_FeedbackModel::TYPE_QUESTION,
                'treshold_max >= ?' => $treshold,
                'treshold_min <= ?' => $treshold
            )
        );

        foreach($feedbacks as $feedback){
            $msg[] = $feedback->text;
        }
        return $msg;
    }



    static public function isFeedbackable()
    {
        $session = $_SESSION['s'];
        $currentQuestions = $session['ckod'];
        $result = false;

        $questions = Zend_Registry::get('serviceContainer')->getService('Question')->fetchAll(array('kod IN (?)' => $currentQuestions));

        foreach($questions as $question){

            // TODO temporaly
            if(in_array($question->qtype, array(HM_Question_QuestionModel::TYPE_SORT, HM_Question_QuestionModel::TYPE_FORM))){
                continue;
            }


            $feed = Zend_Registry::get('serviceContainer')->getService('TestFeedback')->fetchAll(array(
                'question_id = ?'   => $question->kod
            ));

            if(count($feed) > 0){
                $result = true;
                break;
            }

        }
        return $result;
    }
    
    
}