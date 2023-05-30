<?php

class Message_AjaxController extends HM_Controller_Action
{
    public function lessonCallbackAction()
    {
        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        //$this->getHelper('viewRenderer')->setNoRender();
        $lessonId = $this->_getParam('lesson_id', 0);
        $form = new HM_Form_AjaxMessage();
        $form->getElement('message')->setAttribs(
            array('cols' => '55','rows' => '16')
        );
        $this->view->success = false;
        $request = $this->getRequest();
        if ($request->isPost() && $this->_hasParam('message')) {
            if ($form->isValid($request->getParams())) {
                $lesson = $this->getOne($this->getService('Lesson')->find($lessonId));
                if ($lesson) {
                    $message = $form->getValue('message');
                    $messenger = $this->getService('Messenger');
                    $messenger->setTemplate(HM_Messenger::TEMPLATE_PRIVATE);
                    $messenger->send($this->getService('User')->getCurrentUserId(),
                        $lesson->createID,
                        array('TEXT' => _('Обратная связь по занятию '.$lesson->title.': ').$message,
                        )
                    );
                }
                $this->view->success = true;
            }
        }
        $view = $this->view;
        $view->form = $form;
    }
}