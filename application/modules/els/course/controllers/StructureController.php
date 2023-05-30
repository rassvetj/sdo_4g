<?php
class Course_StructureController extends HM_Controller_Action
{
    private $_subject = null;
    private $_course = null;

    private $_idName = '_zfgId';

    /**
     * @var HM_Course_Item_ItemService
     */
    protected $_defaultService = null;

    public function init()
    {
        parent::init();

        $this->_helper->ContextSwitch()->setAutoJsonSerialization(true)->addActionContext('get-tree-branch', 'json')->initContext('json');
        $this->_helper->ContextSwitch()->setAutoJsonSerialization(true)->addActionContext('delete-tree-branch', 'json')->initContext('json');
        $this->_helper->ContextSwitch()->setAutoJsonSerialization(true)->addActionContext('move', 'json')->initContext('json');

        $this->setDefaultService($this->getService('CourseItem'));

        $subjectId = (int) $this->_getParam('subject_id', 0);
        $courseId = (int) $this->_getParam('course_id', 0);
		
		$request = Zend_Controller_Front::getInstance()->getRequest();
		$this->_currentLang = $request->getCookie(HM_User_UserService::COOKIE_NAME_LANG);

        if ($subjectId > 0) {
            $subject = $this->_getSubject();

            $this->view->setExtended(
                array(
                    'subjectName' => 'Subject',
                    'subjectId' => $subjectId,
                    'subjectIdParamName' => 'subject_id',
                    'subjectIdFieldName' => 'subid',
                    'subject' => $subject
                )
            );

            if ($courseId > 0) {
                $course = $this->_getCourse();
                if ($course) {
                    $this->getService('Unmanaged')->setSubHeader($course->Title);                    
                }
            }
        }

        if (!$subjectId && ($courseId > 0)) {
            $course = $this->_getCourse();

            $this->view->setExtended(
                array(
                    'subjectName' => 'Course',
                    'subjectId' => $courseId,
                    'subjectIdParamName' => 'course_id',
                    'subjectIdFieldName' => 'CID',
                    'subject' => $course
                )
            );

            if ($course) {
                $this->getService('Unmanaged')->setSubHeader($course->Title);
            }

        }
    }

    private function _getSubject()
    {
        if (null === $this->_subject) {
            $subjectId = (int) $this->_getParam('subject_id', 0);
            if ($subjectId > 0) {
                $this->_subject = $this->getOne($this->getService('Subject')->find($subjectId));
            }
        }

        return $this->_subject;
    }

    private function _getCourse()
    {
        if (null === $this->_course) {
            $courseId = (int) $this->_getParam('course_id', 0);
            if ($courseId > 0) {
                $this->_course = $this->getOne($this->getService('Course')->find($courseId));
            }
        }
        return $this->_course;
    }

    private function _getHeadActions()
    {
        return $this->view->actions(
            'course-content',
            array(
                 array(
                     'title' => _('Создать раздел'),
                     'url' => $this->view->url(array('module' => 'course', 'controller' => 'structure', 'action' => 'new-section', 'key' => $this->_getParam('key', 0), 'subject_id' => $this->_getParam('subject_id', 0), 'course_id' => $this->_getParam('course_id', 0)), null, true)
                 ),
                 array(
                     'title' => _('Создать ресурс'),
                     'url' => $this->view->url(array('module' => 'resource', 'controller' => 'list', 'action' => 'new', 'key' => $this->_getParam('key', 0), 'subject_id' => $this->_getParam('subject_id', 0), 'course_id' => $this->_getParam('course_id', 0)), null, true)
                 ),
                 array(
                     'title' => _('Подключить раздел'),
                     'url' => $this->view->url(array('module' => 'course', 'controller' => 'section', 'action' => 'index', 'key' => $this->_getParam('key', 0), 'subject_id' => $this->_getParam('subject_id', 0), 'course_id' => $this->_getParam('course_id', 0)), null, true)
                 ),
                 array(
                     'title' => _('Подключить ресурс'),
                     'url' => $this->view->url(array('module' => 'resource', 'controller' => 'list', 'action' => 'items', 'key' => $this->_getParam('key', 0), 'subject_id' => $this->_getParam('subject_id', 0), 'course_id' => $this->_getParam('course_id', 0)), null, true)
                 )
            )
        );
    }

