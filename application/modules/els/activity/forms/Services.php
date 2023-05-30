<?php
class HM_Form_Services extends HM_Form
{
	public function init()
	{
        $id = $this->getParam('id', 0);

        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setAttrib('enctype', 'multipart/form-data');
        $this->setName('services');

        $this->addElement('hidden', 'cancelUrl', array(
            'Required' => false,
            'Value' => $this->getView()->url(array('module' => 'default','action' => 'index', 'controller' => 'index'))
        ));


        /*
        $this->addElement('hidden', 'id', array(
            'Required' => true,
            'Validators' => array('Int'),
            'Filters' => array('Int')
        ));

        $this->addElement('checkbox', 'news', array(
            'Required' => false,
            'Validators' => array(
                'Int'),
            'Filters' => array(
                'Int'),
            'Label' => _('Новости')));

        $this->addElement('checkbox', 'forum', array(
            'Required' => false,
            'Validators' => array(
                'Int'),
            'Filters' => array(
                'Int'),
            'Label' => _('Форум')));
        $this->addElement('checkbox', 'opros', array(
            'Required' => false,
            'Validators' => array(
                'Int'),
            'Filters' => array(
                'Int'),
            'Label' => _('Опрос')));
        $this->addElement('checkbox', 'library', array(
            'Required' => false,
            'Validators' => array(
                'Int'),
            'Filters' => array(
                'Int'),
            'Label' => _('Библиотека документов')));
        $this->addElement('checkbox', 'contacts', array(
            'Required' => false,
            'Validators' => array(
                'Int'),
            'Filters' => array(
                'Int'),
            'Label' => _('Контакты')));
        $this->addElement('checkbox', 'messages', array(
            'Required' => false,
            'Validators' => array(
                'Int'),
            'Filters' => array(
                'Int'),
            'Label' => _('Сообщения')));
        $this->addElement('checkbox', 'blog', array(
            'Required' => false,
            'Validators' => array(
                'Int'),
            'Filters' => array(
                'Int'),
            'Label' => _('Блоги')));
        $this->addElement('checkbox', 'wiki', array(
            'Required' => false,
            'Validators' => array(
                'Int'),
            'Filters' => array(
                'Int'),
            'Label' => _('Wiki')));
        $this->addElement('checkbox', 'webinar', array(
            'Required' => false,
            'Validators' => array(
                'Int'),
            'Filters' => array(
                'Int'),
            'Label' => _('Вебинар')));
        $this->addElement('checkbox', 'chat', array(
            'Required' => false,
            'Validators' => array(
                'Int'),
            'Filters' => array(
                'Int'),
            'Label' => _('Чат')));*/

        $activity = HM_Activity_ActivityModel::getTabActivities();
        $temp = $this->addElement('MultiCheckbox',
                          'activity',
                          array(
                            'separator' => '<br/><br/>',
				            'Required' => false,
				            'Label' => '',
				            'MultiOptions' => $activity
                          )
            );
       // $this->getElement('activity')->setRegisterInArrayValidator(true);



		$this->addElement('Submit', 'submit', array('Label' => _('Сохранить')));

        $this->addDisplayGroup(
            array(
                'cancelUrl',
                'id',
                'activity',
                'submit'
            ),
            'resourceGroup',
            array('legend' => '')
        );

        parent::init(); // required!
	}

    public function getFileElementDecorators($alias, $first = 'File') {
        $decorators = parent::getFileElementDecorators($alias, $first);

        $resourceId = (int) Zend_Controller_Front::getInstance()->getRequest()->getParam('resource_id', 0);

        if ($resourceId) {
            $resource = Zend_Registry::get('serviceContainer')->getService('Resource')->getOne(
                Zend_Registry::get('serviceContainer')->getService('Resource')->find($resourceId)
            );
            array_shift($decorators);
            array_unshift($decorators, array('FileInfo', array(
                 'file' => Zend_Registry::get('config')->path->upload->resource.'/'.$resourceId,
                 'name' => $resource->filename,
                 'download' => $this->getView()->url(array('module' => 'file', 'controller' => 'get', 'action' => 'resource', 'resource_id' => $resourceId))
            )));
            array_unshift($decorators, 'File');
        }

        return $decorators;
    }

}
