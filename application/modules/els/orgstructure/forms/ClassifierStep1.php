<?php
class HM_Form_ClassifierStep1 extends HM_Form_SubForm{

    public function init(){

        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('classifierStep1');
        $services = Zend_Registry::get('serviceContainer');
        $orgId = $this->getParam('org_id', 0);

        // all classifiers types are available for orgstructure item
        $classifier_types = $services->getService('ClassifierType')->getClassifierTypes(HM_Classifier_Link_LinkModel::TYPE_STRUCTURE);

        // this hell for find default $classifier_types (which already linked with $orgId)
		if (!empty($classifier_types)) {
			$types = $classifier_types->getList('type_id', 'type_id');
			if (!empty($types)) {
		        $classifiers = $services->getService('Classifier')->fetchAll(
		            array('type IN (?)' => $types)
		        );
			}
		}
		if (count($classifiers)) {
	        $current_classifiers = $services->getService('ClassifierLink')->fetchAll(array(
	                                                                                      'classifier_id IN (?)' => $classifiers->getList('classifier_id', 'classifier_id'),
	                                                                                      'item_id = ?' => $orgId,
	                                                                                      'type = ?' => HM_Classifier_Link_LinkModel::TYPE_STRUCTURE
	                                                                                 ))->getList('classifier_id', 'classifier_id');
	        $current_types = array_intersect_key($classifiers->getList('classifier_id', 'type'), $current_classifiers);
		}
        // end of hell
        $this->addElement('hidden', 'org_id', array('value' => $orgId));
        $this->addElement('UiMultiSelect', 'classifiers_types',
                          array(
                               'Label' => '',
                               'Required' => false,
                               'Filters' => array(
                                   'Int'
                               ),
                               'multiOptions' => $classifier_types->getList('type_id', 'name'),
                               'value' => $current_types,
                               'class' => 'multiselect'
                          ));

        $this->addElement(
            'Submit',
            'submit',
            array(
                 'Label' => _('Далее')
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

        $this->addDisplayGroup(
            array('org_id', 'classifiers_types', 'submit'),
            'classifiersGroup',
            array('legend' => _('Функции'))
        );

        parent::init(); // required!

    }

}