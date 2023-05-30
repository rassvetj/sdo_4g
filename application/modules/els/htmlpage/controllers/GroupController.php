<?php
class Htmlpage_GroupController extends HM_Controller_Action_Crud{

    public function init() {
        $this->_setForm(new HM_Form_GroupPage());
        parent::init();
    }

    public function indexAction(){
        $this->_redirector->gotoSimple('index', 'list', 'htmlpage');
    }
    
    public function update(Zend_Form $form) {

        $groupId = (int) $this->_request->getParam('group_id', 0);
        $page = $this->getService('HtmlpageGroup')->update(
            array(
			    'group_id' => $groupId,
                'name' => $form->getValue('name'),
                'role' => $form->getValue('role'),
                'ordr' => $form->getValue('ordr'),
            )
        );
        
    }

	public function setDefaults(Zend_Form $form)
	{
		$groupId = (int) $this->_request->getParam('group_id', 0);
		$group = $this->getService('HtmlpageGroup')->getOne($this->getService('HtmlpageGroup')->find($groupId));
		if (group)
		{
			$values = $group->getValues();
			$form->populate($values);
			//$form->setDefaults($subject->getValues());
		}
	}
	
    public function delete($id) {
        $this->getService('HtmlpageGroup')->delete($id);
    }


    public function create(Zend_Form $form) {

        $group = $this->getService('HtmlpageGroup')->insert(
            array(
                'name' => $form->getValue('name'),
                'role' => $form->getValue('role'),
                'ordr' => $form->getValue('ordr'),
            ),
            0
        );
        
        if($group)
            $page = $this->getService('Htmlpage')->insert(
                array(
                    'group_id' => $group->group_id,
                    'name' => $group->name, 
                    'ordr' => HM_Htmlpage_HtmlpageModel::ORDER_DEFAULT, 
                )
            );
        
        if($page){
                $this->_flashMessenger->addMessage(_('Группа страниц успешно создана'));
                $this->_redirector->gotoSimple('index', 'list', 'htmlpage');
        }
        
    }
    
    public function newAction(){
    	
    	parent::newAction();
        $role = $this->_request->getParam('role');
        $form = $form = $this->_getForm();
        $form->setDefault('role', $role);
        $this->view->form = $form;
        
    }
    
}