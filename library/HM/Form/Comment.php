<?php
class HM_Form_Comment extends HM_Form
{
    public function init()
    {
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('comment');

        $this->addElement(new Zend_Form_Element_Textarea('message', array(
            'Label' => _('Текст'),
            'Required' => true,
            'Filters' => array('StripTags'),
            'Validators' => array(
                array(
                    'validator' => 'StringLength',
                    'options' => array('max' => 4096, 'min' => 3)
            ))
        )));

        $this->addElement('Submit', 'submit', array('Label' => _('Сохранить')));

        $this->addDisplayGroup(
            array(
                'message',
                'submit'
            ),
            'commentGroup',
            array('legend' => _('Комментарий'))
        );

        parent::init(); // required!
	}
}