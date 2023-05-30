<?php 
class HM_Form_Services extends HM_Form
{
	public function init()
	{
        $id = $this->getParam('id', 0);

        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setAttrib('enctype', 'multipart/form-data');
        //$this->setAttrib('onSubmit', "select_list_select_all('list2');");
        $this->setName('services');

        $this->addElement('hidden', 'cancelUrl', array(
            'Required' => false,
            'Value' => $this->getView()->url(($resourceId ? array('action' => 'index', 'resource_id' => $resourceId) : array('action' => 'index')))
        ));
        
          $temp = $this->addElement('multiCheckbox', 
                          'activity', 
                          array(
                            'separator' => '<br/><br/>',  
				            'Required' => false,
				            'Label' => '',
				            'MultiOptions' => HM_Activity_ActivityModel::getTabActivities()
                          )
            );
        
        
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
