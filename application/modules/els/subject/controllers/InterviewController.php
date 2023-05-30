<?php
class Subject_InterviewController extends HM_Controller_Action_Subject
{
    const COURSE_TYPE_LOCAL = 'Учебный курс';
	
	
	/**
	 * Список всех доступных стдудентов курса.
	 * Доступ только для тьютора
	*/
	public function listAction()
    {
		$subject_id = intval($this->_getParam('subject_id', 0));
		
        $serviceAcl  = $this->getService('Acl');
		$serviceUser = $this->getService('User');
        
        $currentUserRole = $serviceUser->getCurrentUserRole();
        $currentUserId 	 = $serviceUser->getCurrentUserId();
        $currentUser_InheritsFrom_Tutor 	= $serviceAcl->inheritsRole($currentUserRole, HM_Role_RoleModelAbstract::ROLE_TUTOR);
        
		$request = Zend_Controller_Front::getInstance()->getRequest();
		$this->_currentLang = $request->getCookie(HM_User_UserService::COOKIE_NAME_LANG);
		
		if($serviceAcl->inheritsRole($currentUserRole, HM_Role_RoleModelAbstract::ROLE_STUDENT)){
			$this->_redirector->gotoSimple('index', 'interview', 'subject', array('subject_id' => $subject_id));			
		}
		
		$cols = array(
            'MID' => 'p.MID',
            'SID' => 'st.SID',
            'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
            'group_id' => 'sg.group_id',
            'group_name' => 'sg.name',                        
        );
	
		$select = $this->getService('User')->getSelect();
        $select->from(	   array('p' => 'People'), $cols);        
        $select->joinLeft( array('sgu' => 'study_groups_users'), 'sgu.user_id = p.MID');
        $select->joinLeft( array('sg' => 'study_groups'), 'sg.group_id = sgu.group_id');
        $select->joinInner(array('st' => 'Students'), 'st.MID = p.MID AND st.CID = '.$subject_id);
		
		# если тьютор		
		if ($currentUser_InheritsFrom_Tutor) {
			$studentIDs = $this->getService('Subject')->getAvailableStudents($currentUserId, $subject_id);
			if($studentIDs !== false){
				if(is_array($studentIDs)){
					if(count($studentIDs)){
						$select->where($serviceUser->quoteInto('p.MID IN (?)', $studentIDs));
					} else {
						# нет доступных студентов.
						$select->where('1=0');
					}
				}
			}		
		}
        $select->order('group_name', 'fio');
        $result = $select->query()->fetchAll();
		
        $groups = array('show_all' => array(
            'name' => _('Показать всех'),
            'new_count' => 0
        )); //список групп $key = id, $val['name'] = название, $val['new_count'] = количество новых сообщений
	   
	    $users = array();
        foreach ($result as $key => $value) {
            $mid = $value['MID'];
            $sid = $value['SID'];

            $result[$key]['group_id'] = intval($result[$key]['group_id']);
            $group_id = 0;
        
			$group_id = $result[$key]['group_id'];
			if($this->_currentLang == 'eng')
				$value['fio'] = $this->translit($value['fio']);
			if (empty($groups[$group_id])) {
				$groups[$group_id] = array(
					'name' => $value['group_name'],
					'new_count' => 0
				);
				if ($group_id == 0) {
					$groups[$group_id]['name'] = _('Без группы');
				}
			}
			if ($value['is_new']) {
				$groups[$group_id]['new_count']++;
				$groups['show_all']['new_count']++;
			}
            
            //ссылка на переписку с пользователем
            $url = array(
                'module' => 'subject',
                'controller' => 'interview',
                'action' => 'index',
                'subject_id' => $subject_id,
                'user_id' => $mid,
            );

           
			$result[$key]['card'] = $this->view->cardLink(
				$this->view->url(
					array(
						'module' => 'user',
						'controller' => 'list',
						'action' => 'view',
						'user_id' => $mid
					)
				),
				_('Карточка пользователя')
			);
            

            $result[$key]['url'] = $this->view->url($url);

            if (empty($users[$sid])) {
                $users[$sid] = $result[$key];
                $users[$sid]['groups'] = array();
            }
            $users[$sid]['groups'][] = $group_id;
        }
		
		$tt = array();
		foreach($users as $key => $u){
			$tt[$u['fio'].'~'.$key] = $u;		
		}
		$users = $tt;		
		ksort($users);	
        $this->view->users = $users;

        $this->view->groups = $groups;
		
		$serviceSubject = $this->getService('Subject');
		$subject 		= $serviceSubject->getOne($serviceSubject->find($subject_id));
		$header_name 	= $subject->name.' <span class="header-bc">['._('сервисы – задать вопрос в курсе').']</span>';
		$this->view->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/rgsu_style.css');
		$this->view->setHeader(_($header_name));		
	   
    }

