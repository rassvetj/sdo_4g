<?php
class HM_Form_Classifier extends HM_Form_Multi
{

    public function init()
    {
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->addSubForm(new HM_Form_ClassifierStep1(), 'classifierStep1');
        $form2 = new HM_Form_ClassifierStep2();
        if ($form2->getElements()) {
            $this->addSubForm($form2, 'classifierStep2');
        }

        return parent::init();


    }


}