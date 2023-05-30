<?php
class HM_Form_Resource extends HM_Form
{
	public function init()
	{
        $resourceId = $this->getParam('resource_id', 0);

        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setAttrib('enctype', 'multipart/form-data');
        $this->setName('resource');

        $this->addElement('hidden', 'cancelUrl', array(
            'Required' => false,
            'Value' => $this->getView()->url(array('action' => 'index'))
        ));

        $this->addElement('hidden', 'resource_id', array(
            'Required' => true,
            'Validators' => array('Int'),
            'Filters' => array('Int')
        ));

        $this->addElement('hidden', 'activity_id', array(
            'Required' => true,
            'Validators' => array('Int'),
            'Filters' => array('Int')
        ));

        $this->addElement('hidden', 'activity_type', array(
            'Required' => true,
            'Validators' => array('Int'),
            'Filters' => array('Int')
        ));

        $this->addElement('text', 'title', array(
            'label' => _('Название'),
            'readonly' => true,
        ));

		
        $this->addElement('text', 'title_translation', array(
            'label' => _('Название').(' (en)'),
            //'readonly' => false,
        ));

        $this->addElement('select', 'type', array(
            'label' => _('Тип ресурса'),
            'id' => 'type_id',
            'multiOptions' => array(HM_Resource_ResourceModel::TYPE_ACTIVITY => _('Ресурс на основе сервиса взаимодействия')),
            'disabled' => true
        ));

	    if ($this->getService('User')->getCurrentUserRole() == HM_Role_RoleModelAbstract::ROLE_MANAGER) {
            $this->addElement('select', 'status', array(
                    'label' => _('Статус ресурса БЗ'),
                    'required' => true,
                    'filters' => array(array('int')),
                    'multiOptions' => array(
                        HM_Resource_ResourceModel::STATUS_UNPUBLISHED    => _('Не опубликован'),
                        HM_Resource_ResourceModel::STATUS_PUBLISHED      => _('Опубликован'),
                        // 3-го не дано
                    )
                )
            );
        } else {
            $this->addElement('hidden', 'status',
                array(
                    'required' => true,
                    'filters' => array(array('int'))
                )
            );
        }

        $this->addElement('hidden', 'location',
            array(
                'value' => 1,
                'filters' => array(array('int'))
            )
        );

        $this->addElement('textarea', 'description', array(
            'Label' => _('Краткое описание'),
            'Required' => false,
            'Validators' => array(
            ),
            'Filters' => array(
                'StripTags'
            )
        ));

        $this->addElement('textarea', 'description_translation', array(
            'Label' => _('Краткое описание').(' (en)'),
            'Required' => false,
            'Validators' => array(
            ),
            'Filters' => array(
                'StripTags'
            )
        ));

        $tags = $resourceId ? $this->getService('Tag')->getTags($resourceId, $this->getService('TagRef')->getResourceType() ) : '';
        $this->addElement(new HM_Form_Element_FcbkComplete('tags', array(
                'Label' => _('Метки'),
				'Description' => _('Произвольные слова, предназначены для поиска и фильтрации, после ввода слова нажать &laquo;Enter&raquo;'),
                'json_url' => $this->getView()->url(array('module' => 'resource', 'controller' => 'index', 'action' => 'tags')),
                'value' => $tags,
                'Filters' => array()
            )
        ));

        $this->addElement(new HM_Form_Element_FcbkComplete('related_resources', array(
                'Label' => _('Связанные ресурсы'),
                'Description' => _('Используйте знак # для указания ID ресурса'),
                'json_url' => $this->getView()->url(array('module' => 'resource', 'controller' => 'index', 'action' => 'resources-list')),
                'value' => array(),
                'newel' => false,
                'height' => 3,
                'maxitimes' => 10,
                'Filters' => array()
            )
        ));

        $this->addDisplayGroup(
            array(
                'cancelUrl',
                'resource_id',
                'title',
				'title_translation',
                'type',
                'status',
                'description',
				'description_translation',
                'tags',
                'related_resources',
                'submit'
            ),
            'resourceGroup',
            array('legend' => _('Общие свойства'))
        );

        if (!$this->getParam('subject_id', 0)) {
            $classifierElements = $this->addClassifierElements(HM_Classifier_Link_LinkModel::TYPE_RESOURCE, $this->getParam('resource_id', 0));
            $this->addClassifierDisplayGroup($classifierElements);
        }

        $this->addElement('Submit', 'submit', array('Label' => _('Сохранить')));

        parent::init(); // required!
	}
}