	/**
	 * Переписка выбранного студента и тьютора.
	 * Для студента параметр user_id не нужен.
	 * Для тьютора - обязательный параметр
	*/
    public function indexAction()
    {
        
		$subject_id = (int) $this->_getParam('subject_id', false);		
        $user_id	= (int) $this->_getParam('user_id', false);
		$current_id = (int) $this->getService('User')->getCurrentUserId();
		
		$serviceDialog = $this->getService('SubjectDialog');
		$serviceSubject = $this->getService('Subject');
		$serviceAcl = $this->getService('Acl');
		
		$currentUserRole = $this->getService('User')->getCurrentUserRole();
		
		$isForbidden = false;
		if($serviceAcl->inheritsRole($currentUserRole, array(HM_Role_RoleModelAbstract::ROLE_TUTOR ))){
			if(!$serviceSubject->isTutor($subject_id, $current_id)){				
				$isForbidden = true;
			} else {
				$studentIDs = $serviceSubject->getAvailableStudents($current_id, $subject_id);				
				if($studentIDs && !in_array($user_id, $studentIDs)){					
					$isForbidden = true;
				}
			}
		} elseif($serviceAcl->inheritsRole($currentUserRole, array(HM_Role_RoleModelAbstract::ROLE_TEACHER ))){
			if(!$serviceSubject->isTeacher($subject_id, $current_id)){ $isForbidden = true; }	
		} elseif($serviceAcl->inheritsRole($currentUserRole, array(HM_Role_RoleModelAbstract::ROLE_ENDUSER ))){
			if(		!$serviceSubject->isStudent($subject_id, $current_id)	&&	 !$serviceSubject->isGraduated($subject_id, $current_id)	){	$isForbidden = true; }	
		}
		
			
		if($isForbidden){
			$this->_helper->getHelper('FlashMessenger')->addMessage(
				array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
				'message' => _('Вы не имеете права просматривать эту страницу'))
			);
			$this->_redirect('/');
		}
		
		$subject 		= $serviceSubject->getOne($serviceSubject->find($subject_id));
		$header_name 	= $subject->name.' <span class="header-bc">[сервисы – задать вопрос в курсе]</span>';
		$this->view->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/rgsu_style.css');
		$this->view->setHeader(_($header_name));
		
				
		# получаем цепочку сообщений				
		$condition = array();
		$condition['CID = ?'] = $subject_id;
		if ($current_id == $user_id) {
			/**
			 * исключаем попадание в выборку своих ответов из других заданий
			 * оставляем только ответы самому себе
			 */
			$condition[] = '(user_id = ' . $user_id .' OR to_whom = ' . $user_id . ') AND (user_id != ' . $current_id . ' OR user_id = to_whom OR to_whom = 0)';
		} else {
			if($serviceAcl->inheritsRole($currentUserRole, array(HM_Role_RoleModelAbstract::ROLE_ENDUSER ))){
				$condition[] = '(user_id = ' . $current_id .' OR to_whom = ' . $current_id . ')';
			} else {
				$condition[] = '(user_id = ' . $user_id .' OR to_whom = ' . $user_id . ')';
			}
		}		
		
		$messages = $serviceDialog->fetchAllHybrid('User', 'Files', 'File', $condition,  array('interview_id'));
		
		$this->view->messages = $messages;
		