    public function indexAction()
    {
        $subjectId = (int) $this->_getParam('subject_id', 0);
        $courseId = (int) $this->_getParam('course_id', 0);
        $key = (int) $this->_getParam('key', 0);

        $children = $this->_defaultService->getChildrenLevel($courseId, $key);

        $childrenArray = array();

        if (count($children)) {
            foreach($children as $child) {
                $childrenArray[] = array(
                    $this->_idName => $child->oid,
                    'oid'          => $child->oid,
                    'name'         => $child->title,
					'name_translation'         => $child->title_translation,
                    'type'         => 0,
                    'vol1'         => $child->vol1,
                    'vol2'         => $child->vol2,
                    'mod'          => $child->module
                );
            }
        }

        $grid = $this->getGrid($childrenArray, array(
                $this->_idName => array(),
                'oid' => array('hidden' => true),
                'name' => array(
                    'title' => _('Название'),
                    'callback' => array(
                        'function' => array($this, 'updateName'),
                        'params' => array('{{oid}}', '{{name}}', '{{vol1}}', '{{vol2}}', '{{mod}}', '{{name_translation}}')
                    )
                ),
				'name_translation' => array('hidden' => true), 
                'type' => array(
                    'title' => _('Тип ресурса'),
                    'callback' => array(
                        'function' => array($this, 'updateType'),
                        'params' => array('{{vol2}}')
                    )
                ),
                'vol1' => array('hidden' => true),
                'vol2' => array('hidden' => true),
                'mod' => array('hidden' => true)
            ),
            array(
                'title' => null,
            )
        );

        $grid->addAction(array(
            'module' => 'course',
            'controller' => 'structure',
            'action' => 'section'
        ),
            array($this->_idName),
            $this->view->icon('edit')
        );

        $grid->addAction(array(
            'module' => 'course',
            'controller' => 'structure',
            'action' => 'delete'
        ),
            array($this->_idName),
            $this->view->icon('delete', _('Удалить из структуры')),
            _('Вы действительно желаете удалить выбранный элемент из структуры модуля? При этом соответствующий информационный ресурс не будет удалён и Вы сможете использовать его в дальнейшем.')
        );

/*        $grid->addAction(array(
            'module' => 'course',
            'controller' => 'structure',
            'action' => 'delete-force'
        ),
            array($this->_idName),
            $this->view->icon('delete', _('Удалить')),
            _('Вы действительно желаете удалить выбранный элемент? При этом будет удалён вложенный информационный ресурс и Вы не сможете использовать его в дальнейшем.')
        );*/

        $grid->addMassAction(array(
            'module' => 'course',
            'controller' => 'structure',
            'action' => 'delete-by'
        ),
            _('Удалить из структуры'),
            _('Вы действительно желаете удалить выбранные элементы из структуры модуля? При этом соответствующие информационные ресурсы не будут удалены и Вы сможете использовать их в дальнейшем.')
        );

/*        $grid->addMassAction(array(
            'module' => 'course',
            'controller' => 'structure',
            'action' => 'delete-force-by'
        ),
            _('Удалить'),
            _('Вы действительно желаете удалить выбранные элементы? При этом будут удалены все вложенные информационные ресурсы и Вы не сможете использовать их в дальнейшем.')
        );*/

        if (!$this->isAjaxRequest()) {
            //$tree = $this->_defaultService->getTreeContent($courseId, array($key), 0, 0, true);
            $this->_defaultService->addOpenedBranch($courseId, $key);

            $path = $this->_defaultService->getBranchPath($courseId, $key);

            if (count($path)) {
                foreach($path as $step) {
                    $this->_defaultService->addOpenedBranch($courseId, $step);
                }
            }

            $tree = $this->_defaultService->getTreeContent($courseId, $this->_defaultService->getOpenedBranch($this->_getCourse()->CID), 0, 0, true);

            $tree = array(
                0 => array(
                    'title' => $this->_getCourse()->Title,

				/*	array(
                        'callback' => array(
                        'function' => array($this, 'updateTitle'),
                        'params' => array('{{title}}','{{title_translation}}')
                    )
                ),*/
					
                    'count' => 0,
                    'key' => 0,
                    'isLazy' => true,
                    'isFolder' => true,
                    'expand' => true
                ),
                1 => $tree
            );

            $this->view->tree = $tree;

        }

        $this->view->key             = $key;
        $this->view->course          = $this->_getCourse();
        $this->view->subjectId       = $subjectId;
        $this->view->courseId        = $courseId;
        $this->view->grid            = $grid->deploy();
        $this->view->gridAjaxRequest = $this->isAjaxRequest();
        $this->view->headActions     = $this->_getHeadActions();
        $this->view->treeajax        = $this->_getParam('treeajax', 'none');
    }

