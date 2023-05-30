<?php
class HM_Form_InstantSend extends HM_Form
{
	public function init()
	{
        $this->setMethod(Zend_Form::METHOD_POST);
        //$this->setAttrib('enctype', 'multipart/form-data');
        //$this->setAttrib('onSubmit', "select_list_select_all('list2');");
        $this->setName('resource');
        $this->setAction($this->getView()->url());

        $this->addElement('hidden', 'cancelUrl', array(
            'required' => false,
            'value' => $this->getView()->url(array('module' => 'message', 'controller' => 'view', 'action' => 'index'))
        ));

/*        $this->addElement('hidden', 'users', array(
            'Required' => true,
            'Validators' => array(),
            'Filters' => array()
        ));*/

        $this->addElement('hidden', 'subject', array(
            'required' => false,
            'filters' => array(
                'StripTags'
            )
        ));

        $this->addElement('hidden', 'subject_id', array(
            'required' => false,
            'filters' => array(
                'Int'
            )
        ));


//#16837
        $addresatModeAjax = trim($this->getParam('subject', ''))=="";
        if($addresatModeAjax){
            $this->addElement(new HM_Form_Element_FcbkComplete('users', array(
                    'required' => true,
                    'Label' => _('Кому'),
                    'Description' => _('Для поиска можно вводить любое сочетание букв из фамилии, имени и отчества'),
                    'json_url' => $this->getView()->url(array('module' => 'user', 'controller' => 'ajax', 'action' => 'users-list', 'subject_id' => intval($this->getParam('subject_id', '')) ), null, true),
                    'newel' => false
                )
            ));
        } else {
             $this->addElement('UiMultiSelect', 'users',
                array(
                    'Label' => _('Кому'),
                    'Required' => true,
                    'Validators' => array(
                        'Int'
                    ),
                    'Filters' => array(
                    'Int'
                    ),
                    'class' => 'multiselect'
                )
            );
        }
//

        $this->addElement($this->getDefaultWysiwygElementName(), 'message', array(
            'Label' => _('Сообщение'),
            'Required' => true,
            'Validators' => array(
                array('StringLength',255,3)
            ),
            'Filters' => array('HtmlSanitizeRich'),
        ));

        //$this->getElement('message')->addFilter(new HM_Filter_Utf8());

		$this->addElement('Submit', 'submit', array('Label' => _('Отправить')));

        $this->addDisplayGroup(
            array(
                'cancelUrl',
                'subject',
                'subject_id',
                'users',
                'message',
                'submit'
            ),
            'messageGroup',
            array('legend' => _('Сообщение'))
        );

        parent::init(); // required!
	}

}