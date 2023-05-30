<?php
class HM_Scale_ScaleTable extends HM_Db_Table
{
	protected $_name    = "scales";
	protected $_primary = "scale_id";
	protected $_sequence = 'S_100_1_SCALES';

    protected $_referenceMap = array(
        'ScaleValue' => array(
            'columns'       => 'scale_id',
            'refTableClass' => 'HM_Scale_Value_ValueTable',
            'refColumns'    => 'scale_id',
            'propertyName'  => 'scaleValues'
        ),
        'Subject' => array(
            'columns'       => 'scale_id',
            'refTableClass' => 'HM_Subject_SubjectTable',
            'refColumns'    => 'scale_id',
            'propertyName'  => 'subject'
        ),
    );
}