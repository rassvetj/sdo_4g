<?php
class HM_Form_Lesson extends HM_Form_Multi
{

    public function init()
    {
        $subject = $this->getService('Subject')->getOne($this->getService('Subject')->find($this->getParam('subject_id', 0)));

        $this->setMethod(Zend_Form::METHOD_POST);
        $this->addSubForm(new HM_Form_LessonStep1(), 'step1');
        $form2 = new HM_Form_LessonStep2();
        if ($form2->getElements()) {
            $this->addSubForm($form2, 'step2');
        }
        $form3 = new HM_Form_LessonStep3();
        if (!$form2->getElements()) {
            $form3->setDefault('prevSubForm', 'step1');
        }
        $this->addSubForm($form3, 'step3');
        return parent::init();
    }


}