    public function updateName($id, $name, $vol1, $vol2, $module, $name_translation='')
    {
        $prefix = '<div class="draggable-item">';
        $postfix = '</div>';
        if ($vol1 || $vol2 || $module) {
            if ($vol2 > 0) {
				if ($this->_currentLang == 'eng'&& $name_translation!='')
                return sprintf($prefix.'<span class="icon-item"></span> <a id="item_%d" href="%s">%s</a>'.$postfix, $id, $this->view->url(array('module' => 'resource', 'controller' => 'index', 'action' => 'index', 'subject_id' => $this->_getParam('subject_id', 0), 'course_id' => $this->_getParam('course_id', 0), 'key' => $this->_getParam('key', 0), 'resource_id' => $vol2), null, true), $name_translation);
				else
				return sprintf($prefix.'<span class="icon-item"></span> <a id="item_%d" href="%s">%s</a>'.$postfix, $id, $this->view->url(array('module' => 'resource', 'controller' => 'index', 'action' => 'index', 'subject_id' => $this->_getParam('subject_id', 0), 'course_id' => $this->_getParam('course_id', 0), 'key' => $this->_getParam('key', 0), 'resource_id' => $vol2), null, true), $name);
            }
			if ($this->_currentLang == 'eng'&& $name_translation!='')
            return sprintf($prefix.'<span class="icon-item"></span> %s'.$postfix, $name_translation);
			else
			return sprintf($prefix.'<span class="icon-item"></span> %s'.$postfix, $name);
        }
		if ($this->_currentLang == 'eng' && $name_translation!='')
        return sprintf($prefix.'<span class="icon-folder"></span> <a id="item_%d" href="%s">%s</a>'.$postfix, $id, $this->view->url(array('module' => 'course', 'controller' => 'structure', 'action' => 'index', 'subject_id' => $this->_getParam('subject_id', 0), 'course_id' => $this->_getParam('course_id', 0), 'key' => $id), null, true), $name_translation);
		else
		return sprintf($prefix.'<span class="icon-folder"></span> <a id="item_%d" href="%s">%s</a>'.$postfix, $id, $this->view->url(array('module' => 'course', 'controller' => 'structure', 'action' => 'index', 'subject_id' => $this->_getParam('subject_id', 0), 'course_id' => $this->_getParam('course_id', 0), 'key' => $id), null, true), $name);
    }
    public function updateTitle($name, $name_translation='')
    {
		if ($this->_currentLang == 'eng'&& $name_translation!='')
			/*$tree[0][title]==*/return $name_translation;
		else
			/*$tree[0][title]==*/return $name;
	}
    public function updateType($id)
    {
        if ($id) {
            $types = HM_Resource_ResourceModel::getTypes();
            $resource = $this->getOne($this->getService('Resource')->find($id));
            if ($resource) {
                return $types[$resource->type];
            }
        }
    }

    public function getTreeBranchAction()
    {
        $key = (int) $this->_getParam('key', 0);
        $children = array();
        if ($key > 0) {
            $courseItem = $this->getOne($this->getService('CourseItem')->find($key));
            if ($courseItem) {
                $children = $this->_defaultService->getBranchContent($courseItem->cid, $key, false);//, true); - #17714
            }

            $this->_defaultService->addOpenedBranch($courseItem->cid, $key);
        }

        //$this->_helper->ContextSwitch()->initContext('json');
        if (!is_array($children)) {
            $children = array();
        }
        $this->view->assign($children);
//pr($children);
//die();
    }

    public function deleteTreeBranchAction()
    {
        $key = $this->_getParam('key', 0);
	    if($key > 0) {
            $courseItem = $this->getOne($this->getService('CourseItem')->find($key));
            if ($courseItem) {
                $this->getService('CourseItem')->deleteOpenedBranch($courseItem->cid, $key);
            }
	    }
    }

