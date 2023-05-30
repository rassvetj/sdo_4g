<?php class HM_Test_Feedback_Answer_AnswerModel extends HM_Test_Feedback_FeedbackModel
{
    public function getService()
    {
        return $this->getService('TestFeedbackQuestion');
    }        

}
?>