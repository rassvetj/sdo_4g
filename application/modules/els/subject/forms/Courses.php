<?php
class HM_Form_Courses extends HM_Form
{

    public function init()
    {
        
        $this->setMethod(Zend_Form::METHOD_POST);
        
        $this->setName('edit-courses');
        
        $this->addElement('hidden', 'cancelUrl', array(
            'Required' => false,
            'Value' => $this->getView()->url(
                array(
                    'module' => 'subjects',
                    'controller' => 'index',
                    'action' => 'index',
                    'subject_id' => $this->getParam('subject_id', 0)
                )
            )
        )
        );
        
        $this->addElement('hidden', 'subject_id', array(            
            'Required' => true,
            'Validators' => array(
                'Int'
            ),
            'Filters' => array(
                'Int'
            )
        ));
        
        $courses = array();

        $this->addElement('UiMultiSelect', 'courses',
            array(
                'Label' => _('Учебные модули'),
                'Required' => false,
                'jQueryParams' => array(
                    'remoteUrl' => $this->getView()->url(array('module' => 'subject', 'controller' => 'index', 'action' => 'courses-list'))
                ),
                'multiOptions' => $courses,
                'class' => 'multiselect'
            )
        );
        
        $this->addElement('Submit', 'submit', array(            
            'Label' => _('Сохранить')
        ));

        $this->addDisplayGroup(array(
            'cancelUrl',
            'subject_id',
            'courses',
            'submit'),
            'groupCourses',
            array(
            'legend' => _('Учебные модули')
            ));
        
        parent::init(); // required!
    }

}