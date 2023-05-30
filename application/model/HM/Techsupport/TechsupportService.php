<?php
class HM_Techsupport_TechsupportService extends HM_Service_Abstract
{
    public function getIndexSelect() {
        $select = $this->getSelect();
        $select->from(
            array(
                'sr' => 'support_requests'
            ),
            array(
                //для совместимости с методом updateRole, псевдоним поля будет MID
                'MID'                => 'sr.user_id',
                'support_request_id' => 'sr.support_request_id',
                'user_name'          => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
                'roles'              => new Zend_Db_Expr('1'),
                'date_'              => 'sr.date_',
                'theme'              => 'sr.theme',
                'status'             => 'sr.status',
            )
        );
        $select->joinLeft(array('p' => 'People'), 'p.MID = sr.user_id', array());
        
        
        return $select;
    }
}