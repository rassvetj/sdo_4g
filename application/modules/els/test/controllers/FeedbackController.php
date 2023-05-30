<?php
class Test_FeedbackController extends HM_Controller_Action
{

    public function indexAction()
    {

        $questionFeedback = $_POST;
        $test = $this->getService('Test')->fetchAll(array('tid = ?' => $questionFeedback['TEST_ID']))->current();
        $testId = $test->test_id;

        foreach($questionFeedback['AR_QUESTION'] as &$question){
            $questionId = $question['QUESTION_ID'];

            // Временная заглушка
            if(in_array($question['QUESTION_TYPE'], array('SORT'))){
                $question['QUESTION_FEED'] = '';
                $question['QUESTION_SUBMIT'] = true;
                continue;
            }


            $result = $this->getService('Question')->validate($questionId, &$question['AR_ANSWERS'], $testId);
            $temp = false;
            foreach($question['AR_ANSWERS'] as $answer){
                if($answer['ANSWER_FEED'] != ""){
                    $temp = true;
                    break;
                }
            }
            if($result[0] === -1 || $temp == false){
                $question['QUESTION_SUBMIT'] = true;
            }else{

                $question['QUESTION_FEED'] = implode('<br/>', $this->getService('TestFeedback')->getFeedbackForQuestion($result[1], $questionId, $testId));
            }

            if($question['QUESTION_FEED'] == null){
                $question['QUESTION_FEED'] = '';
            }

        }
        echo json_encode($questionFeedback);
        exit;
    }

}