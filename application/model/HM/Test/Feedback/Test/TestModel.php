<?php 
class HM_Test_Feedback_Test_TestModel extends HM_Test_Feedback_FeedbackModel
{

    public function getService()
    {
        return $this->getService('TestFeedbackTest');
    }
    

}