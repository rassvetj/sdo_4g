<?php
class HM_Form_ClassifierStep2 extends HM_Form_SubForm{

    public function init(){

        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('classifierStep2');

        $session = $this->getSession();
        $classifierElements = $this->addClassifierElements(
            HM_Classifier_Link_LinkModel::TYPE_STRUCTURE,
            $this->getParam('org_id', 0),
            '',
            $session['classifierStep1']['classifiers_types']
        );
        //$this->addClassifierDisplayGroup($classifierElements);

        if ($classifierElements) {
            $this->addDisplayGroup(
                $classifierElements,
                'classifiers',
                array('legend' => _('Функции'))
            );
        }

        $this->addElement(
            'Submit',
            'submit',
            array(
                 'Label' => _('Сохранить')
            ));

        $this->addElement('hidden', 'prevSubForm', array(
            'Required' => false,
            'Value' => 'classifierStep1'
        ));

        $this->addElement('hidden', 'cancelUrl', array(
            'Required' => false,
            'Value' => $this->getView()->url(array(
                                                  'module' => 'orgstructure',
                                                  'controller' => 'list',
                                                  'action' => 'index',
                                                  'org_id' => null
                                             ), null, true)
        ));
        
        parent::init(); // required!

    }

}