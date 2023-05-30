<?php
class StudentRecordbook_IndexController extends HM_Controller_Action
{
    public function init()
    {
        parent::init();
		
		
		$current_user		= $this->getService('User')->getCurrentUser();	
		$recordbook_number	= $this->getService('RecordCard')->getRecordbookNumber($current_user->mid_external);
		$isStudent			= empty($recordbook_number) ? false : true;		
		if(!$isStudent){
			
			$user_info		= $this->getService('UserInfo')->getByCode($current_user->mid_external);
			if(empty($user_info->fio_dative)){
				$this->_helper->getHelper('FlashMessenger')->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Файл не сформирован, т.к. не заполнен падеж ФИО')));
				$this->_helper->getHelper('FlashMessenger')->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Обратитесь в деканат').': dekanat@rgsu.net'));
				$this->_redirector->gotoSimple('index', 'index', 'services');
				die;
			}
			
			
			$this->_helper->getHelper('FlashMessenger')->addMessage(
				array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
					'message' => _('Зачетная книжка сейчас не доступна. Попробуйте завтра'))
			);			
			$this->_redirector->gotoSimple('index', 'index', 'services');
			die;	
		}
		
		
		# Формируем ТОЛЬКО pdf версию
		$this->_redirector->gotoSimple('pdf', 'export', 'student-recordbook');
		die;
		
    }
    
    
    public function indexAction()
    {
        try {
		
		if( ! $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_ENDUSER)   ){
			$this->_flashMessenger->addMessage(array(
				'type'    => HM_Notification_NotificationModel::TYPE_ERROR,
				'message' => _('Раздел доступен только для студентов')
			));
			$this->_redirect('/');
        }
		
		
		
		$current_user	= $this->getService('User')->getCurrentUser();
		$is_no_photo	= (basename($current_user->getPhoto()) == 'nophoto.gif') ? true : false;
		
		if($is_no_photo){
			$this->_helper->getHelper('FlashMessenger')->addMessage(
				array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
					'message' => _('Загрузите фотографию в профиле'))
			);			
			$this->_redirector->gotoSimple('index', 'edit', 'user');		
			die;
		}
		
		
		
		$config = Zend_Registry::get('config');
		$this->view->setHeader(_('Зачетная книжка'));
		
		$this->view->headLink()->appendStylesheet($config->url->base.'css/rgsu_style.css');
		
		$user = $this->getService('User')->getCurrentUser();
		
		if($user->Login == 'guest-rgsu'){
			$mid_external = '0';
		} else {
			$mid_external = $user->mid_external;
		}
				
		$this->_recService = $this->getService('RecordCard'); 
		$this->_studyCardService = $this->getService('StudyCard'); 
		
		
		$first_doc = $this->_recService->getFirstActualOrder($mid_external);
		$disciplins = $this->_studyCardService->getDisciplins($mid_external); //-_получаем список всех зачетов и экзаменов, что сдавал студент
		$data = array();
		if($disciplins){
			foreach($disciplins as $j){				
				$i = (object)$j;
				if($i->Vid == HM_StudyCard_StudyCardModel::VID_PRACTIC || $i->Vid == HM_StudyCard_StudyCardModel::VID_COURSE_WORK){
					$data[$i->Vid][] = $i;	
				} else {
					$data[$i->Vid][$i->Semester][$i->Type][] = $i;	
				}
				
			}
		}
				
		$this->view->currentPage = 1; //--порядковый номер страницы. Автоинкремент в шаблонах страниц.
		
		$this->view->institute_name = _('Федеральное государственное бюджетное образовательное учреждение Высшего профессионального образования. Российский государственный социальный университет');
		$this->view->fio = $user->LastName.' '.$user->FirstName.' '.$user->Patronymic; 
		$this->view->photo = $user->getPhoto();
		
		
		if(!empty($first_doc->DateTake)){
			$other_date = new Zend_Date($first_doc->DateTake);
			$dateTake = $other_date->get(Zend_Date::DATE_LONG);
		}
		$this->view->dateTake = $dateTake;
		$this->view->recordbook_number = $this->_recService->getRecordbookNumber($mid_external);
		$this->view->speciality 	= $first_doc->Speciality;		
		$this->view->specialization = trim($first_doc->Specialization);		
		$this->view->faculty 		= $first_doc->Faculty;
		$this->view->code 			= $first_doc->Code;
		$this->view->orderName 		= $this->getOrderName($first_doc->TypeOrder);
		
		//--Главная страница
		$this->view->p_main = $this->view->render('index/parts/_main.tpl'); 
		
		//--основные зачеты, экзамены
		if(count($data)){
			foreach($data[HM_StudyCard_StudyCardModel::VID_BASE] as $semestr => $i){								
				
				$this->view->semestr = $semestr; 
				
				$this->view->cource = ceil($semestr / 2); 
				$this->view->cource_name = $this->getCourceName($this->view->cource); //--Числительное курса (Первы, второй, третий, ...)				
				
				$this->view->year_begin = ($first_doc->year_begin + $this->view->cource) - 1;
				$this->view->year_end = $first_doc->year_begin + $this->view->cource;
				
				$this->view->p_data = $i;								
				$html_content[] = $this->view->render('index/parts/_base.tpl');
			}
		}
		
		//--формируем пустые страницы для полоноты зачетки.
		$emptyPageCount = (14 - count($data[HM_StudyCard_StudyCardModel::VID_BASE]));
		if(count($emptyPageCount)){
			for($i = 0; $i < $emptyPageCount; $i++){
				$this->view->semestr = $this->view->semestr + 1; 				
				$this->view->cource = ceil($this->view->semestr / 2);
				$this->view->cource_name = $this->getCourceName($this->view->cource);				
				$this->view->year_begin = ($first_doc->year_begin + $this->view->cource) - 1;
				$this->view->year_end = $first_doc->year_begin + $this->view->cource;				
				$this->view->p_data = false;
				$html_content[] = $this->view->render('index/parts/_base_empty.tpl');
			}
		}
		$this->view->p_base = $html_content;
	
		//--факультативные занятия. Пустая страница.
		$this->view->p_data = false;
		$this->view->p_facult = $this->view->render('index/parts/_facult.tpl'); 
		
		//--курсовые
		$this->view->p_data = false;
		if(count($data[HM_StudyCard_StudyCardModel::VID_COURSE_WORK])){			
			$this->view->p_data = $data[HM_StudyCard_StudyCardModel::VID_COURSE_WORK];				
		}
		$this->view->p_coursework = $this->view->render('index/parts/_coursework.tpl');
		
		//--практика
		$this->view->p_data = false;
		if(count($data[HM_StudyCard_StudyCardModel::VID_PRACTIC])){			
			$this->view->p_data = $data[HM_StudyCard_StudyCardModel::VID_PRACTIC];				
		}		
		$this->view->p_practice = $this->view->render('index/parts/_practice.tpl');
		
		//--научная работа
		$this->view->p_data = false;
		$this->view->p_scientific = $this->view->render('index/parts/_scientific.tpl');
		
		//--ГОСы + данные по ГИА
		$gia = $this->_recService->getGIA($mid_external);
		if($gia){		
			$this->view->giaName = $gia->Reason;			
			$other_date = new Zend_Date($gia->DateTake);
			$giaDateTake = $other_date->get(Zend_Date::DATE_LONG);
			$this->view->giaDateTake = $giaDateTake;
			$this->view->giaCode = $gia->Code;
		}				
		$this->view->gos = $this->_recService->getGOS($mid_external);
		$this->view->p_gos = $this->view->render('index/parts/_gos.tpl');
		
		
		//--выпускная работа и диплом.
		$this->view->grad = $this->_recService->getGraduationWork($mid_external);
		$this->view->p_grad_work = $this->view->render('index/parts/_grad_work.tpl'); //--Выпускная квал. работа + решение Гос. комиссии.
		
		
		} catch (Exception $e) {
			//echo $e->getMessage(), "\n";
		}		        
    }
	
	public function getCourceName($id){
		$c = array(
			1	=> _('ПЕРВЫЙ'),
			2	=> _('ВТОРОЙ'),
			3	=> _('ТРЕТИЙ'),
			4	=> _('ЧЕТВЕРТЫЙ'),
			5	=> _('ПЯТЫЙ'),
			6	=> _('ШЕСТОЙ'),	
			7	=> _('СЕДЬМОЙ'),	
		);			
		return $c[$id];        
	}
	
	public function getOrderName($name){
		$c = array(
			'Зачисление' => _('о зачислении'),
			'Стипендия' => _('о стипендии'),
			'Перевод' => _('о переводе'),
			'Внесение изменений в приказ' => _('о внесении изменений в приказ'),
			'Сессия (летняя и зимняя)' => _('о сессии'),
			'Разное' => _('о разном'),
			'ГИА' => _('о ГИА'),
			'Изменение ФИО' => _('об изменении ФИО'),
			'Отчисление' => _('об отчислении'),
			'Академический отпуск' => _('об академическом отпусе'),
			'Восстановление' => _('о восстановлении'),
			'Оплата' => _('об оплате'),			
			'Восстановление и перевод' => _('о восстановлении и переводе'),
		);			
		return $c[$name];        
	}
	
}