    public function newSectionAction()
    {
        $form = new HM_Form_Section();

        if ($this->_request->isPost() && $form->isValid($this->_request->getPost())) {

            $key = (int) $form->getValue('key');
            $courseId = (int) $form->getValue('cid');


            $item = $this->_defaultService->append(
                array(
                    'title' => $form->getValue('title'),
					'title_translation' => $form->getValue('title_translation'),
                    'cid' => $courseId,
                    'module' => 0
                ),
                $key
            );

            if ($item) {
                $this->_flashMessenger->addMessage(_('Раздел успешно добавлен'));
            } else {
                $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Ошибка при добавлении раздела.')));
            }

            $this->_redirector->gotoUrl($form->getValue('cancelUrl'));

        } else {
            $form->setDefaults(array(
                                   'key' => $this->_getParam('key', 0),
                                   'cid' => $this->_getParam('course_id', 0),
                                   'subject_id' => $this->_getParam('subject_id', 0)
                               ));

            if (!$this->_request->isPost()) {
                //$form->setDefault('cancelUrl', $_SERVER['HTTP_REFERER']);
                $form->setDefault('cancelUrl', $this->view->url(array(
                                                                    'module' => 'course',
                                                                    'controller' => 'structure',
                                                                    'action' => 'index',
                                                                    'subject_id' => $this->_getParam('subject_id', 0),
                                                                    'course_id' => $this->_getParam('course_id', 0),
                                                                    'key' => $this->_getParam('key', 0)
                                                                ), null, true));
            }
        }

        $this->view->form = $form;
    }

    public function sectionAction()
    {

        $form = new HM_Form_Section();

        if ($this->_request->isPost() && $form->isValid($this->_request->getPost())) {

            $this->_defaultService->update(
                array(
                    'oid' => (int) $form->getValue('oid'),
                    'title' => $form->getValue('title'),
					'title_translation' => $form->getValue('title_translation'),
                )
            );

            $this->_flashMessenger->addMessage(_('Раздел успешно отредактирован'));
            $this->_redirector->gotoUrl($form->getValue('cancelUrl'));

        } else {

            $id = (int) $this->_getParam($this->_idName, 0);

            $section = $this->getOne($this->_defaultService->find($id));

            if ($section) {
                $form->setDefaults(
                    array(
                         'key' => (int) $this->_getParam('key', 0),
                        'oid' => $section->oid,
                        'title' => $section->title,
						'title_translation' => $section->title_translation,
                        'cid' => $section->cid,
                        'subject_id' => (int) $this->_getParam('subject_id', 0)

                    )
                );
            }

            if (!$this->_request->isPost()) {
                //$form->setDefault('cancelUrl', $_SERVER['HTTP_REFERER']);
                $form->setDefault('cancelUrl', $this->view->url(array(
                                                                    'module' => 'course',
                                                                    'controller' => 'structure',
                                                                    'action' => 'index',
                                                                    'subject_id' => $this->_getParam('subject_id', 0),
                                                                    'course_id' => $this->_getParam('course_id', 0),
                                                                    'key' => $this->_getParam('key', 0)
                                                                ), null, true));
            }
        }

        $this->view->form = $form;

    }

    public function deleteAction()
    {
        $id = (int) $this->_getParam($this->_idName, 0);
        $courseId = (int) $this->_getParam('course_id', 0);
        $subjectId = (int) $this->_getParam('subject_id', 0);

        if ($id > 0) {
            $this->_defaultService->delete($id);
            $this->_flashMessenger->addMessage(_('Элемент успешно удалён'));
        }

        $this->_redirector->gotoSimple('index', 'structure', 'course', array('key' => $this->_getParam('key', 0), 'subject_id' => $subjectId, 'course_id' => $courseId));
    }

    public function deleteByAction()
    {
        $ids = explode(',', $this->_request->getParam('postMassIds_grid'));
        $courseId = (int) $this->_getParam('course_id', 0);
        $subjectId = (int) $this->_getParam('subject_id', 0);

        if (count($ids)) {
            foreach($ids as $id) {
                $this->_defaultService->delete($id);
            }
        }

        $this->_flashMessenger->addMessage(_('Элементы успешно удалёны'));
        $this->_redirector->gotoSimple('index', 'structure', 'course', array('key' => $this->_getParam('key', 0), 'subject_id' => $subjectId, 'course_id' => $courseId));

    }

