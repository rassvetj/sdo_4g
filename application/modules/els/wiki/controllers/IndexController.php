<?php

class Wiki_IndexController extends HM_Controller_Action_Activity implements Es_Entity_EventViewer 
{
    protected $_subjectName;
    protected $_subjectId;
    protected $_lessonId;
    protected $_isModerator = false;
    
    public function preDispatch()
    {
        if($this->getService('User')->getCurrentUserRole() == 'guest') {
            $this->_redirector->gotoSimple('index', 'index', 'index');
        }
        // parent::preDispatch();
        
        $this->_subjectName = $this->_getParam('subject', '');
        if(empty($this->_subjectName)) {
            $this->_subjectName = null;
        }
        $this->_subjectId = (int) $this->_getParam('subject_id', 0);
        
        $config = Zend_Registry::get('config');
        $this->view->headLink()->appendStylesheet($config->url->base.'css/wiki.css');
        $this->view->subjectName = $this->_subjectName;
        $this->view->subjectId = $this->_subjectId;
        $this->view->isModerator = $this->_isModerator = $this->getService('WikiArticles')->isCurrentUserActivityModerator();
        $this->view->canEdit = true;
        $this->view->canDelete = $this->view->isModerator;
        $this->_lessonId = (int) $this->_getParam('lesson_id', 0);
    }
    
    private function _getTitle()
    {    
        $title = urldecode($this->_getParam('title', ''));
        $title = str_replace('_', ' ', trim($title));
        return $title;
    }
    
    private function _getHistory($article)
    {
        $history = $this->getService('WikiArchive')->getHistory($article->id);
        foreach($history as $ver) {
            $ver->authorInfo = $this->getService('User')->find($ver->author)->current();
            $ver->created = new Zend_Date($ver->created, 'YYYY-MM-DD HH:mm:ss');
            $ver->createdStr = iconv('UTF-8', Zend_Registry::get('config')->charset, $ver->created->toString('dd MMM YYYY, HH'));
        }
        $this->view->history = $history;   
    }
    
    public function contentAction()
    {
        $defaultSession = new Zend_Session_Namespace('defaultWiki');
        if(isset($this->getRequest()->viewType)) {
            $defaultSession->viewType = (($this->getRequest()->viewType == 'table') ? 'table' : 'default');
        }
        if(!isset($defaultSession->viewType) || 
            (isset($defaultSession->viewType) && $defaultSession->viewType == 'table' && !$this->_isModerator)) {
            $defaultSession->viewType = 'default';
        }
        $this->view->viewType = $viewType = $defaultSession->viewType;
        
        $articles = $this->getService('WikiArticles');

        if ($viewType == 'table') {
            $select = $articles->getWikigSelect($this->_subjectId, $this->_subjectName);
            $grid = $this->getGrid(
                $select,
                array(
                    'id' => array('hidden' => true),
                    'title_url' => array('hidden' => true),
                    'title' => array('title' => _('Название'), 'escape' => true),
                    'created' => array('title' => _('Дата создания')),
                    'changed' => array('title' => _('Дата последнего изменения')),
                    'authors' => array('title' => _('Авторы'))
                ),
                array(
                    'title' => null,
                    'created'   => null,
                    'changed'   => null,
                    'authors' => null
                ),
                'grid_wiki'
            );
            
            $grid->addAction(array(
                'module' => 'wiki',
                'controller' => 'index',
                'action' => 'view'
            ),
                array('title_url' => 'title'),
                $this->view->icon('view')
            );
            
            $grid->addAction(array(
                'module' => 'wiki',
                'controller' => 'index',
                'action' => 'edit'
            ),
                array('id'),
                $this->view->icon('edit')
            );

            $grid->addAction(array(
                'module' => 'wiki',
                'controller' => 'index',
                'action' => 'delete'
            ),
                array('id'),
                $this->view->icon('delete')
            );

            $grid->addMassAction(
                array('module' => 'wiki', 'controller' => 'index', 'action' => 'delete-by', 'subject' => $this->_subjectName, 'subject_id' => $this->_subjectId),
                _('Удалить'),
                _('Вы подтверждаете удаление отмеченных записей?')
            );

            $grid->updateColumn('title_url', array(
                'callback' => array(
                    'function'=> array('HM_Wiki_WikiArticlesModel', 'getUrl'),
                    'params'=> array('{{title}}')
                )
            ));
            $grid->updateColumn('created', array(
                'callback' => array(
                    'function'=> array($this, 'getDateForGrid'),
                    'params'=> array('{{created}}')
                )
            ));
            $grid->updateColumn('changed', array(
                'callback' => array(
                    'function'=> array($this, 'getDateForGrid'),
                    'params'=> array('{{changed}}')
                )
            ));


            $filters = new Bvb_Grid_Filters();
            $filters->addFilter('title');
            $filters->addFilter('authors', array(
                'callback' => array(
                    'function' => array($this, 'customAuthorsFilter')
                )
            ));
            $filters->addFilter('created', array('render' => 'GridDate'));
            $filters->addFilter('changed', array('render' => 'GridDate'));
            $grid->addFilters($filters);
            
            $grid->updateColumn('authors', array(
                'callback' => array(
                    'function'=> array($this, 'getAuthorsForGrid'),
                    'params'=> array('{{id}}')
                )
            ));

            $this->view->grid = $grid->deploy();
        } else {
            $this->view->articles = $articles->fetchAll($articles->getCondition($this->_subjectId, $this->_subjectName), 'title');
        }
        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
    }
    
