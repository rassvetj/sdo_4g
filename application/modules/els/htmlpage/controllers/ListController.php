<?php
class Htmlpage_ListController extends HM_Controller_Action_Crud{

    public function init() {
        $this->_setForm(new HM_Form_Page());
        parent::init();
    }
    
    public function indexAction()
    {
        // $key может быть ролью или group_id при $type=true и page_id при $type=false
        // теперь $key может быть ролью при текстовом виде, group_id или page_id при числовом
        // если роль - выводим список групп, 
        // если группа - список страниц, 
        // если страница - форму редактирования
        $type = $this->_getParam('type', 1);
        $key = $this->_getParam('key', 0);
        
        //$field = (!$type || $type == 'false') ? 'page_id' : ((!is_numeric($key)) ? 'role' : 'group_id');
        $field = (!is_numeric($key)) ? 'role' : (($type != 'false') ? 'group_id' : 'page_id');
        
        switch ($field){
        	case 'group_id': $grid = $this->getPagesGrid($key); break;
        	case 'role':     $grid = $this->getGroupsGrid($key); break;
        	case 'page_id':  $grid = $this->getPagesGrid($key, true); break;
        					 //$this->_request->setParam('htmlpage_id', $key); // $this->setDefaults
	        				 //$this->editAction(); 
	        				 break;
        }
        
        if (!$this->isAjaxRequest()) {
            $tree = $this->getService('HtmlpageGroup')->getTreeContent();
        }
        
        if($grid) $this->view->grid = $grid;
        
        $this->view->field = $field;
        $this->view->key = $key;
        $this->view->tree = $tree;
        $this->view->gridAjaxRequest = $this->isAjaxRequest();
    }
    
    public function getPagesGrid($key = 0, $page_id = false){
    	
        $select = $this->getService('Htmlpage')->getSelect();
        $select->from('htmlpage', array('page_id', 'name', 'ordr'));
        if($page_id)
        	$select->where('page_id = ?', $key);
        else
        	$select->where('group_id = ?', $key);

        $grid = $this->getGrid(
            $select,
            array(
                'page_id' => array('hidden' => true),
                'name' => array('title' => _('Название')),
                'ordr' => array('title' => _('Порядок следования')),
            ),
            array(
                'name' => null
            )
        );

        $grid->addAction(array(
            'module' => 'htmlpage',
            'controller' => 'list',
            'action' => 'edit',
            'key' => null,
        	'type' => null
        ),
            array('page_id'),
            $this->view->icon('edit')
        );

        $grid->addAction(array(
            'module' => 'htmlpage',
            'controller' => 'list',
            'action' => 'delete',
            'key' => null,
        	'type' => null
        ),
            array('page_id'),
            $this->view->icon('delete')
        );

        $grid->addMassAction(array(
            'module' => 'htmlpage',
            'controller' => 'list',
            'action' => 'delete-by'
        ),
            _('Удалить'),
            _('Вы уверены?')
        );

        $this->view->addAction = !$page_id;
        return $grid->deploy();
    	
    }
    
    public function getGroupsGrid($role){
    	
        $select = $this->getService('HtmlpageGroup')->getSelect();
        $select->from('htmlpage_groups', array('group_id', 'name', 'ordr'));
        $select->where('role = ?', $role);

        $grid = $this->getGrid(
            $select,
            array(
                'group_id' => array('hidden' => true),
                'name' => array('title' => _('Название')),
                'ordr' => array('title' => _('Порядок следования')),
            ),
            array(
                'name' => null
            )
        );

        $grid->addAction(array(
            'module' => 'htmlpage',
            'controller' => 'group',
            'action' => 'edit',
            'key' => null,
        	'type' => null
        ),
            array('group_id'),
            $this->view->icon('edit')
        );

        $grid->addAction(array(
            'module' => 'htmlpage',
            'controller' => 'group',
            'action' => 'delete',
            'key' => null,
        	'type' => null
        ),
            array('group_id'),
            $this->view->icon('delete')
        );

        $grid->addMassAction(array(
            'module' => 'htmlpage',
            'controller' => 'group',
            'action' => 'delete-by'
        ),
            _('Удалить'),
            _('Вы уверены?')
        );

        $this->view->addAction = 1;
        return $grid->deploy();
    	
    }

    public function update(Zend_Form $form) {

        $page = $this->getService('Htmlpage')->update(
            array(
			    'page_id' => $form->getValue('page_id'),
                'name' => $form->getValue('name'),
                'ordr' => $form->getValue('ordr'),
                'url' => $form->getValue('url'),
                'text' => $form->getValue('text'),
                'translation' => $form->getValue('translation'),
                'nametranslation' => $form->getValue('nametranslation')
            )
        );
        
    }

	public function setDefaults(Zend_Form $form)
	{
		$pageId = (int) $this->_request->getParam('page_id', 0);
		$page = $this->getService('Htmlpage')->getOne($this->getService('Htmlpage')->find($pageId));
		if ($page)
		{
			$values = $page->getValues();
			$form->populate($values);
			//$form->setDefaults($subject->getValues());
		}
	}
	
    public function delete($id) 
    {
        $this->getService('Htmlpage')->delete($id);
    }


    public function create(Zend_Form $form) 
    {
        $page = $this->getService('Htmlpage')->insert(
            array(
                'group_id' => $form->getValue('group_id'),
                'name' => $form->getValue('name'),
                'ordr' => $form->getValue('ordr'),
                'url' => $form->getValue('url'),
                'text' => $form->getValue('text'),
				'translation' => $form->getValue('translation'),
				'nametranslation' => $form->getValue('nametranslation')
            )
        );
    }
    
    public function newAction()
    {
    	parent::newAction();
        $group_id = $this->_request->getParam('group_id');
        $form = $form = $this->_getForm();
        $form->setDefault('group_id', $group_id);
        $this->view->form = $form;
        
    }
}