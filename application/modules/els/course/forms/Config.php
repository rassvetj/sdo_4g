<?php
/**
 * Форма для редактирования конфиг-файлов Skillsoft курсов
 *
 */
class HM_Form_Config extends HM_Form
{
    //public $status;

    public function init()
    {

        //$modelName = Zend_Registry::get('serviceContainer')->getService('Course')->getMapper()->getModelClass();
        //$model = new $modelName(null);

        $front = Zend_Controller_Front::getInstance();
        $req = $front->getRequest();

        $this->setMethod(Zend_Form::METHOD_POST);

        $this->setName('config');

        $this->addElement('hidden', 'cancelUrl', array(
            'Required' => false,
            'Value' => $this->getView()->baseUrl('course/list/' . $req->getParam('status'))
        ));

        $this->addElement('hidden', 'cid', array(
            'Required' => true,
            'Validators' => array('Int'),
            'Filters' => array('Int')));

        $this->addElement('textarea', 'content', array('Label' => _('Содержимое файла'),
                                                        'Required' => true,
                                                        'Validators' => array(),
                                                        'Filters' =>
                                                                array('StripTags')
                                                        )
                          );

        $this->addElement('Submit', 'submit', array(
            'Label' => _('Сохранить')));

        parent::init(); // required!
    }


}