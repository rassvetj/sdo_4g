<?php

class HM_Subject_Mark_MarkTable extends HM_Db_Table
{
    protected $_name = "courses_marks";
    protected $_primary = array('cid', 'mid');
    //protected $_sequence = "S_58_1_SCHEDULEID";

    //protected $_dependentTables = array("HM_Role_StudentTable");

    protected $_referenceMap = array(
        'Subject' => array(
            'columns'       => 'cid',
            'refTableClass' => 'HM_Subject_SubjectTable',
            'refColumns'    => 'subid',
            'propertyName'  => 'subject' // имя свойства текущей модели куда будут записываться модели зависимости
        ),
        'User' => array(
            'columns'       => 'mid',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns'    => 'MID',
            'propertyName'  => 'user'
        )

    );
    public function getDefaultOrder()
    {
        return array('courses_marks.mid ASC');
    }
	
	public function getMarks($mid,$cid)
	{
		if (!empty($mid) and !empty($cid))
		{
			$result = $this->select()->where('mid = ?',$mid)->where('cid = ?',$cid);
			
			$row = $this->fetchRow($result);
			
			return $row->mark;
		}	
	}	
}