<?php
class News_IndexController extends HM_Controller_Action_Activity
{
    public function indexAction()
    {
    	$this->view->headScript()->appendFile(Zend_Registry::get('config')->url->base.'themes/rgsu/js/dev.js');
		
		$defaultSession = new Zend_Session_Namespace('default');
    	$defaultSession->viewType = $viewType = $this->_request->getParam('viewType', 'default');
		$isTutor = ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_TUTOR)) ? true : false;
        
        $subjectName = $this->_getParam('subject', '');
        $subjectId = (int) $this->_getParam('subject_id', 0);
        $isModerator = $this->getService('News')->isCurrentUserActivityModerator();
        if($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_DEAN))
            $isModerator = true;//Если новости в курсе, то isCurrentUserActivityModerator проверяет в окружении курса, а не новостей

        // grid for moderator
        if($isModerator && $viewType == 'table'){
        	
	        if (!$this->_hasParam('ordergrid_news')) $this->_setParam('ordergrid_news', 'created_DESC');
	        $select = $this->getService('News')->getSelect();
	        $select->from(
	            'news',
	            array('id', 'news_id' => 'id', 'created', 'author', 'announce')
	        )
	        ->where('subject_id = ?', $subjectId);
	        if ($subjectName) {
	            $select->where('subject_name = ?', $subjectName);
	        } else {
	            $select->where("subject_name IS NULL OR subject_name = ''");
	        }
	        
	        $grid = $this->getNewsGrid($select, $subjectName, $subjectId);
	
	        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
	        $this->view->grid = $grid->deploy();
	        
        }
        // default list for everybody
        else{
        	
        	$filter = array();
            if(isset($this->_request->filter)) {
                $filter[$this->_request->filter] = $this->_request->{$this->_request->filter};
            }
            $paginator = $this->getService('News')->getPaginator(
                $this->getService('News')->getNewsCondition($subjectId, $subjectName, $filter, true),
                'created DESC'
            );
            $paginator->setItemCountPerPage((int) Zend_Registry::get('config')->dimensions->news_per_page);
            $paginator->setCurrentPageNumber($this->_request->getParam('page', 1));

            //$this->_sideBar();
            
            $this->view->news = $paginator;
            $this->view->isFullView = false;  
                  	
        }
		
		if($isTutor){
			$groups       = array();
			$users_groups = $this->getService('Subject')->getUsersGroupsById($subjectId);
			if(!empty($users_groups)){
				$users_groups = $this->getService('Subject')->filterGroupsByAssignStudents($subjectId, $this->getService('User')->getCurrentUserId(), $users_groups);
			}
			
			$group_collection = $this->getService('StudyGroup')->getBySubject($subjectId);
			$group_collection = $this->filteredGroups($group_collection, $users_groups);
			
			if(!count($group_collection)){
				$groups[] = _('Нет');
			} else {
				foreach($group_collection as $group){ $groups[] = $group->getName(); }
			}			 
			$this->view->groups = implode(', ', $groups);
		}
			

        //$this->view->title = $this->getService('News')->getSubjectTitle($subjectName, $subjectId);
        $this->view->isTutor  = $isTutor;
		$this->view->viewType = $viewType;
        $this->view->isModerator = $isModerator;
        $this->view->subjectName = $subjectName;
        $this->view->subjectId = $subjectId;
    }

    public function newAction()
    {
        $subjectName = $this->_getParam('subject', '');
        $subjectId = (int) $this->_getParam('subject_id', 0);

        if (!$this->getService('News')->isCurrentUserActivityModerator()) {
            $this->_flashMessenger->addMessage(array('message' => _('Вы не являетесь модератором данного вида взаимодействия'), 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
            $this->_redirector->gotoSimple('index', 'index', 'news', array('subject' => $subjectName, 'subject_id' => $subjectId));
        }


        $form = new HM_Form_News();

        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {
                $authorName = sprintf(_('Пользователь #%d'), $this->getService('User')->getCurrentUserId());
                $author = $this->getOne($this->getService('User')->find($this->getService('User')->getCurrentUserId()));
                if ($author) {
                    $authorName = $author->getName();
                }
                                
                $this->getService('News')->insert(array(
                    'message' => $form->getValue('message'),
                    'announce' => $form->getValue('announce'),
                    'subject_name' => $form->getValue('subject_name'),
                    'subject_id' => $form->getValue('subject_id'),
                    'author' => $authorName,
                    'created_by' => $this->getService('User')->getCurrentUserId(),
                ));

                $this->_flashMessenger->addMessage(_('Новость опубликована'));
                $this->_redirector->gotoSimple('index', 'index', 'news', array('subject' => $subjectName, 'subject_id' => $subjectId));

            }
        } else {
            $form->setDefaults(
                array(
                    'subject_name' => $subjectName,
                    'subject_id' => $subjectId
                )
            );
        }

        $this->view->form = $form;
        
    }

    public function editAction()
    {
        $subjectName = $this->_getParam('subject', '');
        $subjectId = (int) $this->_getParam('subject_id', 0);
        $news_id = (int) $this->_getParam('news_id', 0);

        if (!$this->getService('News')->isCurrentUserActivityModerator()) {
            $this->_flashMessenger->addMessage(array('message' => _('Вы не являетесь модератором данного вида взаимодействия'), 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
            $this->_redirector->gotoSimple('index', 'index', 'news', array('subject' => $subjectName, 'subject_id' => $subjectId));
        }

        $form = new HM_Form_News();
        $form->setAction($this->view->url(array('module' => 'news', 'controller' => 'index', 'action' => 'edit')));

        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {

                $this->getService('News')->update(array(
                    'message' => $form->getValue('message'),
                    'announce' => $form->getValue('announce'),
                    'id' => $form->getValue('id')
                ));

                $this->_flashMessenger->addMessage(_('Новость успешно изменена'));
                $this->_redirector->gotoSimple('index', 'index', 'news', array('subject' => $subjectName, 'subject_id' => $subjectId));

            }
        } else {
            if ($news_id) {
                $news = $this->getOne($this->getService('News')->find($news_id));
                $values = array();
                if ($news) {
                    $values = $news->getValues();
                }
                $form->setDefaults($values);
            }
        }

        $this->view->form = $form;

    }

    public function deleteAction()
    {
        $subjectName = $this->_getParam('subject', '');
        $subjectId = (int) $this->_getParam('subject_id', 0);

        if (!$this->getService('News')->isCurrentUserActivityModerator()) {
            $this->_flashMessenger->addMessage(array('message' => _('Вы не являетесь модератором данного вида взаимодействия'), 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
            $this->_redirector->gotoSimple('index', 'index', 'news', array('subject' => $subjectName, 'subject_id' => $subjectId));
        }
        
        $id = $this->_getParam('news_id', 0);
        if ($id) {
            $this->getService('News')->delete($id);
        }


        $this->_flashMessenger->addMessage(_('Новость успешно удалена'));
        $this->_redirector->gotoSimple('index', 'index', 'news', array('subject' => $subjectName, 'subject_id' => $subjectId));
    }


    public function deleteByAction()
    {
        $subjectName = $this->_getParam('subject', '');
        $subjectId = (int) $this->_getParam('subject_id', 0);

        if (!$this->getService('News')->isCurrentUserActivityModerator()) {
            $this->_flashMessenger->addMessage(array('message' => _('Вы не являетесь модератором данного вида взаимодействия'), 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
            $this->_redirector->gotoSimple('index', 'index', 'news', array('subject' => $subjectName, 'subject_id' => $subjectId));
        }
        
        $ids = explode(',', $this->_request->getParam('postMassIds_grid_news'));
        foreach ($ids as $value) {
            $this->getService('News')->delete($value);
        }
        $this->_flashMessenger->addMessage(_('Новости успешно удалены'));
        $this->_redirector->gotoSimple('index', 'index', 'news', array('subject' => $subjectName, 'subject_id' => $subjectId));
    }

    public function viewAction()
    {
        $subjectName = $this->_getParam('subject', '');
        $subjectId = (int) $this->_getParam('subject_id', 0);
        $newsId = (int) $this->_getParam('news_id', 0); // точка отсчета
        $step = (int) $this->_getParam('step', 0); // куда листнули от точки отсчета
        $isModerator = $this->getService('News')->isCurrentUserActivityModerator();

        $news = $this->getService('News')->getNews($newsId, $subjectName, $subjectId, $step);
		/*        
 		$triple = $this->getService('News')->getNewsTriple($newsId, $subjectName, $subjectId);
        $prev = $triple[0];
        $news = $triple[1];
        $next = $triple[2];
		*/
        //pr($news);
        
        $this->view->isModerator = $isModerator;
        $this->view->news = $news;
    }
    
    protected function getNewsGrid($select, $subjectName, $subjectId){
    	
	        $grid = $this->getGrid(
	            $select,
	            array(
	                'id' => array('hidden' => true),
	                'news_id' => array('hidden' => true),
	                'created' => array('title' => _('Дата')),
	                'author' => array('title' => _('Автор')),
	                'announce' => array('title' => _('Анонс новости'), 'escape' => false)
	            ),
	            array(
	                'news_id' => null,
	                'created'   => array('render' => 'Date'),
	                'author' => null,
	                'announce' => null
	            ),
	            'grid_news'
	        );
	
            $grid->addAction(array(
                'module' => 'news',
                'controller' => 'index',
                'action' => 'edit'
            ),
                array('news_id'),
                $this->view->icon('edit')
            );

            $grid->addAction(array(
                'module' => 'news',
                'controller' => 'index',
                'action' => 'delete'
            ),
                array('news_id'),
                $this->view->icon('delete')
            );
            
            $grid->addMassAction(
                array('module' => 'news', 'controller' => 'index', 'action' => 'delete-by', 'subject' => $subjectName, 'subject_id' => $subjectId),            _('Удалить'),
                _('Вы подтверждаете удаление отмеченных новостей?')
            );

	
	        $grid->updateColumn('created', array(
	            'format' => array(
	                'date',
	                array('date_format' => HM_Locale_Format::getDateFormat())
	            )
	        ));
	        
	        return $grid;
    	
    }

    public function preDispatch()
    {
        $activitySubjectName = $this->_getParam('subject', '');
        $activitySubjectId = $this->_getParam('subject_id', 0);
        if ($this->getService('User')->getCurrentUserRole() == HM_Role_RoleModelAbstract::ROLE_GUEST
            && !$activitySubjectName
            && !$activitySubjectId) {
            return true;
        }

        parent::preDispatch();
    }
	
	protected function filteredGroups($groups, $user_groups)
	{
		$collection = new HM_Collection();
		$collection->setModelClass($groups->getModelClass());
		
		if(empty($user_groups)){ return $collection; }
		
		foreach($user_groups as $name){
			$group = $groups->exists('name', $name);
			if(!$group){ continue; }
			$collection[count($collection)] = $group;
		}
		return $collection;
	}
    
}