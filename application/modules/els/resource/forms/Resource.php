<?php
class HM_Form_Resource extends HM_Form_Multi
{

    public function init()
    {
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setAttrib('enctype', 'multipart/form-data');

        if($this->getParam('action', '') == 'new' || $this->getParam('action', '') == 'edit'){
            $this->addSubForm(new HM_Form_ResourceStep1(), 'resourceStep1');
        }

        if($this->getParam('action', '') == 'new' || $this->getParam('action', '') == 'edit-content'){
            $form2 = new HM_Form_ResourceStep2();
            //if ($form2->getElements()) {
            $this->addSubForm($form2, 'resourceStep2');

        }

        return parent::init();

    }


}