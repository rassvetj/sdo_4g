<?php
class Report_StudentsController extends HM_Controller_Action_Crud
{
    public function init()
    {
        parent::init();
       
        $this->getService('Unmanaged')->setHeader(_('Отчет по студентам'));
    }
	
	
    public function indexAction()
    {
        $this->_reportService = $this->getService('Report');
		
		$this->view->headLink()->appendStylesheet($config->url->base.'/css/rgsu_style.css');
		
		$userIDs  = false;	
		if($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_SUPERVISOR)) { //--наблюдатель
			//--закрепленные группы.
			$userIDs = $this->getService('Orgstructure')->getAllSubUnit(); //--подчиненные студенты.			
		}
		
		$groupList = $this->_reportService->getGroupList($userIDs);
		
			
		$groupList = array('-1' => _('Выберите группу')) + $groupList;
		
		$this->view->groups = $groupList;
		
		$this->view->content = _('Выберите группу из списка');
		
    }
	
	public function getAction()
    {	
		$this->getHelper('viewRenderer')->setNoRender();
		
		$request = $this->getRequest();

        if ($request->isPost() || $request->isGet()) {
			
			$content = array();
			$group_id = (intval($request->getParam('group_id')) >= 0 ) ? $request->getParam('group_id') : false;	
			$group_name = $request->getParam('group_name');
			
			if(!$group_id){				
				$content['message'] = 'Выберите группу из списка';
				$content['data'] = '';				
			} else {	
				$report = $this->getService('Report')->getReportGroup($group_id);
				
				
				if($report){
					$cols = array();
					$rows = array();
					$rowsUser = array();
					foreach($report as $i){					
						if($i['semester']%2 || empty($i['semester'])){
							$cols[1][$i['sessionID']] = $i['sessionName'];							
						} else {
							$cols[2][$i['sessionID']] = $i['sessionName'];							
						}
						$rows[$i['userID']][$i['sessionID']] = $i['mark'];
						$rowsUser[$i['userID']] = $i['fio'];						
					}
				}
				
				//$userService = $this->getService('User');
				
				//try {				
					//$gridId = 'grid';
					//$content_grid = $grid->deploy();										
				
				//} catch (Exception $e) {
					//echo 'Выброшено исключение: ',  $e->getMessage(), "\n";					
				//}										
			}
			
			
			
			$this->view->group_name = $group_name;
			$this->view->cols = $cols;
			$this->view->rows = $rows;
			$this->view->rowsUser = $rowsUser;
		
			echo $this->view->render('students/ajax.tpl');			
		}		
	}
	

  




}