    public function customAuthorsFilter($data)
    {
        $users = $this->getService('User');
        $data['value'] = strtolower(trim($data['value']));
        $usersList = $users->fetchAll(
            $users->quoteInto('LOWER(LastName) LIKE ?', '%'.$data['value'].'%').' OR '.
            $users->quoteInto('LOWER(FirstName) LIKE ?', '%'.$data['value'].'%').' OR '.
            $users->quoteInto('LOWER(Patronymic) LIKE ?', '%'.$data['value'].'%').' OR '.
            $users->quoteInto('LOWER(CONCAT(CONCAT(CONCAT(CONCAT(LastName, \' \') , FirstName), \' \'), Patronymic)) LIKE ?', '%'.$data['value'].'%')
        );
        $ids = array();
        foreach($usersList as $user) {
            $ids []= $user->MID;
        }
        if(count($ids) > 0) {
            $data['select']->joinLeft(array('arch' => 'wiki_archive'),
                        'arch.article_id = wiki_articles.id',
                        array('arch.author')
                    );
            $data['select']->where('arch.author IN ('.implode(',', $ids).')');
        } else {
            $data['select']->where('1 != 1');
        }
    }
    
    public function indexAction()
    {
        $articles = $this->getService('WikiArticles');
        if($this->_lessonId) {
            $article = $articles->getByLesson($this->_lessonId, $this->_subjectId, $this->_subjectName);
        } else {
            $article = $articles->getByTitle(_('Главная страница'), $this->_subjectId, $this->_subjectName);
        }
        if($article && $article->id) {
            $filter = $this->getFilterByRequest($this->getRequest());
            if ($filter->getGroupId() != null) {
                $this->getService('EventServerDispatcher')->trigger(
                    Es_Service_Dispatcher::EVENT_UNSUBSCRIBE,
                    $this,
                    array('filter' => $filter)
                );
            }
            $this->_setParam('id', $article->id);
            $this->viewAction();
            echo $this->view->render('index/view.tpl');
            return;
        } else {
            $this->getService('Unmanaged')->getController()->persistent_vars->terminate(); // unmanaged hack for breadcrumbs
            $this->_redirector->gotoSimple('edit', 'index', 'wiki', array(
                'subject' => $this->_subjectName, 
                'subject_id' => $this->_subjectId,
                'title' => _('Главная_страница')
            ));
        }
    }

