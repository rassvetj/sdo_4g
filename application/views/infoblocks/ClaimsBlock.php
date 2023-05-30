<?php
require_once APPLICATION_PATH . '/views/infoblocks/ScreenForm.php';

class HM_View_Infoblock_ClaimsBlock extends HM_View_Infoblock_ScreenForm
{
    protected $id = 'claims';
    protected $session;

    public $periodSet = array(
    	HM_Date::PERIOD_WEEK_CURRENT,
    	HM_Date::PERIOD_MONTH_CURRENT,
    	HM_Date::PERIOD_YEAR_CURRENT,
    );

    public function claimsBlock($title = null, $attribs = null, $options = null)
    {
        $service = Zend_Registry::get('serviceContainer')->getService('Claimant');

		$this->session = new Zend_Session_Namespace('infoblock_claims');
		if (!isset($this->session->period)) {
			$this->session->period = HM_Date::PERIOD_MONTH_CURRENT; //default
		}

		$period = HM_Date::getCurrendPeriod($this->session->period);
		$begin = $period['begin']->toString('yyyy-MM-dd');
		$end = $period['end']->toString('yyyy-MM-dd');

        $total = 0;
        $select = $service->getSelect();
		$select->from(array('c' => 'claimants'), array(
	                'value'		=> new Zend_Db_Expr('COUNT(c.SID)'),
            	)
            )
			->where(new Zend_Db_Expr($service->quoteInto(array("c.created BETWEEN ? ", "AND ?"), array($begin, $end))));

        // Область ответственности
        $options = Zend_Registry::get('serviceContainer')->getService('Dean')->getResponsibilityOptions(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserId());
        if($options['unlimited_subjects'] != 1
            && Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_DEAN)
        //   && Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole() == HM_Role_RoleModelAbstract::ROLE_DEAN
        ){
            $select->joinInner(array('d' => 'deans'), 'd.subject_id = c.CID', array())
                   ->where('d.MID = ?', Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserId());
        }

		if ($rowset = $select->query()->fetchAll()) {
        	$total = $rowset[0]['value'];
        }

        $undone = 0;
        $select = $service->getSelect();
		$select->from(array('c' => 'claimants'), array(
	                'value'		=> new Zend_Db_Expr('COUNT(c.SID)'),
            	)
            )
            ->joinInner(array('s' => 'subjects'), 'c.CID = s.subid', array())
        	->where('c.status = ?', HM_Role_ClaimantModel::STATUS_NEW)
        	->group('c.status');

        // Область ответственности
        $options = Zend_Registry::get('serviceContainer')->getService('Dean')->getResponsibilityOptions(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserId());
        if($options['unlimited_subjects'] != 1
            && Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_DEAN)
        //   && Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole() == HM_Role_RoleModelAbstract::ROLE_DEAN
        ){
            $select->joinInner(array('d' => 'deans'), 'd.subject_id = c.CID', array())
                   ->where('d.MID = ?', Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserId());
        }

        if ($rowset = $select->query()->fetchAll()) {
        	$undone = $rowset[0]['value'];
        }

        $this->view->periodSet = $this->periodSet;
        $this->view->period = $this->session->period;
        $this->view->total = $total;
        $this->view->undone = $undone;

    	$content = $this->view->render('claimsBlock.tpl');
        $this->view->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/infoblocks/claims/style.css');
        $this->view->headScript()->appendFile(Zend_Registry::get('config')->url->base.'js/infoblocks/claims/script.js');

        if ($title == null) return $content;
        return parent::screenForm($title, $content, $attribs);
    }
}