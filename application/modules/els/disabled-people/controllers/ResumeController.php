<?php
class DisabledPeople_ResumeController extends HM_Controller_Action
{
    public function init(){
		/*
		$user = $this->getService('User')->getCurrentUser();
		$recruitInfo = $this->getService('Recruits')->getRecruitInfo($user->mid_external);
		if(empty($recruitInfo)){
			$this->_helper->getHelper('FlashMessenger')->addMessage(array('message' => 'Нет доступной информации для подтверждения.', 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
			$this->_redirect('/');
		}
		*/
		parent::init();			
	}
	
	public function indexAction()
    {			
		$this->getService('Unmanaged')->setHeader(_('Кабинет ОВЗ: резюме'));
		$serviceResume = $this->getService('DisabledPeopleResume');
		$serviceUser = $this->getService('User');
		$user = $serviceUser->getCurrentUser();
		$res = $serviceResume->getOne($serviceResume->fetchAll($serviceResume->quoteInto('mid_external = ?', $user->mid_external)));
		if($res){			
			$userImg = $serviceUser->getImageSrc($user->MID);
			$userImg = ($userImg)? '/' . Zend_Registry::get('config')->src->upload->photo . $userImg : '/images/content-modules/nophoto-small.gif';
			$this->view->userImg = $userImg;
			
			$this->view->fio = $user->LastName.' '.$user->FirstName.' '.$user->Patronymic;
			
			$this->view->resume = $res;			
		} else {
			$this->view->isEmpty = true;
		}	
    }
	
