<?php

class HM_DisabledPeople_Resume_ResumeTable extends HM_Db_Table
{
    protected $_name = "disabled_people_resume";
    protected $_primary = '';
    
    public function getDefaultOrder()
    {
        return array('disabled_people_resume.resume_id ASC');
    }
}