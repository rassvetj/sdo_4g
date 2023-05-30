<?php
class HM_CertificatesStudent_CertificatesStudentTable extends HM_Db_Table
{
	protected $_name = "certificates_student";
    protected $_primary = 'certificate_id';
    
	
	public function getDefaultOrder()
    {
        return array('certificates_student.certificate_id');
    }
}