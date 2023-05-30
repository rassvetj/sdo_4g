<?php
class HM_Debtors_DebtorsTable extends HM_Db_Table
{
    //protected $_name = "learning_subjects";
    protected $_name = "Students";
    //protected $_primary = "learning_subject_id";
    protected $_primary = "SID";
    protected $_sequence = "";

	//protected $_dependentTables = array(
      //  "HM_User_UserTable",      
    //);
	
    protected $_referenceMap = array(
      //  'User' => array(
        //    'columns' => 'MID',
          //  'refTableClass' => 'HM_User_UserTable',
//            'refColumns' => 'MID',
  //          'onDelete' => self::CASCADE,
    //        'propertyName' => 'students'),
    );

    public function getDefaultOrder()
    {
        return array('Students.SID ASC');
    }
}