	public function editAction()
    {			
		$this->getService('Unmanaged')->setHeader(_('Кабинет ОВЗ: изменить резюме'));
		$form = new HM_Form_Resume();
		$this->view->form = $form;		
    }
	
	
	public function saveAction()
    {			
		$this->_helper->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
		$serviceResume = $this->getService('DisabledPeopleResume');
		$user = $this->getService('User')->getCurrentUser();
		
		$data = array(		
			'type_id' 				=> $this->_getParam('type_id', ''),
			'job_vacancy' 			=> $this->_getParam('job_vacancy', ''),
			'income_level' 			=> $this->_getParam('income_level', ''),
			'phone' 				=> $this->_getParam('phone', ''),
			'email' 				=> $this->_getParam('email', ''),
			'competence' 			=> $this->_getParam('competence', ''),
			'result_competition' 	=> $this->_getParam('result_competition', ''),
			'institution' 			=> $this->_getParam('institution', ''),			
			'faculty' 				=> $this->_getParam('faculty', ''),
			'specialty' 			=> $this->_getParam('specialty', ''),
			'form_study' 			=> $this->_getParam('form_study', ''),
			'position' 				=> $this->_getParam('position', ''),
			'organization' 			=> $this->_getParam('organization', ''),
			'job_function' 			=> $this->_getParam('job_function', ''),
			'achievements' 			=> $this->_getParam('achievements', ''),
			'city' 					=> $this->_getParam('city', ''),
			'metro'					=> $this->_getParam('metro', ''),			
			'english' 				=> $this->_getParam('english', ''),
			'computer_skills' 		=> $this->_getParam('computer_skills', ''),
			'about' 				=> $this->_getParam('about', ''),
			'recommendations' 		=> $this->_getParam('recommendations', ''),
			'date_updated' 			=> new Zend_Db_Expr('NOW()'),		
		);
		
		$dt = DateTime::createFromFormat('d.m.Y', $this->_getParam('graduation_date', ''));
		if($dt){ $data['graduation_date'] = $dt->format('Y-m-d');  }
		
		$dt = DateTime::createFromFormat('d.m.Y', $this->_getParam('work_period_begin', ''));
		if($dt){ $data['work_period_begin'] = $dt->format('Y-m-d');  }
		
		$dt = DateTime::createFromFormat('d.m.Y', $this->_getParam('work_period_end', ''));
		if($dt){ $data['work_period_end'] = $dt->format('Y-m-d');  }
		
		$dt = DateTime::createFromFormat('d.m.Y', $this->_getParam('date_birth', ''));
		if($dt){ $data['date_birth'] = $dt->format('Y-m-d');  }
		
		try {
			$resume = $serviceResume->getOne($serviceResume->fetchAll($serviceResume->quoteInto('mid_external = ?', $user->mid_external)));
			if($resume->resume_id){
				$serviceResume->updateWhere($data, array('resume_id = ?' => $resume->resume_id));
			} else {
				$data['mid_external'] = $user->mid_external;
				$serviceResume->insert($data);
			}			
			$this->_flashMessenger->addMessage(_('Данные успешно сохранены.'));						
		} catch (Exception $e) {			
			$this->_flashMessenger->addMessage(array('message' => 'Не удалось сохранить результат.', 'type' => HM_Notification_NotificationModel::TYPE_ERROR));		
		}
		$this->_redirector = $this->_helper->getHelper('Redirector');           
		$this->_redirector->gotoSimple('index', 'resume', 'disabled-people');
    }

	
	public function downloadAction(){
		$this->_helper->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
		
		$serviceResume = $this->getService('DisabledPeopleResume');
		$serviceUser = $this->getService('User');
		$user = $serviceUser->getCurrentUser();
		$res = $serviceResume->getOne($serviceResume->fetchAll($serviceResume->quoteInto('mid_external = ?', $user->mid_external)));
		if($res){
			$dt 		= DateTime::createFromFormat('Y-m-d', $res->graduation_date);
			$dt_b 		= DateTime::createFromFormat('Y-m-d', $res->work_period_begin);
			$dt_e 		= DateTime::createFromFormat('Y-m-d', $res->work_period_end);
			$dt_birth 	= DateTime::createFromFormat('Y-m-d', $res->date_birth);
			
			$userImg = $serviceUser->getImageSrc($user->MID);
			$userImg = ($userImg)? '/' . Zend_Registry::get('config')->src->upload->photo . $userImg : '/images/content-modules/nophoto-small.gif';
		
			$data = array(
				'userImg' 				=> $userImg,
				'job_vacancy' 			=> $res->job_vacancy,
				'fio' 					=> $user->LastName.' '.$user->FirstName.' '.$user->Patronymic,
				'income_level'  		=> number_format($res->income_level, 0, ',', ' '),
				'phone' 				=> $res->phone,
				'email' 				=> $res->email,
				'competence' 			=> $res->competence,
				'result_competition' 	=> $res->result_competition,
				'institution' 			=> $res->institution,
				'graduation_date'		=> ($dt)?($dt->format('d.m.Y')):(''),
				'faculty' 				=> $res->faculty,
				'specialty' 			=> $res->specialty,
				'form_study' 			=> $res->form_study,
				'work_period_begin'		=> ($dt_b)?($dt_b->format('d.m.Y')):(''),
				'work_period_end'		=> ($dt_e)?($dt_e->format('d.m.Y')):(''),
				'position' 				=> $res->position,
				'organization' 			=> $res->organization,
				'job_function' 			=> $res->job_function,
				'achievements' 			=> $res->achievements,
				'city' 					=> $res->city,
				'metro' 				=> $res->metro,
				'date_birth'			=> ($dt_birth)?($dt_birth->format('d.m.Y')):(''),
				'english' 				=> $res->english,
				'computer_skills' 		=> $res->computer_skills,
				'about' 				=> $res->about,
				'recommendations' 		=> $res->recommendations,
			);
			$pathToPDF = $serviceResume->createPDF($type, $data);
			
			$this->getResponse()->setHeader('Content-Type', 'application/pdf');
			$this->getResponse()->setHeader('Content-Disposition', "attachment;filename='resume.pdf'");
			readfile($pathToPDF);			
			unlink($pathToPDF);
		}
	}	
   
}