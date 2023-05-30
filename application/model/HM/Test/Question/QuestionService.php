<?php
class HM_Test_Question_QuestionService extends HM_Service_Abstract
{
    public function processTest($test)
    {
        if ($test) {
            $this->deleteBy(
                $this->quoteInto('test_id = ?', $test->test_id)
            );
            if (strlen($test->data)) {
                $questions = explode(HM_Test_Abstract_AbstractModel::QUESTION_SEPARATOR, $test->data);
                if (count($questions)) {
                    foreach($questions as $questionId) {
                        $this->insert(array(
                            'subject_id' => $test->subject_id,
                            'test_id' => $test->test_id,
                            'kod' => $questionId
                        ));
                    }
                }
            }
        }
    }
}