    public function deleteForceAction()
    {
        $id = (int) $this->_getParam($this->_idName, 0);
        $courseId = (int) $this->_getParam('course_id', 0);
        $subjectId = (int) $this->_getParam('subject_id', 0);

        if ($id > 0) {
            $this->_defaultService->delete($id, true, true, $subjectId);
            $this->_flashMessenger->addMessage(_('Элемент успешно удалён'));
        }

        $this->_redirector->gotoSimple('index', 'structure', 'course', array('key' => $this->_getParam('key', 0), 'subject_id' => $subjectId, 'course_id' => $courseId));
    }

    public function deleteForceByAction()
    {
        $ids = explode(',', $this->_request->getParam('postMassIds_grid'));
        $courseId = (int) $this->_getParam('course_id', 0);
        $subjectId = (int) $this->_getParam('subject_id', 0);

        if (count($ids)) {
            foreach($ids as $id) {
                $this->_defaultService->delete($id, true, true, $subjectId);
            }
        }

        $this->_flashMessenger->addMessage(_('Элементы успешно удалёны'));
        $this->_redirector->gotoSimple('index', 'structure', 'course', array('key' => $this->_getParam('key', 0), 'subject_id' => $subjectId, 'course_id' => $courseId));

    }

    public function moveAction()
    {
        $parentId = (int) $this->_getParam('parent', 0);
        $childId  = (int) $this->_getParam('child', 0);

        $this->view->assign('result', false);

        if ($childId == $parentId) return;

        if ($childId > 0) {

            $child = $this->getOne(
                $this->_defaultService->find($childId)
            );

            if ($child) {

                $parent = $this->getOne(
                    $this->_defaultService->find($parentId)
                );

                if ($parent) {
                    $parentLevel = $parent->level;
                    $parentLastChildId = $parent->oid;
                } else {
                    $parentLevel = -1;
                    $parentLastChildId = -1;
                }

                $levelDiff = $child->level - ($parentLevel + 1);

                $parentChildren = $this->_defaultService->getChildrenLevel($child->cid, $parentId, false);
                if (count($parentChildren)) {
                    $parentLastChild = $parentChildren[count($parentChildren)-1];
                    if ($parentLastChild) {
                        $parentLastChildId = $parentLastChild->oid;
                    }
                }


                $childLastChildId = $child->oid;

                $childChildren = $this->_defaultService->getChildrenLevel($child->cid, $child->oid, false);

                // нельзя перемещать самого в себя
                if(array_key_exists($parentId,$childChildren->getList('oid','cid'))) return;

                if (count($childChildren)) {
                    $childLastChild = $childChildren[count($childChildren)-1];
                    if ($childLastChild) {
                        $childLastChildId = $childLastChild->oid;
                    }
                }

                try {
                    $this->_defaultService->getSelect()->getAdapter()->beginTransaction();

                    $this->_defaultService->updateWhere(
                        array('prev_ref' => $child->prev_ref),
                        $this->_defaultService->quoteInto('prev_ref = ?', $childLastChildId)
                    );

                    $this->_defaultService->updateWhere(
                        array('prev_ref' => $childLastChildId),
                        $this->_defaultService->quoteInto('prev_ref = ?', $parentLastChildId)
                    );

                    $this->_defaultService->updateWhere(
                        array('prev_ref' => $parentLastChildId),
                        $this->_defaultService->quoteInto('oid = ?', $child->oid)
                    );

                    $childrenIds = $childChildren->getList('oid', 'oid');
                    $childrenIds[$child->oid] = $child->oid;

                    if (count($childrenIds)) {
                        $this->_defaultService->updateWhere(
                            array('level' => new Zend_Db_Expr(sprintf('level - %d', $levelDiff))),
                            $this->_defaultService->quoteInto('oid IN (?)', $childrenIds)
                        );
                    }

                    $this->getService('Course')->update(array('tree' => '', 'CID' => $child->cid));

                    $this->_defaultService->getSelect()->getAdapter()->commit();

                    $this->view->assign('result', true);
                } catch (Zend_Db_Exception $e) {
                    $this->_defaultService->getSelect()->getAdapter()->rollBack();
                    throw $e;
                }

            }
        }

    }

}