<?php
class HM_Form_Faq extends HM_Form
{
	public function init()
	{

        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('faq');

        $this->addElement('hidden', 'cancelUrl', array(
            'Required' => false,
            'Value' => $this->getView()->url(array('action' => 'index'))
        ));

        $this->addElement('hidden', 'faq_id', array(
            'Required' => true,
            'Validators' => array('Int'),
            'Filters' => array('Int')
        ));

        $this->addElement('textarea', 'question', array(
            'Label' => _('Вопрос'),
            'Required' => true,
            'Validators' => array(
                array('StringLength', 4000, 1)
            ),
            'Filters' => array(
                'StripTags'
            ),
            'Rows' => 5
        ));

        $this->addElement($this->getDefaultWysiwygElementName(), 'answer', array(
            'Label' => _('Ответ'),
            'Required' => true,
            'Validators' => array(
            ),
            'Filters' => array('HtmlSanitizeRich'),
        ));

        $roles = HM_Role_RoleModelAbstract::getBasicRoles(true, true);
        unset($roles[HM_Role_RoleModelAbstract::ROLE_DEAN]);
        unset($roles[HM_Role_RoleModelAbstract::ROLE_MANAGER]);
        unset($roles[HM_Role_RoleModelAbstract::ROLE_ADMIN]);
        unset($roles[HM_Role_RoleModelAbstract::ROLE_DEVELOPER]);

        $this->addElement('multiCheckbox', 'roles', array(
            'Label' => _('Роли'),
            'Required' => true,
            'Validators' => array(
            ),
            'Filters' => array(
            ),
            'separator' => '<br/><br/>',
            'MultiOptions' => $roles
        ));

        $this->addElement('checkbox', 'published', array(
            'Label' => _('Опубликован'),
            'Required' => false,
            'Validators' => array(
                array('Int')
            ),
            'Filters' => array(
                'Int'
            ),
            'multiOptions' => HM_Faq_FaqModel::getStatuses()
        ));


        $fields = array(
            'cancelUrl',
            'faq_id',
            'published',
            'question',
            'answer',
            'roles'
        );

        $this->addDisplayGroup(
            $fields,
            'faqGroup',
            array('legend' => _('Общие свойства'))
        );


		$this->addElement('Submit', 'submit', array('Label' => _('Сохранить')));


        parent::init(); // required!
	}

    public function getElementDecorators($alias, $first = 'ViewHelper'){

        if(in_array($alias, array('published'))){
            return array ( // default decorator
                array($first),
                array('RedErrors'),
                array('Description', array('tag' => 'p', 'class' => 'description')),
                array('Label', array('tag' => 'span', 'placement' => Zend_Form_Decorator_Abstract::APPEND, 'separator' => '&nbsp;')),
                array(array('data' => 'HtmlTag'), array('tag' => 'dd', 'class'  => 'element'))
            );
        }else{
            return parent::getElementDecorators($alias, $first);
        }


    }


}