<?php
/**
 * Контроллер редактирования шаблона серификата 
 * меню "Орг.обуч. -> Настройки -> Шаблон сертификатов"
 * 
 */
class Template_CertificateController extends HM_Controller_Action {
	
	public function indexAction()
    {
        $form = new HM_Form_Cetrificate();
        $request = $this->getRequest();

        if ($request->isPost() && $form->isValid($request->getPost())) {

        	$this->getService('Option')->setOption('template_certificate_text', $form->getValue('template_certificate_text'));
            $this->_flashMessenger->addMessage(_('Шаблон сертификатов сохранен'));
            $this->_redirector->gotoSimple('index', 'certificate', 'template');

        } else {

            $form->setDefaults(
                array(
                    'template_certificate_text' => $this->getService('Option')->getOption('template_certificate_text')
                )
            );
        }

        $this->view->form = $form;
    }
    
    public function previewAction()
    {
        $certificateText = $this->_getParam('template_certificate_text'); // сейчас не работает; нужно для просмотра несохраненного template'а
        $output = $this->getService('Certificates')->createFile(null, true, $certificateText);
        
        $sender = $this->_helper->getHelper('SendFile');
        $oldEncoding = mb_internal_encoding();
		mb_internal_encoding("Windows-1251"); // зачем??        
        
        $sender->SendData(
            $output,
            'application/pdf',
            'certificate.pdf'
        );        
        
        mb_internal_encoding($oldEncoding);
        die();        
    }
}