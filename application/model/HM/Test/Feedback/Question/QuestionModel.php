<?php class HM_Test_Feedback_Question_QuestionModel extends HM_Test_Feedback_FeedbackModel
{

    public function getService()
    {
        return $this->getService('TestFeedbackQuestion');
    }

}
?>