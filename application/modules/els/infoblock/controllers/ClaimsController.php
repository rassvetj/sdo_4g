<?php
require_once APPLICATION_PATH . '/views/infoblocks/ClaimsBlock.php'; // chart model wanted?

class Infoblock_ClaimsController extends HM_Controller_Action_Chart
{
	protected $session;

	public function getDataAction()
	{
        $this->session = new Zend_Session_Namespace('infoblock_claims');
		if ($period = $this->_getParam('period')) {
			$this->session->period = $period;
		}
		$this->view->selectedPeriod = $this->session->selectedPeriod;

		$service = $this->getService('Claimant');
        $select = $service->getSelect();
        $period = HM_Date::getCurrendPeriod($this->session->period);
		$data = array();

        switch ($this->session->period) {

        	case HM_Date::PERIOD_WEEK_CURRENT:

		        $select->from(array('c' => 'claimants'), array(
			                'date_period' 		=> new Zend_Db_Expr("DAY(c.created)"),
			                'date_period_month' => new Zend_Db_Expr("MONTH(c.created)"),
			                'date_period_year' 	=> new Zend_Db_Expr("YEAR(c.created)"),
			                'value'				=> new Zend_Db_Expr('COUNT(c.SID)'),
		            	)
		            )
	            	->group(new Zend_Db_Expr("YEAR(c.created), MONTH(c.created), DAY(c.created)"))
	            	->order(new Zend_Db_Expr("YEAR(c.created), MONTH(c.created), DAY(c.created)"));

	           	$iterator = clone $period['begin'];
	           	while ($iterator->getTimestamp() < $period['end']->getTimestamp()) {
	           		$data[$iterator->get('dd.MM')] = $iterator->get('d');
	           		$iterator->add(1, HM_Date::DAY);
	           	}
        		break;

        	case HM_Date::PERIOD_MONTH_CURRENT:

		        $select->from(array('c' => 'claimants'), array(
			                'date_period' 		=> new Zend_Db_Expr("DAY(c.created)"),
			                'date_period_month' => new Zend_Db_Expr("MONTH(c.created)"),
			                'date_period_year' 	=> new Zend_Db_Expr("YEAR(c.created)"),
			                'value'				=> new Zend_Db_Expr('COUNT(c.SID)'),
		            	)
		            )
	            	->group(new Zend_Db_Expr("YEAR(c.created), MONTH(c.created), DAY(c.created)"))
	            	->order(new Zend_Db_Expr("YEAR(c.created), MONTH(c.created), DAY(c.created)"));

	           	$iterator = clone $period['begin'];
	           	while ($iterator->getTimestamp() < $period['end']->getTimestamp()) {
	           		$data[$iterator->get('d')] = $iterator->get('d');
	           		$iterator->add(1, HM_Date::DAY);
	           	}
        		break;

        	case HM_Date::PERIOD_YEAR_CURRENT:

		        $select->from(array('c' => 'claimants'), array(
			                'date_period' 	=> new Zend_Db_Expr("MONTH(c.created)"),
			                'value'			=> new Zend_Db_Expr('COUNT(c.SID)'),
		            	)
		            )
	            	->group(new Zend_Db_Expr("MONTH(c.created)"))
	            	->order(new Zend_Db_Expr("MONTH(c.created)"));

	           	$iterator = clone $period['begin'];
	           	while ($iterator->getTimestamp() < $period['end']->getTimestamp()) {
	           		$data[$iterator->getStandalone('MMMMM')] = $iterator->get('M');
	           		$iterator->add(1, HM_Date::MONTH);
	           	}
        		break;

        	default:
        		break;
        }

		$begin = $period['begin']->toString('yyyy-MM-dd');
		$end = $period['end']->toString('yyyy-MM-dd');

        $select->where(new Zend_Db_Expr($service->quoteInto(array("c.created BETWEEN ? ", "AND ?"), array($begin, $end))));

	    // Область ответственности
        $options = $this->getService('Dean')->getResponsibilityOptions($this->getService('User')->getCurrentUserId());
        if($options['unlimited_subjects'] != 1
            && $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_DEAN)
           //&& $this->getService('User')->getCurrentUserRole() == HM_Role_RoleModelAbstract::ROLE_DEAN
        ){
            $select->joinInner(array('d' => 'deans'), 'd.subject_id = c.CID', array())
                   ->where('d.MID = ?', $this->getService('User')->getCurrentUserId());
        }

        $series = array_keys($data);
		$graphs = array_fill(0, count($data), 0);
        if ($rowset = $select->query()->fetchAll()) {
        	foreach ($rowset as $row) {
        		$key = array_search(array_search($row['date_period'], $data), $series);
        		if (isset($graphs[$key])) {
        			$graphs[$key] = $row['value'];
        		}
        	}
        }
		$this->view->series = $series;
		$this->view->graphs = $graphs;

		$this->view->meta = array(
			'total'		=> array_sum($graphs),
		);
	}
}