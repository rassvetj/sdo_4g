<?php

class HM_StudentCertificate_Statement_StatementTable extends HM_Db_Table
{
    protected $_name 	= "student_statements";
    protected $_primary = "statement_id";
    

    public function getDefaultOrder()
    {
        return array('student_statements.statement_id ASC');
    }
}