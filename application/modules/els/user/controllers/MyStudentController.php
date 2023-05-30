<?php
class User_MyStudentController extends HM_Controller_Action_User
{
	public function indexAction()
	{
		$this->view->headLink()->appendStylesheet($config->url->base.'css/rgsu_style.css');
		
		$serviceSubject = $this->getService('Subject');
		
		$current_user_id = $this->getService('User')->getCurrentUserId();
		
		$select = $serviceSubject->getSelect();
		$select->from(array('t' => 'Tutors'),
			array(
				'student_id'	=> 'p.MID',
				'fio'			=> new Zend_Db_Expr("GROUP_CONCAT(DISTINCT CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic))"),
				'skype'			=> 'p.Skype',
				'group_name'	=> 'sg.name',
			)
		);
		$select->join(array('s' 	=> 'Students'			), 's.CID 		= t.CID', 			array());
		$select->join(array('p' 	=> 'People'				), 'p.MID 		= s.MID', 			array());
		$select->join(array('sgc'	=> 'study_groups_custom'), 'sgc.user_id = s.MID', 			array());
		$select->join(array('sg'	=> 'study_groups'		), 'sg.group_id = sgc.group_id', 	array());
		
		
		$select->where('t.CID > 0');
		$select->where($serviceSubject->quoteInto('t.MID = ?', $current_user_id));		
		$select->where($serviceSubject->quoteInto('(p.blocked IS NULL OR p.blocked = ?)', HM_User_UserModel::STATUS_ACTIVE));
		
		$select->group(array('p.LastName', 'p.FirstName', 'p.Patronymic', 'p.Skype', 'sg.name', 'p.MID'));
		
		$select->order(array('sg.name', 'p.LastName', 'p.FirstName', 'p.Patronymic'));
		
		#echo $select->assemble();
		#die;
		
		$res = $select->query()->fetchAll();
		
		$data = array();
		if(!empty($res)){
			foreach($res as $student){
				$data[$student['group_name']][] = array(
					'student_id'	=> $student['student_id'],
					'fio'			=> $student['fio'],
					'skype' 		=> $student['skype'],
				);
			}
		}
		$this->view->students = $data;
	}



}

