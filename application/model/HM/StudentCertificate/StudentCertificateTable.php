<?php
class HM_StudentCertificate_StudentCertificateTable extends HM_Db_Table
{
	protected $_name = "CertStud";
    protected $_primary = "CertID";
    protected $_sequence = "";

    protected $_referenceMap = array(
        
    );

    public function getDefaultOrder()
    {
        return array('CertStud.CertID ASC');
    }
}