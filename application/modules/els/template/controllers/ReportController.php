<?php

class Template_ReportController extends HM_Controller_Action
{
    public function indexAction()
    {
        $form = new HM_Form_Report();

        $request = $this->getRequest();

        if ($request->isPost() && $form->isValid($request->getPost())) {
            $this->getService('Option')->setOption('template_report_header', $form->getValue('template_report_header'));
            $this->getService('Option')->setOption('template_report_footer', $form->getValue('template_report_footer'));

            $this->_flashMessenger->addMessage(_('Шаблоны отчётов сохранены'));
            $this->_redirector->gotoSimple('index', 'report', 'template');

        } else {

            $form->setDefaults(
                array(
                    'template_report_header' => $this->getService('Option')->getOption('template_report_header'),
                    'template_report_footer' => $this->getService('Option')->getOption('template_report_footer'),
                )
            );
        }

        $this->view->form = $form;
    }
}