    public function getFilterByRequest(Zend_Controller_Request_Http $request) {
        $filter = $this->getService('ESFactory')->newFilter();
        $filter->setUserId((int)$this->getService('User')->getCurrentUserId());
        $subject = $request->getParam('subject_id', null);
        if ($subject !== null) {
            $groupAdd = $this->getService('ESFactory')->eventGroup(
                HM_Wiki_WikiArticlesService::EVENT_GROUP_NAME_PREFIX, (int)$subject
            );
            $groupModify = $this->getService('ESFactory')->eventGroup(
                HM_Wiki_WikiArticlesService::EVENT_GROUP_NAME_PREFIX, (int)$subject
            );
        } else {
            $groupAdd = $this->getService('ESFactory')->eventGroup(
                HM_Wiki_WikiArticlesService::EVENT_GROUP_NAME_PREFIX, 0
            );
            $groupModify = $this->getService('ESFactory')->eventGroup(
                HM_Wiki_WikiArticlesService::EVENT_GROUP_NAME_PREFIX, 0
            );
        }
        $groupIds = array();
        if ($groupAdd->getId() !== null) {
            $groupIds[] = $groupAdd->getId();
        }
        if ($groupModify->getId() !== null) {
            $groupIds[] = $groupModify->getId();
        }
        $filter->setGroupId($groupIds);
        $filter->setEventId($request->getParam('eventId', null));
        return $filter;
    }

    public function getDateForGrid($date)
    {
        if (!$date) {
            return '';
        }
        $date = new Zend_Date($date, 'YYYY-MM-DD HH:mm:ss');
        return iconv('UTF-8', Zend_Registry::get('config')->charset, $date->toString(HM_Locale_Format::getDateFormat()));
    }
    
    public function getAuthorsForGrid($articleId)
    {
        $authors = $this->getService('WikiArchive')->getAuthors($articleId);
        $ret = array();
        foreach($authors as $user) {
            $ret []= $user->LastName .' '.$user->FirstName.' '.$user->Patronymic;
        }
        return implode(', ', $ret);
    }
    
    public function newAction()
    {
        $articles = $this->getService('WikiArticles');
        $form = new HM_Form_CreateArticle();
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {
                $article = $articles->insert(array(
                    'title' => $this->_getTitle(),
                    'subject_name' => $this->_subjectName, 
                    'subject_id' => $this->_subjectId
                ));
                $this->_redirector->gotoSimple('edit', 'index', 'wiki', array(
                    'subject' => $this->_subjectName, 
                    'subject_id' => $this->_subjectId,
                    'id' => $article->id
                ));
            }
        } else {
            $formValues = array();
            $formValues['title'] = $this->_getTitle();
            $form->setDefaults($formValues);
        }
        
