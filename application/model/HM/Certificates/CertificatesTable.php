<?php
class HM_Certificates_CertificatesTable extends HM_Db_Table
{
	protected $_name = "certificates";
    protected $_primary = 'certificate_id';
    protected $_sequence = "S_32_1_CERTIFICATES";
     
	protected $_referenceMap = array(
        'User' => array(
            'columns'       => 'user_id',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns'    => 'MID',
            'propertyName'  => 'student'
        ),
        'Subject' => array(
            'columns'       => 'subject_id',
            'refTableClass' => 'HM_Subject_SubjectTable',
            'refColumns'    => 'subid',
            'propertyName'  => 'courses')
	);
	
	
	public function getDefaultOrder()
    {
        return array('certificates.certificate_id');
    }
}