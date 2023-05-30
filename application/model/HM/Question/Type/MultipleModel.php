<?php
class HM_Question_Type_MultipleModel extends HM_Question_QuestionModel
{
    public function getAnswerData()
    {
        $adata = explode(self::SEPARATOR, $this->adata);
        return $adata;
    }


    public function couldBeExportedToTxt()
    {
        return true;
    }


}