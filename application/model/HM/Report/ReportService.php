<?php
class HM_Report_ReportService extends HM_Service_Abstract
{
    const CACHE_NAME = 'HM_Report_ReportService';
	protected $_reportBallTutors = array(); # отчет по выставленным оценкам тьютора.
	
	public function saveToCache()
    {
        return Zend_Registry::get('cache')->save(
            array(
                'reportBallTutors' => $this->_reportBallTutors,                                  
            ),
            self::CACHE_NAME
        );
    }

    public function clearCache()
    {
        return Zend_Registry::get('cache')->remove(self::CACHE_NAME);
    }

    public function restoreFromCache()
    {
        if ($actions = Zend_Registry::get('cache')->load(self::CACHE_NAME)) {
            $this->_reportBallTutors  = $actions['reportBallTutors'];            
            $this->_restoredFromCache = true;
            return true;
        }
        return false;
    }
	
	public function saveReportBallTutors($data)
    {     
		$this->_reportBallTutors = $data;
		$this->saveToCache();
    }
	
	public function insert($data)
    {
        $data['created'] = $this->getDateTime();
        $data['created_by'] = $this->getService('User')->getCurrentUserId();
        return parent::insert($data);
    }

    public function getTreeContent(HM_Report_Config $config)
    {
        $tree = array();   
        $role = $this->getService('User')->getCurrentUserRole(true); // объединяем младшие роли в enduser'а
        foreach($config->getDomains() as $domain => $domainTitle) {

            $reports = $this->fetchAllDependenceJoinInner(
                'ReportRole',
                $this->quoteInto(
                        array('domain = ?', /*' AND status = ?',*/ ' AND role = ?'),
                        array($domain, /*1,*/ $role)
                ),
                'name'
            );

            if (count($reports) || $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_RoleModelAbstract::ROLE_ADMIN, HM_Role_RoleModelAbstract::ROLE_DEAN, HM_Role_RoleModelAbstract::ROLE_MANAGER))) {

                $tree[] =
                    array(
                        'title' => _($domainTitle),
                        'key' => 0,
                        'isLazy' => false,
                        'isFolder' => true
                    );

            }

            if (count($reports)) {
                $tree[count($tree)-1]['expand'] = true;
                $subtree = array();
                foreach($reports as $report) {
                    $subtree[] = array(
                        'title' => _($report->name),
                        'key' => $report->report_id,
                        'isLazy' => false,
                        'isFolder' => false
                    );
                }
                $tree[] = $subtree;
            }
        }  
        return $tree;
    }
	
	
	/**
	 * return список групп, доступных преподавателю.
	 * организатор обучения  - все группы
	 * наблюдатель - только те, что в оргструкруте доступны ему.
	*/
	public function getGroupList($userIDs = false){
		if(empty($userIDs) && is_array($userIDs)){
			return false;
		}
		
		$select = $this->getSelect();
		$select->from(array('g' => 'study_groups'), 
			array(
				'group_id',
				'name'
			)
		);			
		$select->order('name');
		
		if($userIDs){
			$select->join(array('sgc' => 'study_groups_custom'),
				'sgc.group_id = g.group_id',
				array()
			);
			$select->where(
				$this->quoteInto(
					'sgc.user_id IN (?)',
					new Zend_Db_Expr(implode(',', $userIDs))			
                )
			);
		}
		
		$res = $select->query()->fetchAll();
		
		foreach($res as $i){
			$list[$i['group_id']] = $i['name'];
		}
		
		if(empty($list)){
			return false;
		}
		
		return $list;		
	}
	
	/**
	 * return оценки по сессиям одной группы студентов
	*/
	public function getReportGroup($groupID = false){
		if(!$groupID){
			return false;
		}
		
		$select = $this->getSelect();
		$select->from(array('subj' => 'subjects'), 
			array(				
				'sessionID' => 'subj.subid',
				'sessionName' => 'subj.name',				
				'mark' => 'cm.mark',
				'semester' => 'ls.semester',
				'userID' => 'p.MID',
				'fio' =>  new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
			)
		);	
		
		$select->joinLeft(array('ls' => 'learning_subjects'),
			'ls.id_external = subj.learning_subject_id_external',
			array()
		);
		
		$select->joinLeft(array('cm' => 'courses_marks'),
			'cm.cid = subj.subid',
			array()
		);
		
		$select->join(array('p' => 'People'),
			'cm.mid = p.MID',
			array()
		);
				
		$select->join(array('sgc' => 'study_groups_custom'),
			'sgc.user_id = p.MID',
			array()
		);
				
		$select->where('sgc.group_id =?', $groupID);
		$select->where('subj.base = ?', HM_Subject_SubjectModel::BASETYPE_SESSION);
		
		$res = $select->query()->fetchAll();
		
		if(!$res) {
			return false;
		}
		return $res;
		
	}
	
	/**
	 * список всех тьюторов в виде списка
	*/
	public function getTutorList(){
		$select = $this->getSelect();
		$select->from(array('p' => 'People'), array(
			'MID' => 'p.MID',
			'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
		));				
		$select->join(array('t' => 'Tutors'), 't.MID = p.MID', array());
		//$select->where('p.role_1c = 2');
		$select->where('t.MID > 0');
		$select->where('t.CID > 0');
		$select->group(array('p.MID', 'p.LastName', 'p.FirstName', 'p.Patronymic'));
		$select->order('p.LastName ASC');		
		
		$list = array('0' => 'Все');
		$res = $select->query()->fetchAll();
		if(!empty($res)){
			foreach($res as $t){
				$list[$t['MID']] = $t['fio'];				
			}
		}
		return $list;
	}
	
}