<?php
class Bookshelf_IndexController extends HM_Controller_Action_Subject {
	
	public function init()
	{	
		parent::init();
		
		if($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_TUTOR)){
			$subjectId = (int)$this->_getParam('subject_id');
			$this->_redirector->gotoSimple('index', 'manager', 'bookshelf', array('subject_id' => $subjectId));
			die;	
		}
	}
	
	
	public function indexAction()
    {		
		$subjectId        = (int)$this->_getParam('subject_id');
		$gridId           = $subjectId ? 'grid' . $subjectId : 'grid';
		$user_group       = $this->getService('StudyGroupUsers')->getUserGroup($this->getService('User')->getCurrentUserId());
		$group_id         = (int)$user_group->group_id;		
		$linkedSubjectstIds = array();
		
		$subject = $this->getService('Subject')->getById($subjectId);
		if($subject && !empty($subject->learning_subject_id_external)){
			$learningSubject = $this->getService('Learningsubjects')->getByCode($subject->learning_subject_id_external);
		}

		if($learningSubject){
			$publicSelect = $this->getService('Subject')->getSelect();
			$publicSelect->from(array('s'  => 'subjects'), array('subjectId' => 's.subid'));
			$publicSelect->join(array('ls' => 'learning_subjects'), 'ls.id_external = s.learning_subject_id_external',  array());
			$publicSelect->where($this->quoteInto('s.subid!=?',$subjectId));
			$publicSelect->where($this->quoteInto(array('ls.name=?'), array($learningSubject->name)));
			$res = $publicSelect->query()->fetchAll();
			if(!empty($res)){
				foreach($res as $item){
					$linkedSubjectstIds[$item['subjectId']] = $item['subjectId'];					
				}
			}
		}
		
		$fields = array(
			'file_id'      => 'f.file_id',
			'file_name'    => 'f.name',
		);
		
		$filters = array(
			'file_id'      => null,
			'file_name'    => null,
		);


		$select = $this->getService('Bookshelf')->getSelect();
		$select->from(array('b' => 'bookshelf'), $fields);
		$select->joinLeft(array('f' => 'files'),        'f.file_id  = b.file_id',  array());
		$select->joinLeft(array('g' => 'study_groups'), 'g.group_id = b.group_id', array());
		
		if(empty($linkedSubjectstIds)){
			$select->where('b.subject_id = ?', $subjectId);
			$select->where('b.group_id = ?', $group_id);
		} else {
			$select->where($this->quoteInto(array(
				'(   (b.subject_id=?', ' AND b.group_id=?) ',
				  'OR 
					 (b.subject_id IN (?)', ' AND b.isPublic=?) 
			    )'
			), array(
			    $subjectId, $group_id,
			    array_keys($linkedSubjectstIds), HM_Bookshelf_BookshelfModel::PUBLIC_STATUS_YES
			)));
		}		
		$select->order('file_name');
		
		$grid = $this->getGrid(
            $select,
            array(
                'file_id'      => array('hidden' => true),
                'file_name'    => array('title' => _('Файл')),
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
		
		$this->view->setHeader(_('Виртуальная книжная полка'));
		$this->view->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base . 'css/content-modules/material-icons.css');
		$this->view->gridAjaxRequest = $this->isGridAjaxRequest();
		$this->view->grid            = $grid->deploy();
	}
	
	public function updateFileName($file_name, $file_id)
	{
		$url = $this->view->url(array('action' => 'file', 'controller' => 'get', 'module' => 'file', 'file_id' => $file_id, 'download' => 0));
		
		#$cardLink = $this->view->cardLink(
		#	$url,
		#	_('Карточка виртуальной книжной полки'),
        #   'icon-custom',
        #   'pcard',
        #    'pcard',
        #    'material-icon-small ' . ' resource-filetype-' . 
		#);
		
		$cardLink = '<span class="icon-custom material-icon-small  resource-filetype-' . HM_Files_FilesModel::getFileType($file_name) . '" title="Карточка виртуальной книжной полки"></span>';
		
		return $cardLink . '<a target="_blank" href="' . $url . '">' . $file_name . '</a>';
	}
	
}