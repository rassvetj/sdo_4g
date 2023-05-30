<?php

class Contract_IndexController extends HM_Controller_Action
{
    
    public function indexAction()
    {
        $form = new HM_Form_Offer();

        if ( $this->_request->isPost() ) {
            
            if ( $form->isValid($this->_request->getParams()) ) {
                
                $update = array(
//                	'regAllow' => $form->getValue('regAllow'),
                	'regDeny' => $form->getValue('regDeny'),
                	'regRequireAgreement' => $form->getValue('regRequireAgreement'),
                	'regUseCaptcha' => $form->getValue('regUseCaptcha'),
                	'regValidateEmail' => $form->getValue('regValidateEmail'),
                	'regAutoBlock' => $form->getValue('regAutoBlock'),
                	'contractOfferText'       => $form->getValue('contractOfferText'),
    	        	'contractPersonalDataText' => $form->getValue('contractPersonalDataText')
                );
                
                $this->getService('Option')->setOptions($update);
                $this->_flashMessenger->addMessage(_('Обновление регистрационных требований успешно выполнено.'));
                $this->_redirector->gotoSimple('index', 'index', 'contract');
                
            } else {
                $form->populate($this->_request->getParams());
            }
            
        } else {
            
            $default = $this->getService('Option')->getOptions(HM_Option_OptionModel::SCOPE_CONTRACT);
            $form->populate($default);
            
        }
        
        $this->view->form = $form;
    }

    public function viewAction()
    {
        $contract = ($this->_getParam('contract','') == 'offer')? 'contractOfferText' : 'contractPersonalDataText';
        $texts = $this->getService('Option')->getOptions(HM_Option_OptionModel::SCOPE_CONTRACT);
        $this->view->text = $texts[$contract]; 
    }
    
    public function printAction()
    {
        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getResponse()->setHeader('Content-type', 'text/html; charset=' . Zend_Registry::get('config')->charset);
        
        $this->viewAction();
    }
    
}