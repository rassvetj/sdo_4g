<?php
class Blog_IndexController extends HM_Controller_Action_Activity implements Es_Entity_EventViewer
{
    protected $_subjectName;
    protected $_subjectId;
    protected $_isModerator;
    protected $_allowedActions = array('index', 'view', 'tags');

    public function preDispatch()
    {
        if($this->getService('User')->getCurrentUserRole() == 'guest') {
            $this->_redirector->gotoSimple('index', 'index', 'index');
        }

        parent::preDispatch();

        $this->_subjectName = $this->_getParam('subject', '');
        if(empty($this->_subjectName)) {
            $this->_subjectName = null;
        }
        $this->_subjectId = (int) $this->_getParam('subject_id', 0);

        if(!in_array($this->_request->getActionName(), $this->_allowedActions)) {
            $this->_checkPermissions();
        }
        $this->view->subjectName = $this->_subjectName;
        $this->view->subjectId = $this->_subjectId;
        $this->view->isModerator = $this->_isModerator = $this->getService('Blog')->isCurrentUserActivityModerator();
    }

    private function _checkPermissions()
    {
        if (!$this->getService('Blog')->isCurrentUserActivityModerator()) {
            $this->_flashMessenger->addMessage(array('message' => _('Вы не являетесь модератором данного вида взаимодействия'), 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
            $this->_redirector->gotoSimple('index', 'index', 'blog', array('subject' => $this->_subjectName, 'subject_id' => $this->_subjectId));
        }
    }

    public function indexAction()
    {
        $blogServ = $this->getService('Blog');
        $defaultSession = new Zend_Session_Namespace('defaultBlog');
        if(isset($this->getRequest()->viewType)) {
            $defaultSession->viewType = (($this->getRequest()->viewType == 'table') ? 'table' : 'default');
        }
        if(!isset($defaultSession->viewType) ||
            (isset($defaultSession->viewType) && $defaultSession->viewType == 'table' && !$this->_isModerator)) {
                $defaultSession->viewType = 'default';
            }
        $this->view->viewType = $viewType = $defaultSession->viewType;

        $this->view->blogName = $blogServ->getSubjectTitle($this->_subjectName, $this->_subjectId);

        if ($viewType == 'table') {
            $select = $blogServ->getBlogSelect($this->_subjectId, $this->_subjectName);
            #            print_r($select);
            #            exit;
            $grid = $this->getGrid(
                $select,
                array(
                    'id' => array('hidden' => true),
                    'blog_id' => array('hidden' => true),
                    'title' => array('title' => _('Название'), 'escape' => false),
                    'created' => array('title' => _('Дата')),
                    //'author' => array('title' => 'Автор'),
                    'created_by' => array('title' => _('Автор')),
                    'tags' => array('title' => _('Метки'))
                ),
                array(
                    'blog_id' => null,
                    'title' => null,
                    'created'   => array('render' => 'Date'),
                    //'author' => null,
                    'created_by' => null,
                    'tags' => null
                ),
                'grid_blog'
            );

            $grid->addAction(array(
                'module' => 'blog',
                'controller' => 'index',
                'action' => 'edit'
            ),
            array('blog_id'),
            $this->view->icon('edit')
        );

            $grid->addAction(array(
                'module' => 'blog',
                'controller' => 'index',
                'action' => 'delete'
            ),
            array('blog_id'),
            $this->view->icon('delete')
        );

            $grid->addMassAction(
                array('module' => 'blog', 'controller' => 'index', 'action' => 'delete-by', 'subject' => $this->_subjectName, 'subject_id' => $this->_subjectId),
                _('Удалить'),
                _('Вы подтверждаете удаление отмеченных записей?')
            );

            $grid->updateColumn('created', array(
                'callback' => array(
                    'function'=> array($this, 'getDateForGrid'),
                    'params'=> array('{{created}}')
                )
            ));
            $grid->updateColumn('created_by', array(
                'callback' => array(
                    'function'=> array($this, 'getAuthorForGrid'),
                    'params'=> array('{{created_by}}')
                )
            ));
            $grid->updateColumn('tags', array(
                'callback' => array(
                    'function'=> array($this, 'displayTags'),
                    'params'=> array('{{blog_id}}', HM_Tag_Ref_RefModel::TYPE_BLOG )
                )
            ));

            $filters = new Bvb_Grid_Filters();
            $filters->addFilter('title');
            $filters->addFilter('tags', array(
                'callback' => array(
                    'function' => array($this, 'filterTags')
                )
            ));
            $filters->addFilter('created', array('render' => 'SubjectDate'));
            $filters->addFilter('author');
            $grid->addFilters($filters);

            $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
            $this->view->grid = $grid->deploy();
        } else {
            $filter = array();
            if(isset($this->_request->filter)) {
                $filter[$this->_request->filter] = $this->_request->{$this->_request->filter};
            }
            $config = Zend_Registry::get('config');

            $blogPosts = $blogServ->getPaginator(
                $blogServ->getBlogCondition($this->_subjectId, $this->_subjectName, $filter, true),
                'created DESC', 'Tag', 'TagRefBlog'
            );
            $blogPosts->setItemCountPerPage((int)$config->dimensions->blog_posts_per_page);
            $blogPosts->setCurrentPageNumber($this->_request->getParam('page', 1));
            foreach($blogPosts as $blogPost) {
                $author = $this->getService('User')->find($blogPost->created_by)->current();
                if ( $author->MID ) {
                    $blogPost->author_avatar = $config->url->base.$author->getPhoto();
                    $blogPost->author = $author->getName();
                } else {
                    $blogPost->author_avatar = $config->url->base.$config->src->default->photo;
                    $blogPost->author = _('Пользователь был удален');
                }

                $blogPost->comments_count = $blogServ->fetchAllActivityComments($blogPost->id)->count();

                $regex = '#<object\s*(.*?)\s*classid=[\'"](.*?)[\'"]\s*(.*?)\s*codebase=[\'"](.*?)[\'"](.*)>#i';
                $blogPost->body = addslashes(preg_replace($regex, '<object $1 $3 $5>', stripslashes($blogPost->body)));
            }
            $this->_sideBar();
            $this->view->headLink()->appendStylesheet($config->url->base.'css/blog.css');
            $this->view->blogPosts = $blogPosts;
            $this->view->isFullView = false;
        }
        $this->getService('EventServerDispatcher')->trigger(
            Es_Service_Dispatcher::EVENT_UNSUBSCRIBE,
            $this,
            array('filter' => $this->getFilterByRequest($this->getRequest()))
        );
    }

    public function getAuthorForGrid($mid)
    {
        $author = $this->getService('User')->find($mid)->current();
        return  ( $author->MID )? $author->getName() : _('Пользователь был удален');
    }

    public function getDateForGrid($date)
    {
        $date = new Zend_Date($date, 'YYYY-MM-DD HH:mm:ss');
        return iconv('UTF-8', Zend_Registry::get('config')->charset, $date->toString(HM_Locale_Format::getDateFormat()));
        // return $date->toString(HM_Locale_Format::getDateFormat());
    }

    public function newAction()
    {
        $form = new HM_Form_Blog();

        $request = $this->getRequest();

        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {
                /* @var $blogServ HM_Blog_BlogService */
                $blogServ = $this->getService('Blog');
                $blogObj = $blogServ->insert(array(
                    'title' => $form->getValue('title'),
                    'body' => $form->getValue('body'),
                    'subject_name' => $this->_subjectName,
                    'subject_id' => $this->_subjectId,
                    'created_by' => $this->getService('User')->getCurrentUserId(),
                ));

                if ($tags = $form->getParam('tags')) {
                    $this->getService('Tag')->update($tags, $blogObj->id, HM_Tag_Ref_RefModel::TYPE_BLOG);
                }

                $this->getService('EventDispatcher')->notify(
                    new sfEvent($blogServ, get_class($blogServ).'::esPushTrigger', array('item' => $blogObj))
                ); 

                $this->_flashMessenger->addMessage(_('Запись опубликована'));
                $this->_redirector->gotoSimple('index', 'index', 'blog', array('subject' => $this->_subjectName, 'subject_id' => $this->_subjectId));

            } else {
                $tagServ = $this->getService('Tag');
                $values = $request->getParams();
                if($this->_request->getParam('tags')) {
                    $values['tags'] = $this->_request->getParam('tags');
                    foreach($values['tags'] as $k => $tag) {
                        if(is_numeric($tag)) {
                            $tagObj = $tagServ->getOne($tagServ->find($tag));
                            if($tagObj && $tagObj->id) {
                                $values['tags'][$tagObj->id] = $tagObj->body;
                            }
                        } else {
                            $values['tags'][$tag] = $tag;
                        }
                        unset($values['tags'][$k]);
                    }
                }
                $values['body'] = stripslashes($this->_request->getParam('body'));
                $form->setDefaults($values);
            }
        }

        $this->view->form = $form;
    }

    public function getFilterByRequest(Zend_Controller_Request_Http $request)
    {
        $filter = $this->getService('ESFactory')->newFilter();
        $filter->setUserId((int)$this->getService('User')->getCurrentUserId());
        $subject = $request->getParam('subject_id', null);
        if ($subject !== null) {
            $group = $this->getService('ESFactory')->eventGroup(
                HM_Blog_BlogService::EVENT_GROUP_NAME_PREFIX, (int)$subject    
            );
        } else {
            $group = $this->getService('ESFactory')->eventGroup(
                HM_Blog_BlogService::EVENT_GROUP_NAME_PREFIX, 0
            );
        }
        if ($group->getId() !== null) {
            $filter->setGroupId($group->getId());
        }
        return $filter;
    }

    public function viewAction()
    {
        $blogId = (int) $this->_getParam('blog_id', 0);

        $blogServ = $this->getService('Blog');
        $blogPost = $blogServ->getOne($blogServ->find($blogId));
        if(!$blogPost->id) {
            $this->_redirector->gotoSimple('index', 'index', 'blog', array(
                'subject' => $this->_subjectName,
                'subject_id' => $this->_subjectId
            ));
        }

        $form = new HM_Form_Comment();
        $form->setAction($this->view->url(array(
            'module' => 'blog',
            'controller' => 'index',
            'action' => 'view',
            'subject' => $this->_subjectName,
            'subject_id' => $this->_subjectId,
            'blog_id' => $blogId
        ), null, true));
        if ($this->_request->isPost()) {
            if ($form->isValid($this->_request->getParams())) {
                $comment = new HM_Comment_CommentModel();
                $comment->user_id = $this->getService('User')->getCurrentUserId();
                $comment->item_id = $blogId;
                $comment->message = $form->getValue('message');
                $comment = $blogServ->insertActivityComment($comment);

                $this->_flashMessenger->addMessage(_('Комментарий успешно добавлен'));
                $this->_redirector->gotoSimple('view', 'index', 'blog', array(
                    'subject' => $this->_subjectName,
                    'subject_id' => $this->_subjectId,
                    'blog_id' => $blogId
                ));

                // $this->_redirector->gotoUrl($this->view->url(array(
                // 'module' => 'blog',
                // 'controller' => 'index',
                // 'action' => 'view',
                // 'subject' => $this->_subjectName,
                // 'subject_id' => $this->_subjectId,
                // 'blog_id' => $blogId
                // )).'#comment_'.$comment->id
                // );
            }
        }
        $this->view->form = $form;
        $config = Zend_Registry::get('config');
        $this->view->headLink()->appendStylesheet($config->url->base.'css/blog.css');
        $author = $this->getService('User')->find($blogPost->created_by)->current();
        $blogPost->author_avatar = $config->url->base.$author->getPhoto();
        $blogPost->author = $author->getName();
        $blogPost->comments = $blogServ->fetchAllActivityComments($blogPost->id);

        $this->_sideBar();
        $this->view->blogName = $this->getService('Blog')->getSubjectTitle($this->_subjectName, $this->_subjectId);
        $this->view->isFullView = true;
        $this->view->blogPost = $blogPost;
    }

    public function editAction()
    {
        $blogId = (int) $this->_getParam('blog_id', 0);
        $blogServ = $this->getService('Blog');

        $form = new HM_Form_Blog();
        $form->setAction($this->view->url(array('module' => 'blog', 'controller' => 'index', 'action' => 'edit')));

        if ($this->_request->isPost()) {
            if ($form->isValid($this->_request->getParams())) {
                $this->getService('Blog')->update(array(
                    'title' => $form->getValue('title'),
                    'body' => $form->getValue('body'),
                    'id' => $blogId
                ));

                $this->getService('Tag')->update( $form->getParam('tags',array()), $blogId, HM_Tag_Ref_RefModel::TYPE_BLOG );



                $this->_flashMessenger->addMessage(_('Запись успешно изменена'));
                $this->_redirector->gotoSimple('index', 'index', 'blog', array('subject' => $this->_subjectName, 'subject_id' => $this->_subjectId));
            } else {
                $values = $this->_request->getParams();
                if($this->_request->getParam('tags')) {
                    $values['tags'] = $this->_request->getParam('tags');
                    foreach($values['tags'] as $k => $tag) {
                        $values['tags'][$tag] = $tag;
                        unset($values['tags'][$k]);
                    }
                }
                $blog = $blogServ->getOne($blogServ->findManyToMany('Tag', 'TagRef', $blogId));
                if ($blog && $blog->tag) {
                    foreach($blog->tag as $tag) {
                        if ($tag->item_type != HM_Tag_Ref_RefModel::TYPE_BLOG) continue;
                        if(isset($values['tags'][$tag->body])) {
                            unset($values['tags'][$tag->body]);
                        }
                        $values['tags'][$tag->id] = $tag->body;
                    }
                }
                $values['body'] = stripslashes($this->_request->getParam('body'));
                $form->setDefaults($values);
            }
        } else {
            if ($blogId) {
                $blog = $blogServ->getOne($blogServ->findManyToMany('Tag', 'TagRef', $blogId));
                $values = array();
                if ($blog) {
                    $values = $blog->getValues();
                    $values['body'] = stripslashes($values['body']);
                    $values['tags'] = array();
                    if($blog->tag) {
                        foreach($blog->tag as $tag) {
                            if ($tag->item_type != HM_Tag_Ref_RefModel::TYPE_BLOG) continue;
                            $values['tags'][$tag->id] = $tag->body;
                        }
                    }
                }

                $form->setDefaults($values);
            }
        }

        $this->view->form = $form;
    }

    public function deleteAction()
    {
        $id = $this->_getParam('blog_id', 0);
        if ($id) {
            $this->getService('Blog')->delete($id);
        }

        $this->_flashMessenger->addMessage(_('Запись успешно удалена'));
        $this->_redirector->gotoSimple('index', 'index', 'blog', array('subject' => $this->_subjectName, 'subject_id' => $this->_subjectId));
    }


    public function deleteByAction()
    {
        $ids = explode(',', $this->_request->getParam('postMassIds_grid_blog'));
        foreach ($ids as $value) {
            $value = (int)$value;
            if($value) {
                $this->getService('Blog')->delete($value);
            }
        }
        $this->_flashMessenger->addMessage(_('Записи успешно удалены'));
        $this->_redirector->gotoSimple('index', 'index', 'blog', array('subject' => $this->_subjectName, 'subject_id' => $this->_subjectId));
    }

    private function _saveTags($blogId)
    {
        $tagServ = $this->getService('Tag');

        $blogServ = $this->getService('Blog');
        $tags = $this->getRequest()->getParam('tags');

        $blog = $blogServ->findManyToMany('Tag', 'TagRefBlog', $blogId)->current();
        foreach($blog->tags as $tg) {
            if(!in_array($tg->id, $tags)) {
                $blogServ->removeTag($blogId, $tg);
            }
        }

        foreach($tags as $tag) {
            $tagObj = null;
            if(is_numeric($tag)) {
                $tagObj = $tagServ->getOne($tagServ->find($tag));
            } else {
                if ($this->getRequest()->isXmlHttpRequest()) {
                    $tag = iconv("UTF-8", Zend_Registry::get('config')->charset, $tag);
                }
                $tagObj = $tagServ->getOne($tagServ->fetchAll(
                    $tagServ->getTagCondition($this->_subjectId, $this->_subjectName, $tag)
                ));
            }
            if(!isset($tagObj->id)) {
                $tagObj = $tagServ->insert(array(
                    'body' => $tag,
                    'subject_name' => $this->_subjectName,
                    'subject_id' => $this->_subjectId,
                ));
            }
            if(!$blogServ->hasTag($blogId, $tagObj->id)) {
                $blogServ->addTag($blogId, $tagObj->id, $tagObj->rating);
            }
        }
    }

    private function _sideBar()
    {
        $blogServ = $this->getService('Blog');

        /*$blogs = $blogServ->fetchAll($blogServ->getBlogSelect($this->_subjectId, $this->_subjectName));
        $tags = $this->getService('Tag')->getTags(HM_Tag_TagModel::ITEM_TYPE_RESOURCE);
        $min = 1000;
        $max = 0;
        foreach ($tags as $tag) {
            if ($tag->rating > $max) {
                $max = $tag->rating;
            }
            if ($tag->rating < $min) {
                $min = $tag->rating;
            }
        }
        foreach ($tags as $tag) {
            $p = $max - $min;
            if ($p == 0) {
                $p = 1;
            }
            $percent = round(100 * ($tag->rating - $min) / $p);
            $tag->percent = $percent;
            $tag->num = round($percent * 0.09);
        }*/

        $tags = $this->getService('Tag')->getTagsRating(HM_Tag_Ref_RefModel::TYPE_BLOG, $this->_subjectId, $this->_subjectName);
        $this->view->cloudTags = $tags;
        $this->view->archiveDates = $this->getService('Blog')->getArchiveDates($this->_subjectId, $this->_subjectName);
        $this->view->authors = $this->getService('Blog')->getAuthors($this->_subjectId, $this->_subjectName);
    }
}
