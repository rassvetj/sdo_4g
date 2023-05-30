<?php
class HM_Form_Materials extends HM_Form
{
	public function init()
	{
        $id = $this->getParam('id', 0);

        $this->setMethod(Zend_Form::METHOD_POST);

        $this->addElement('hidden', 'cancelUrl', array(
            'Required' => false,
            'Value' => $this->getView()->url(($resourceId ? array('action' => 'index', 'resource_id' => $resourceId) : array('action' => 'index')))
        ));

        $this->addElement('hidden', 'subject_id', array(
            'Required' => false,
        ));

        $this->addElement('hidden', 'SHEID', array(
            'Required' => false,
        ));

        $this->addElement('text', 'title', array(
            'Label' => _('Название'),
            'Description' => _('Название материала, отображаемое на странице "Все материалы", может не совпадать с названием информационного ресурса или учебного модуля. Таким образом один и тот же элемент базы знаний может быть использован в разных курсах под разными названиями.'),
            'Required' => true,
            'Validators' => array(
                array('StringLength', 255, 1)
            ),
            'Filters' => array(
                'StripTags'
            )
        ));

        $this->addElement('textarea', 'descript', array(
            'Label' => _('Краткое описание'),
            'Description' => _('Описание материала, отображаемое на странице "Все материалы", может не совпадать с описанием информационного ресурса или учебного модуля. Таким образом один и тот же элемент базы знаний может быть использован в разных курсах и иметь при этом различный текст краткого описания.'),
            'Required' => false,
            'Validators' => array(
            ),
            'Filters' => array(
                'StripTags'
            )
        ));

		$this->addElement('Submit', 'submit', array('Label' => _('Сохранить')));

        $this->addDisplayGroup(
            array(
                'cancelUrl',
                'title',
                'descript',
                'submit'
            ),
            'resourceGroup',
            array('legend' => '')
        );

        parent::init(); // required!
	}

}