		$interviewForm = new HM_Form_Interview();
		$this->view->form = $interviewForm;
       
    }
	
	/**
	 * Сохраняет вопрос
	*/
	public function createAction(){		
        $current_user_id = $this->getService('User')->getCurrentUserId();
        $user_id 		 = (int) $this->_getParam('user_id', 0);
        $subject_id      = (int) $this->_getParam('subject_id', 0);
		$serviceDialog	 = $this->getService('SubjectDialog');
		
		$request = $this->getRequest();
		$form = new HM_Form_Interview();
		if ($request->isPost()){
			if ($form->isValid($request->getParams())){
				$date = new HM_Date();				
				$messageUserId = ($user_id > 0) ? ($user_id) : ($current_user_id);
				
				$message = $serviceDialog->getOne($serviceDialog->fetchAll($serviceDialog->quoteInto(array('CID = ?', ' AND (user_id = ? OR ', 'to_whom = ? )'), array($subject_id, $messageUserId, $messageUserId))));
				$interview_hash = (!empty($message->interview_hash)) ? ($message->interview_hash) :  (mt_rand(999999, 999999999));
				
				$interview = $serviceDialog->insert(
					array(
						'user_id' 		=> $current_user_id,
						'to_whom' 		=> $user_id,						
						'CID'	 		=> $subject_id,						
						'title'			=> '',						
						'type' 			=> $form->getValue('type'),
						'message' 		=> $form->getValue('message'),
						'date' 			=> $date->toString(),
						'interview_hash'=> $interview_hash
					)
				);

				if($interview->interview_id){
					$this->_flashMessenger->addMessage(_('Сообщение успешно добавлено.'));   					
					if($form->files->isUploaded() && $form->files->receive() && $form->files->isReceived()){
						$files = $form->files->getFileName();
						if(count($files) > 1){
							foreach($files as $file){								
								$fileInfo = pathinfo($file);
								$file = $this->getService('Files')->addFile($file, $fileInfo['basename']);
								$this->getService('SubjectDialogFile')->insert(array('interview_id' => $interview->interview_id, 'file_id' => $file->file_id));
							}
						}else{
							$fileInfo = pathinfo($files);
							$file = $this->getService('Files')->addFile($files, $fileInfo['basename']);
							$this->getService('SubjectDialogFile')->insert(array('interview_id' => $interview->interview_id, 'file_id' => $file->file_id));
						}
					}
					
					try {
						# уведомления в ЛК и на почту: согласно настройкам уведомлений в ЛК						
						$this->getService('EventDispatcher')->notify(
							new sfEvent(
								$serviceDialog,
								get_class($serviceDialog).'::esPushTrigger',
								array('message' => $interview)
							)
						);
						
					} catch (Exception $e) {
						echo 'Выброшено исключение: ',  $e->getMessage(), "\n";
					}					
				} else {
					$this->_flashMessenger->addMessage(
						array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
							  'message' => _('Не удалось сохранить сообщение.'))
					);
				}				
				
			} else {			
			}
		} else {			
		}
		
		if ($this->_getParam('referer_redirect')) { $this->_redirector->gotoUrl($_SERVER['HTTP_REFERER']); }
		
		$this->_redirector->gotoSimple('list', 'interview', 'subject', array('subject_id' => $this->_getParam('subject_id', 0)));
	}
		
		public function translit($str='') {
		
		$cyrForTranslit = array('а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п',
						   'р','с','т','у','ф','х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я',
						   'А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П',
						   'Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я'); 
		$latForTranslit = array('a','b','v','g','d','e','yo','zh','z','i','y','k','l','m','n','o','p',
							'r','s','t','u','f','h','ts','ch','sh','sch','','y','','ae','yu','ya',
							'A','B','V','G','D','E','Yo','Zh','Z','I','Y','K','L','M','N','O','P',
							'R','S','T','U','F','H','Ts','Ch','Sh','Sch','','Y','','Ae','Yu','Ya'); 
							
		return str_replace($cyrForTranslit, $latForTranslit, $str);
	} 

	/**
	 * выводит сообщение по курсу в es notice.
	*/	
    /*
	public function esmessageAction()
    {
		$subjectId = (int) $this->_getParam('subject_id', 0);		
		
		$subject = $this->getService('Subject')->getOne($this->getService('Subject')->find($subjectId));
		$this->view->setHeader(_('Сообщение в курсе: ').$subject->name);
		
		#$message = $this->getService('WorkloadSheet')->getMotivationMessage($subjectId);
		if(!$message){
			$message = _('У Вас нет сообщений в курсе.');
		}			
		$this->view->message = 'Текст сообщения';
	}
	*/	
}