        $this->view->form = $form;
    }

    public function viewAction()
    {        
        $articles = $this->getService('WikiArticles');
        $archive = $this->getService('WikiArchive');
        $title = $this->_getTitle();
        $version = (int)$this->_getParam('version', 0);
        $id = (int)$this->_getParam('id', 0);
        if($id) {
            $article = $articles->find($id)->current();
        } elseif($title) {
            $article = $articles->getByTitle($title, $this->_subjectId, $this->_subjectName);
        }
        if($article && $article->id) {
            $form = new HM_Form_Comment();
            $form->setAction($this->view->url(array(
                'module' => 'wiki',
                'controller' => 'index',
                'action' => 'view',
                'subject' => $this->_subjectName,
                'subject_id' => $this->_subjectId,
                'id' => $article->id
            ), null, true));
            if ($this->_request->isPost()) {
                if ($form->isValid($this->_request->getParams())) {
                    $comment = new HM_Comment_CommentModel();
                    $comment->user_id = $this->getService('User')->getCurrentUserId();
                    $comment->item_id = $article->id;
                    $comment->message = $form->getValue('message');
                    $comment = $articles->insertActivityComment($comment);

                    $this->_flashMessenger->addMessage(_('Комментарий успешно добавлен'));
                    $this->_redirector->gotoSimple('view', 'index', 'wiki', array(
                        'subject' => $this->_subjectName,
                        'subject_id' => $this->_subjectId,
                        'id' => $id
                    ));
                }
            }
            $this->view->form = $form;

            if($version) {
                $article->body = $archive->render($archive->find($version)->current());
            } else {
                $article->body = $archive->render($archive->getBody($article->id));
            }
            $article->comments = $articles->fetchAllActivityComments($article->id);
            $article->comments_count = $article->comments->count();
            $this->view->article = $article;
            $this->view->isFullView = true;
            $this->view->linksUrl = $this->view->url(array(
                'module' => 'wiki',
                'controller' => 'index',
                'action' => 'view',
                'subject' => $this->_subjectName,
                'subject_id' => $this->_subjectId,
                'id' => $article->id
            ), null, true);

            $like = $this->getService('Like')->fetchRow(
                $this->quoteInto(array('item_type = ? ', ' AND item_id = ?'),
                    array(HM_Like_LikeModel::ITEM_TYPE_WIKI, $article->id))
            );

            if ($like) {
                $like = array(
                    'count_like'    => $like->count_like,
                    'count_dislike' => $like->count_dislike,
                );

                $userLike = $this->getService('LikeUser')->fetchRow(
                    $this->quoteInto(array('item_type = ? ', ' AND item_id = ?'),
                        array(HM_Like_LikeModel::ITEM_TYPE_WIKI, $article->id))
                );

                if ($userLike) {
                    $like['user_vote'] = $userLike->value;
                }
            } else {
                $like = array(
                    'count_like'    => 0,
                    'count_dislike' => 0,
                    'user_vote'     => 0
                );
            }

            $this->view->likes = array($article->id => $like);
            $this->_getHistory($article);
        } else {
            $this->_redirector->gotoSimple('edit', 'index', 'wiki', array(
                'subject' => $this->_subjectName, 
                'subject_id' => $this->_subjectId,
                'title' => $this->_getParam('title', '')
            ));
        }
        
        if($article->lesson_id) {
            $lesson = $this->getService('Lesson')->getOne($this->getService('Lesson')->findDependence('Assign', $article->lesson_id));
            $this->view->canEdit = $this->view->isModerator || ($lesson && $lesson->isStudentAssigned($this->getService('User')->getCurrentUserId()));
            $this->view->canDelete = false;
        }
        
        $this->view->title = $title;
        $this->view->rawTitle = $this->_getParam('title', '');
        
        $filter = $this->getFilterByRequest($this->getRequest());
        if ($filter->getGroupId() != null) {
            $this->getService('EventServerDispatcher')->trigger(
                Es_Service_Dispatcher::EVENT_UNSUBSCRIBE,
                $this,
                array('filter' => $filter)
            );
        }


    }

    public function deleteAction()
    {
        $id = $this->_getParam('id', 0);
        if ($id) {
            $this->getService('WikiArticles')->delete($id);
            $this->_flashMessenger->addMessage(_('Статья успешно удалена'));
        }
        $this->_redirector->gotoSimple('content', 'index', 'wiki', array('subject' => $this->_subjectName, 'subject_id' => $this->_subjectId));
    }
    
    public function deleteByAction()
    {
        $ids = explode(',', $this->_request->getParam('postMassIds_grid_wiki'));
        foreach ($ids as $value) {
            $value = (int)$value;
            if($value) {
                $this->getService('WikiArticles')->delete($value);
            }
        }
        $this->_flashMessenger->addMessage(_('Статьи успешно удалены'));
        $this->_redirector->gotoSimple('content', 'index', 'wiki', array('subject' => $this->_subjectName, 'subject_id' => $this->_subjectId));
    }

    public function editAction()
    {
        $articles = $this->getService('WikiArticles');
        $archive = $this->getService('WikiArchive');

        $title = $this->_getTitle();
        $id = (int)$this->_getParam('id', 0);
        if($id) {
            $article = $articles->find($id)->current();
        } elseif($title) {
            $article = $articles->getByTitle($title, $this->_subjectId, $this->_subjectName);
        }
        
        $form = new HM_Form_EditArticle(($article->id && $article->lesson_id));
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {
                $id = (int)$form->getValue('id', 0);
                if($id) {
                    $article = $articles->find($id)->current();
                    $body = $this->addWikiPagetoArchive($article, $form);
                    $articles->update(array(
                        'changed' => $body->created,
                        'id' => $article->id
                    ));
                } else {
                    $article = $articles->insert(array(
                        'title' => $title,
                        'subject_name' => $this->_subjectName, 
                        'subject_id' => $this->_subjectId
                    ));
                    $this->addWikiPagetoArchive($article, $form);
                }
                $this->_redirector->gotoSimple('view', 'index', 'wiki', array(
                    'subject' => $this->_subjectName, 
                    'subject_id' => $this->_subjectId,
                    'id' => $article->id
                ));
            }
        } else {
            $formValues = array();
            if($article && $article->id) {
                $article = $articles->find($id)->current();
                $body = $archive->getBody($article->id);
                if ($article->id && $body->id) {
                    $formValues['id'] = $article->id;
                    $formValues['title'] = $article->title;
                    $formValues['body'] = stripslashes($body->body);
                }
                $this->view->title = $article->title;
                $this->view->article = $article;
            } else {
                $formValues['title'] = $title;
                $formValues['body'] = 'h1. '.ucfirst($title);
                $this->view->title = $title;
            }
            $form->setDefaults($formValues);
        }
        $this->view->form = $form;
    }

    public function addWikiPageToArchive($article, $form) {
        $archive = $this->getService('WikiArchive');
        $body = $archive->insert(array(
            'author' => $this->getService('User')->getCurrentUserId(),
            'article_id' => $article->id,
            'body' => $form->getValue('body')
        ));
        return $body;
    }

    public function restoreAction()
    {
        $articles = $this->getService('WikiArticles');
        $archive = $this->getService('WikiArchive');
        $version = (int)$this->_getParam('version', 0);
        if($version) {
            $body = $archive->find($version)->current();
            $article = $articles->find($body->article_id)->current();
            $archive->insert($body->getValues());
            $this->_redirector->gotoSimple('view', 'index', 'wiki', array(
                'subject' => $this->_subjectName,
                'subject_id' => $this->_subjectId,
                'id' => $article->id
            ));
        }
        $this->_redirector->gotoSimple('index', 'index', 'wiki', array('subject' => $this->_subjectName, 'subject_id' => $this->_subjectId));
    }
    
    public function compareAction()
    {
        $id1 = $this->_getParam('id1');
        $id2 = $this->_getParam('id2');
        if ($id1 && $id2) {
            $config = Zend_Registry::get('config');
            $archive = $this->getService('WikiArchive');
            $body1 = $archive->find($id1)->current();
            $article = $this->getService('WikiArticles')->find($body1->article_id)->current();
            $this->_getHistory($article);
            $body2 = $archive->find($id2)->current();
            if ($body1->id && $body2->id) {
                $this->view->ver1 = $body1;
                $this->view->ver1->author = $this->getService('User')->find($body1->author)->current();
                $this->view->ver1->author_avatar = $config->url->base.$this->view->ver1->author->getPhoto();
                $this->view->ver1->body = $archive->render($body1);
                $this->view->ver2 = $body2;
                $this->view->ver2->author = $this->getService('User')->find($body2->author)->current();
                $this->view->ver2->author_avatar = $config->url->base.$this->view->ver2->author->getPhoto();
                $this->view->ver2->body = $archive->render($body2);
                $this->view->article = $article;
            }
        }
    }
    
    public function historyAction()
    {
        $this->view->history = $this->getService('WikiArchive')->getAllHistory($this->_subjectId, $this->_subjectName);
    }
}
