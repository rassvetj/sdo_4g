<?php

class Template_OrderController extends HM_Controller_Action
{
    public function indexAction()
    {
        $form = new HM_Form_Order();

        $request = $this->getRequest();

        if ($request->isPost() && $form->isValid($request->getPost())) {
            $this->getService('Option')->setOption('template_order_header', $form->getValue('template_order_header'));
            $this->getService('Option')->setOption('template_order_text', $form->getValue('template_order_text'));
            $this->getService('Option')->setOption('template_order_footer', $form->getValue('template_order_footer'));

            $this->_flashMessenger->addMessage(_('Шаблоны приказов сохранены'));
            $this->_redirector->gotoSimple('index', 'order', 'template');

        } else {

            $form->setDefaults(
                array(
                    'template_order_header' => $this->getService('Option')->getOption('template_order_header'),
                    'template_order_text' => $this->getService('Option')->getOption('template_order_text'),
                    'template_order_footer' => $this->getService('Option')->getOption('template_order_footer'),
                )
            );
        }

        $this->view->form = $form;
    }
}