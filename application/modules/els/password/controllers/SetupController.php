<?php

class Password_SetupController extends HM_Controller_Action
{
    
    public function indexAction()
    {
        $form = new HM_Form_Policies();
        
        
        if($this->_getParam('passwordRestriction', 0) == HM_User_Password_PasswordModel::RESTRICTION_FREE){
            $element = $form->getElement('passwordMaxFailedTry');
            if($element){
                $element->removeValidator('GreaterThan');
            }
        }
        
        if ($this->_request->isPost()) {
            if ($form->isValid($this->_request->getParams())) {
                $update = array(
                	'passwordMinLength'       => $form->getValue('passwordMinLength'),
    	        	'passwordMinNoneRepeated' => $form->getValue('passwordMinNoneRepeated'),
    	            'passwordCheckDifficult'  => $form->getValue('passwordCheckDifficult'),
                	'passwordMaxPeriod'       => $form->getValue('passwordMaxPeriod'),
    	        	'passwordMinPeriod'       => $form->getValue('passwordMinPeriod'),
                	'passwordRestriction'     => $form->getValue('passwordRestriction'),
    	            'passwordMaxFailedTry'    => $form->getValue('passwordMaxFailedTry'),
    	        	'passwordFailedActions'   => $form->getValue('passwordFailedActions')
                );
                
                $this->getService('Option')->setOptions($update);
                $this->_flashMessenger->addMessage(_('Настройки парольной политики успешно изменены.'));
                $this->_redirector->gotoSimple('index', 'setup', 'password');
            }else {
                $form->populate($this->_request->getParams());
            }
        }else{
            $default = $this->getService('Option')->getOptions(HM_Option_OptionModel::SCOPE_PASSWORDS);
            $form->populate($default);
        }
        
        $this->view->form = $form;


    }

    
    
}