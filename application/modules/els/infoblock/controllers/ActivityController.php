<?php
require_once APPLICATION_PATH . '/views/infoblocks/ActivityBlock.php'; // chart model wanted?

class Infoblock_ActivityController extends HM_Controller_Action_Chart
{
	protected $session;

	public function init()
	{
		parent::init();
		$this->_helper->ContextSwitch()->addActionContext('get-users', 'json')->initContext();
	}

	public function getUsersAction()
	{
		$users = array(HM_View_Infoblock_ActivityBlock::USER_ALL => iconv(Zend_Registry::get('config')->charset, 'UTF-8', _('все')));
		$this->_getUsers($this->_getParam('group'), $users);
		$this->view->users = $users;
	}

	public function getDataAction()
	{
        $data = array();
		$this->session = new Zend_Session_Namespace('infoblock_activity');
        if (($key = $this->_getParam('key')) && ($value = $this->_getParam('value'))) {
			$this->session->$key = $value;
			$this->view->$key = $value;
			if ($key == 'group') {
			    $this->session->user = HM_View_Infoblock_ActivityBlock::USER_ALL;
			}
		}

        $service = Zend_Registry::get('serviceContainer')->getService('Session');

		switch ($this->session->type) {

			case HM_View_Infoblock_ActivityBlock::TYPE_TIMES:
				$stmt = new Zend_Db_Expr('(SUM(CASE WHEN ((UNIX_TIMESTAMP(s.stop) - UNIX_TIMESTAMP(s.start))/3600) > 24 THEN 24 ELSE ((UNIX_TIMESTAMP(s.stop) - UNIX_TIMESTAMP(s.start))/3600)END ))/COUNT(s.mid)');
				$legend = _('Время в системе, часы');
				break;

			case HM_View_Infoblock_ActivityBlock::TYPE_SESSIONS:
				$stmt = new Zend_Db_Expr('(COUNT(s.mid)/COUNT(DISTINCT s.mid))');
				$legend = _('Количество сессий');
				break;
			default:
				break;
		}

		$period = HM_Date::getCurrendPeriod($this->session->period);
		$begin = $period['begin']->toString('yyyy-MM-dd');
		$end = $period['end']->toString('yyyy-MM-dd');

        $select = $service->getSelect();
        $select->from(array('s' => 'sessions'), array(
	                'date_period' 		=> new Zend_Db_Expr("DAY(s.start)"),
	                'date_period_month' => new Zend_Db_Expr("MONTH(s.start)"),
	                'date_period_year' 	=> new Zend_Db_Expr("YEAR(s.start)"),
	                'value'				=> $stmt,
            	)
            )
	        ->where(new Zend_Db_Expr($service->quoteInto(array("s.start BETWEEN ? ", "AND ?"), array($begin, $end))))
        	->group(new Zend_Db_Expr("YEAR(s.start), MONTH(s.start), DAY(s.start)"))
        	->order(new Zend_Db_Expr("YEAR(s.start), MONTH(s.start), DAY(s.start)"));


		if ($this->session->group != HM_View_Infoblock_ActivityBlock::GROUP_ALL) {
			if ($this->session->user > 0) {
				$select->where('s.mid = ?', $this->session->user);
			} elseif ($this->session->user == HM_View_Infoblock_ActivityBlock::USER_ALL) {
				$users = array();
				$this->_getUsers($this->session->group, $users);
				if (count($users)) {
					$users = implode(',', array_keys($users));
					$select->where(new Zend_Db_Expr("s.mid IN({$users})"));
				} else {
					return array();
				}
			}
		}

       	$iterator = clone $period['begin'];
       	while ($iterator->getTimestamp() < $period['end']->getTimestamp()) {
       		$data[$iterator->get('dd')] = $iterator->get('d');
       		$iterator->add(1, HM_Date::DAY);
       	}
//exit($select->__toString());

       	$series = array_keys($data);
		$graphs = array_fill(0, count($data), 0);
        if ($rowset = $select->query()->fetchAll()) {
        	foreach ($rowset as $row) {
        		$key = array_search(array_search($row['date_period'], $data), $series);
        		if (isset($graphs[$key])) {
        		    $row['value'] = str_replace(',', '.', $row['value']); // oracle float fix
//        			$graphs[$key] = ceil((double)$row['value']);
        			$graphs[$key] = round((double)$row['value'], 2); // do not ceil
        		}
        	}
        }

		$this->view->legend = $legend;
		$this->view->series = $series;
		$this->view->graphs = $graphs;
	    $this->view->type = $this->session->type;
	    $this->view->single = ($this->session->user > 0) ? '-single' : '';
	}

	private function _getUsers($group, &$users)
	{
		if ($group > 0) {
			foreach ($this->getService('Subject')->getAssignedUsers($group) as $user) {
				$users[$user->MID] = iconv(Zend_Registry::get('config')->charset, 'UTF-8', $user->getName());
			}
		} elseif ($group == HM_View_Infoblock_ActivityBlock::GROUP_TEACHERS) {

	        $serviceUser = $this->getService('User');
	        $serviceDean = $this->getService('Dean');

	        $select = $serviceUser->getSelect();
            $select->from(
                array('t1' => 'People'),
                array('MID', 'name' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(t1.LastName, ' ') , t1.FirstName), ' '), t1.Patronymic)"))
            )->joinInner(
                array('t2' => 'Teachers'),
                't1.MID = t2.MID',
                array()
            );

    	    // Область ответственности
	        $options = $serviceDean->getResponsibilityOptions($serviceUser->getCurrentUserId());
	        if($options['unlimited_subjects'] != 1
                && $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_DEAN)
               //&& $serviceUser->getCurrentUserRole() == HM_Role_RoleModelAbstract::ROLE_DEAN
            ){
	            $select->joinInner(array('d2' => 'deans'), 'd2.subject_id = t2.CID', array())
	                   ->where('d2.MID = ?', $serviceUser->getCurrentUserId());
	        }

			if ($rowset = $select->query()->fetchAll()) {
				foreach ($rowset as $row) {
					$users[$row['MID']] = iconv(Zend_Registry::get('config')->charset, 'UTF-8', $row['name']);
				}
	        }
		}
	}
}