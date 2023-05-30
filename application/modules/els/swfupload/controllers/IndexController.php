<?php

class Swfupload_IndexController extends HM_Controller_Action
{
    public function indexAction()    
    {
        $request = $this->getRequest();

        $form = new HM_Form_Test();

        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {
                if ($form->file1->isUploaded()) {
                    pr('uploaded');
                    if ($form->file1->isReceived()) {
                        pr('received');
                    }
                    pr($form->file1->getFileName());
                }
                pr('done');
                die();
            }
            pr($form->getMessages());
            pr('none');
            die();
        }

        $this->view->form = $form;
    }
}