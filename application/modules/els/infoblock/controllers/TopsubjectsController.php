<?php
class Infoblock_TopsubjectsController extends HM_Controller_Action_Chart
{
	public function getDataAction()
	{
		$service = $this->getService('Claimant');
        $select = $service->getSelect();
		$data = array();

        $period = HM_Date::getCurrendPeriod(HM_Date::PERIOD_YEAR_CURRENT);
		$begin = $period['begin']->toString('yyyy-MM-dd');
		$end = $period['end']->toString('yyyy-MM-dd');

        $select->from(array('c' => 'claimants'), array(
	                'value'		=> new Zend_Db_Expr('COUNT(c.SID)'),
            	)
            )
        	->join(array('s' => 'subjects'), 's.subid = c.CID', array(
	                'subject'	=> 's.name',
            	)
            )
            ->where(new Zend_Db_Expr($service->quoteInto(array("c.created BETWEEN ? ", "AND ?"), array($begin, $end))))
        	->group('s.name')
        	->order(new Zend_Db_Expr("COUNT(c.SID) DESC"));

	    // Область ответственности
        $options = $this->getService('Dean')->getResponsibilityOptions($this->getService('User')->getCurrentUserId());
        if($options['unlimited_subjects'] != 1
            && $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_DEAN)
           //&& $this->getService('User')->getCurrentUserRole() == HM_Role_RoleModelAbstract::ROLE_DEAN
        ){
            $select->joinInner(array('d' => 'deans'), 'd.subject_id = s.subid', array())
                   ->where('d.MID = ?', $this->getService('User')->getCurrentUserId());
        }

        if ($rowset = $select->query()->fetchAll()) {
        	foreach ($rowset as $row) {
        		$data[$row['subject']] = $row['value'];
        	}
        }
		$this->view->data = $data;
	}
}