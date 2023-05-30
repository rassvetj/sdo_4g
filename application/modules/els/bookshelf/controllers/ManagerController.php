<?php
class Bookshelf_ManagerController extends HM_Controller_Action_Subject {
	
	private $_linkedSubjectstsCache = array();
	private $_authorCache           = NULL;
	private $_subjectId             = NULL;
	
	public function init()
	{	
		parent::init();
	}
	
	
	public function indexAction()
    {		
		$this->_subjectId = (int)$this->_getParam('subject_id');
		$gridId           = $this->_subjectId ? 'grid' . $this->_subjectId : 'grid';		
		$subject          = $this->getService('Subject')->getById($this->_subjectId);
		if($subject && !empty($subject->learning_subject_id_external)){
			$learningSubject = $this->getService('Learningsubjects')->getByCode($subject->learning_subject_id_external);
		}

		if($learningSubject){
			$publicSelect = $this->getService('Subject')->getSelect();
			$publicSelect->from(array('s'  => 'subjects'), array(
				'subjectId'      => 's.subid',
				'name'           => 's.name',
				'direction'      => 'ls.direction',
				'specialisation' => 'ls.specialisation',
				'name_plan'      => 'ls.name_plan',				
			));
			$publicSelect->join(array('ls' => 'learning_subjects'), 'ls.id_external = s.learning_subject_id_external',  array());			
			$publicSelect->where($this->quoteInto('s.subid!=?',$this->_subjectId));
			$publicSelect->where($this->quoteInto(array('ls.name=?'), array($learningSubject->name)));
			$publicSelect->order(array('s.name', 'ls.name_plan'));
			$res = $publicSelect->query()->fetchAll();
			if(!empty($res)){
				foreach($res as $item){
					$this->_linkedSubjectstsCache[$item['subjectId']] = $item;					
				}
			}
		}

		$fields = array(
			'bookshelf_id'  => 'b.bookshelf_id',
			'file_id'       => 'f.file_id',
			'group_name'    => 'g.name',
			'file_name'     => 'f.name',
			'date_created'  => 'b.date_created',
			'isPublic'      => 'b.isPublic',
			'publishedFrom' => 'b.subject_id',
			'author'        => 'b.author_id',
			'authorId'      => 'b.author_id',
			'subjectId'     => 'b.subject_id',
		);
		
		$filters = array(
			'bookshelf_id'  => null,
			'file_id'       => null,
			'group_name'    => null,
			'file_name'     => null,
			'date_created'  => array('render' => 'DateSmart'),
			'isPublic'      => array('values' => HM_Bookshelf_BookshelfModel::getPublicStatusList()),
			'publishedFrom' => null,
			'author'        => null,
			'authorId'      => null,
			'subjectId'     => null,
		);
		
		$select = $this->getService('Bookshelf')->getSelect();
		$select->from(array('b' => 'bookshelf'), $fields);
		$select->joinLeft(array('f' => 'files'),        'f.file_id  = b.file_id',  array());
		$select->joinLeft(array('g' => 'study_groups'), 'g.group_id = b.group_id', array());
		
		if(empty($this->_linkedSubjectstsCache)){
			$select->where('b.subject_id = ?', $this->_subjectId);	
		} else {
			$select->where($this->quoteInto(
				array('( b.subject_id=?', 'OR (b.subject_id IN (?)', ' AND b.isPublic=?) )'),
				array($this->_subjectId, array_keys($this->_linkedSubjectstsCache), HM_Bookshelf_BookshelfModel::PUBLIC_STATUS_YES)
			));
		}
		
		$select->order(array('group_name', 'file_name'));
		
		$grid = $this->getGrid(
            $select,
            array(
                'bookshelf_id'  => array('hidden' => true),
                'file_id'       => array('hidden' => true),
                'file_id'       => array('hidden' => true),
                'file_id'       => array('hidden' => true),
                'group_name'    => array('title' => _('Группа')),
				'file_name'     => array('title' => _('Файл')),
                'date_created'  => array('title' => _('Дата загрузки')),				
                'isPublic'      => array('title' => _('Публичный')),
                'publishedFrom' => array('title' => _('Опубликовано из')),
                'author'        => array('title' => _('Автор')),
                'authorId'      => array('hidden' => true),
                'subjectId'     => array('hidden' => true),
            ),
            $filters,
            $gridId
        );
		
		$grid->updateColumn('file_name', array(
			'callback' => array(
				'function' => array($this, 'updateFileName'), 
				'params'   => array('{{file_name}}', '{{file_id}}')
			)
		));
		
		$grid->updateColumn('date_created', array(
            'format'   => array('date', array('date_format' => HM_Locale_Format::getDateFormat())),
			'callback' => array(
				'function' => array($this, 'updateDate'), 
				'params'   => array('{{date_created}}')
			)
		));

		$grid->updateColumn('isPublic', array(
			'callback' => array(
				'function' => array($this, 'updateIsPublic'), 
				'params'   => array('{{isPublic}}')
			)
		));

		$grid->updateColumn('publishedFrom', array(
			'callback' => array(
				'function' => array($this, 'updatePublishedFrom'), 
				'params'   => array('{{publishedFrom}}')
			)
		));

		$grid->updateColumn('author', array(
			'callback' => array(
				'function' => array($this, 'updateAuthor'), 
				'params'   => array('{{author}}', $select)
			)
		));
		
		$grid->addAction(
			array('module' => 'bookshelf', 'controller' => 'manager', 'action' => 'delete'), 
			array('bookshelf_id'), 
			_('Удалить'),
			_('Вы уверены?')
		);

		$grid->addAction(
			array('module' => 'bookshelf', 'controller' => 'manager', 'action' => 'publish'), 
			array('bookshelf_id'), 
			_('Изменить публичность'),
			_('Изменить статус на противоположенный?')
		);

		$grid->setActionsCallback(array(
			'function' => array($this, 'updateActions'),
            'params'   => array('{{authorId}}', '{{subjectId}}')
		));
		
		$this->view->setHeader(_('Виртуальная книжная полка'));
		$this->view->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base . 'css/content-modules/material-icons.css');
		$this->view->gridAjaxRequest = $this->isGridAjaxRequest();
		$this->view->grid            = $grid->deploy();
		$this->view->form            = new HM_Form_Manager();
		$this->view->learningSubject = $learningSubject;
		$this->view->publishedToSubjects = $this->_linkedSubjectstsCache;
	}
	
	public function saveAction()
    {	
		$this->getHelper('viewRenderer')->setNoRender();
		
		$data      = array();
		$form      = new HM_Form_Manager();
		$request   = $this->getRequest();
		$user      = $this->getService('User')->getCurrentUser();
		$group_id  = (int)$request->getParam('group_id', 0);
		$subjectId = (int)$request->getParam('subject_id', 0);
		
		$return = array(
            'type'    => HM_Notification_NotificationModel::TYPE_ERROR,
            'message' => _('Заполните все поля')
        );
		
		if(empty($group_id)){
			$return['message'] = _('Выберите группу');
			echo $this->view->notifications(array(array('type' => $return['type'], 'message' => $return['message'])), array('html' => true));		
			echo $form->render();
			return false;
		}
		
		if(empty($subjectId)){
			$return['message'] = _('Выберите сессию');
			echo $this->view->notifications(array(array('type' => $return['type'], 'message' => $return['message'])), array('html' => true));		
			echo $form->render();
			return false;
		}
		
		if(!$request->isPost() && !$request->isGet()){
			echo $form->render();
			return false;
		}
		
		if(!$form->isValid($request->getParams())) {
			echo $form->render();
			return false;
		}
		
		if(!is_object($form->document)){
			$return['message'] = _('Ошибка загрузки файла');
			echo $this->view->notifications(array(array('type' => $return['type'], 'message' => $return['message'])), array('html' => true));		
			echo $form->render();
			return false;
        }

		if(!$form->document->isUploaded()){
			$return['message'] = _('Не удалось загрузить файл');
			echo $this->view->notifications(array(array('type' => $return['type'], 'message' => $return['message'])), array('html' => true));		
			echo $form->render();
			return false;
		}
		
		$file_to_path = $form->document->getFileName();
		
		$data['subject_id'] = (int)$subjectId;
		$data['group_id']   = (int)$group_id;

		if(is_array($file_to_path)){
			foreach($file_to_path as $path){
				
				$fileInfo = pathinfo($path);
				$file     = $this->getService('Files')->addFile($path, $fileInfo['basename']);
				if(!$file){
					$return['message'] = _('Не удалось сохранить файл');
					echo $this->view->notifications(array(array('type' => $return['type'], 'message' => $return['message'])), array('html' => true));		
					echo $form->render();
					return false;
				}
				
				$data['file_id'] = (int)$file->file_id;
				if(!$this->getService('Bookshelf')->addItem($data)){
					$return['message'] = _('Не удалось добавить запись');
					echo $this->view->notifications(array(array('type' => $return['type'], 'message' => $return['message'])), array('html' => true));		
					echo $form->render();
					return false;
				}
			}
		} else {
			
			$fileInfo = pathinfo($file_to_path);
			$file = $this->getService('Files')->addFile($file_to_path, $fileInfo['basename']);
			if(!$file){
				$return['message'] = _('Не удалось сохранить файл');
				echo $this->view->notifications(array(array('type' => $return['type'], 'message' => $return['message'])), array('html' => true));		
				echo $form->render();
				return false;
			}
			$data['file_id'] = (int)$file->file_id;
			
			if(!$this->getService('Bookshelf')->addItem($data)){
				$return['message'] = _('Не удалось добавить запись');
				echo $this->view->notifications(array(array('type' => $return['type'], 'message' => $return['message'])), array('html' => true));		
				echo $form->render();
				return false;
			}
		}
		
		$return = array(
            'type'    => HM_Notification_NotificationModel::TYPE_SUCCESS,
            'message' => _('Запись добавлена')
        );
		
		echo $this->view->notifications(array(array('type' => $return['type'], 'message' => $return['message'])), array('html' => true));
		echo $form->render();
	}
	
	public function deleteAction()
	{
		$request     = $this->getRequest();
		$user        = $this->getService('User')->getCurrentUser();
		$subjectId   = (int)$request->getParam('subject_id', 0);
		$bookshelfId = (int)$request->getParam('bookshelf_id', 0);
		
		$item = $this->getService('Bookshelf')->getById($bookshelfId);
		if(!$item){
			$this->_helper->getHelper('FlashMessenger')->addMessage(array(
				'type'    => HM_Notification_NotificationModel::TYPE_ERROR,
				'message' => _('Запись не найдена')
			));
			$this->_redirector->gotoSimple('index', 'manager', 'bookshelf', array('subject_id' => $subjectId));
			die;
		}
		
		if($item->author_id != $user->MID){
			$this->_helper->getHelper('FlashMessenger')->addMessage(array(
				'type'    => HM_Notification_NotificationModel::TYPE_ERROR,
				'message' => _('Вы не являетесь владельцем записи')
			));
			$this->_redirector->gotoSimple('index', 'manager', 'bookshelf', array('subject_id' => $subjectId));
			die;
		}
		
		if(!empty($item->file_id)){
			$this->getService('Files')->delete($item->file_id);
		}
		
		if(!$this->getService('Bookshelf')->delete($item->bookshelf_id)){
			$this->_helper->getHelper('FlashMessenger')->addMessage(array(
				'type'    => HM_Notification_NotificationModel::TYPE_ERROR,
				'message' => _('Не удалось удалить запись')
			));
			$this->_redirector->gotoSimple('index', 'manager', 'bookshelf', array('subject_id' => $subjectId));
			die;
		}
		
		$this->_helper->getHelper('FlashMessenger')->addMessage(_('Запись удалена'));
		$this->_redirector->gotoSimple('index', 'manager', 'bookshelf', array('subject_id' => $subjectId));
		die;
	}
	
	public function updateFileName($file_name, $file_id)
	{
		$url = $this->view->url(array('action' => 'file', 'controller' => 'get', 'module' => 'file', 'file_id' => $file_id, 'download' => 1));
		
		#$cardLink = $this->view->cardLink(
		#	$url,
		#	_('Карточка виртуальной книжной полки'),
        #   'icon-custom',
        #   'pcard',
        #    'pcard',
        #    'material-icon-small ' . ' resource-filetype-' . 
		#);
		
		$cardLink = '<span class="icon-custom material-icon-small  resource-filetype-' . HM_Files_FilesModel::getFileType($file_name) . '" title="Карточка виртуальной книжной полки"></span>';
		
		return $cardLink . '<a href="' . $url . '">' . $file_name . '</a>';
	}
	
	public function updateDate($date)
    {        
		return $date;    
    }

    public function updateIsPublic($publicStatus)
    {
		$publicStatus = (int)$publicStatus;
		$statusList   = HM_Bookshelf_BookshelfModel::getPublicStatusList();
    	return $statusList[$publicStatus];
    }

    public function updatePublishedFrom($subjectId)
    {
    	if($this->_subjectId != $subjectId){
    		$subject = $this->_linkedSubjectstsCache[$subjectId];
    		$url     = $this->view->url(array('module' => 'subject', 'controller' => 'index', 'action' => 'card', 'subject_id' => $subject['subjectId']));
            return '<a href="' . $url . '" target="_blank">' . $subject['name'] . '</a>';    		
    	}
    	return '';
    }

    public function updateAuthor($authorId, $select)
    {
    	$currentUser = $this->getService('User')->getCurrentUser();
    	if($authorId == $currentUser->MID){
    		return '';
    	}

    	if(!$this->_authorCache){
    		$this->_authorCache = array();
			$authorIds = array();
    		$res       = $select->query()->fetchAll();
			if(!empty($res)){
				foreach($res as $item){
					$authorIds[$item['authorId']] = $item['authorId'];
				}
			}
			$userSelect = $this->getService('Subject')->getSelect();
			$userSelect->from('People', array('MID', 'LastName', 'FirstName', 'Patronymic'));
			$userSelect->where($this->quoteInto('MID IN (?)', $authorIds));
			$res = $userSelect->query()->fetchAll();
			if(!empty($res)){
				foreach($res as $item){
					$this->_authorCache[$item['MID']] = trim($item['LastName'] . ' ' . $item['FirstName'] . ' ' . $item['Patronymic']);
				}
			}
    	}
		if(!array_key_exists($authorId, $this->_authorCache)){
			return 'Пользователь #' . $authorId;
		}
    	return $this->_authorCache[$authorId];
    }

    public function updateActions($authorId, $subjectId, $actions)
    {   
		if(
			$authorId != $this->getService('User')->getCurrentUserId()
			|| 
			$subjectId != $this->_subjectId
		){
			$tmp = explode('<li>', $actions);
			unset($tmp[1]);
            unset($tmp[2]);
            $actions = implode('<li>', $tmp);
		}
        return $actions;
    }

    public function publishAction()
    {
		$subjectId   = (int)$this->_getParam('subject_id');
		$bookshelfId = (int)$this->_getParam('bookshelf_id');
		$user        = $this->getService('User')->getCurrentUser();		
		$bookshelf   = $this->getService('Bookshelf')->getById($bookshelfId);

		if(!$bookshelf){
			$this->_helper->getHelper('FlashMessenger')->addMessage(array(
				'type'    => HM_Notification_NotificationModel::TYPE_ERROR,
				'message' => _('Запись не найдена')
			));
			$this->_redirector->gotoSimple('index', 'manager', 'bookshelf', array('subject_id' => $subjectId));
			die;
		}
		
		if($bookshelf->author_id != $user->MID){
			$this->_helper->getHelper('FlashMessenger')->addMessage(array(
				'type'    => HM_Notification_NotificationModel::TYPE_ERROR,
				'message' => _('Вы не являетесь владельцем записи')
			));
			$this->_redirector->gotoSimple('index', 'manager', 'bookshelf', array('subject_id' => $subjectId));
			die;
		}
		$invertIsPublic = 

		$data = array(
			'bookshelf_id' => $bookshelf->bookshelf_id,
			'isPublic'     => $bookshelf->getInvertIsPublic(),
		);

		if(!$this->getService('Bookshelf')->update($data)){
			$this->_helper->getHelper('FlashMessenger')->addMessage(array(
				'type'    => HM_Notification_NotificationModel::TYPE_ERROR,
				'message' => _('Не удалось изменить запись')
			));
			$this->_redirector->gotoSimple('index', 'manager', 'bookshelf', array('subject_id' => $subjectId));
			die;
		}
		
		$this->_helper->getHelper('FlashMessenger')->addMessage(_('Запись изменена'));
		$this->_redirector->gotoSimple('index', 'manager', 'bookshelf', array('subject_id' => $subjectId));
		die;
    }
	
}