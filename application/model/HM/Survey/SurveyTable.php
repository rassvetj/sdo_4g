<?php
class HM_Survey_SurveyTable extends HM_Db_Table
{
    protected $_name = "survey_result";
    protected $_primary = "survey_id";
    protected $_sequence = "";

    protected $_referenceMap = array(
        
    );

    public function getDefaultOrder()
    {
        return array('survey_result.survey_id ASC');
    }
}