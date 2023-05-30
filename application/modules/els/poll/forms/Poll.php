<?php
class HM_Form_Poll extends HM_Form
{
	public function init()
	{

        $subjectId = (int) $this->getParam('subject_id', 0);

        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('poll');

        $this->addElement('hidden', 'cancelUrl', array(
            'Required' => false,
            'Value' => $this->getView()->url(($subjectId ? array('action' => 'index', 'subject_id' => $subjectId) : array('action' => 'index')))
        ));

        $this->addElement('hidden', 'poll_id', array(
            'Required' => true,
            'Validators' => array('Int'),
            'Filters' => array('Int')
        ));

        $this->addElement('text', 'title', array(
            'Label' => _('Название'),
            'Required' => true,
            'Validators' => array(
                array('StringLength', 255, 1)
            ),
            'Filters' => array(
                'StripTags'
            )
        ));

        if ($this->getService('User')->getCurrentUserRole() == HM_Role_RoleModelAbstract::ROLE_MANAGER) {
            $this->addElement('select', 'status', array(
                    'label' => _('Статус ресурса БЗ'),
                    'description' => _('Ограниченное использование ресурсов предполагает возможность включения их в состав учебных курсов и оценочных сессий, они не доступны через Портал; неопубликованные ресурсы доступны только менеджерам Базы знаний.'),
                    'required' => true,
                    'filters' => array(array('int')),
                    'multiOptions' => HM_Poll_PollModel::getStatuses()
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

        $this->addElement('textarea', 'description', array(
            'Label' => _('Краткое описание'),
            'Required' => false,
            'Validators' => array(
            ),
            'Filters' => array(
                'StripTags'
            )
        ));

        $this->addElement(new HM_Form_Element_FcbkComplete('tags', array(
                'Label' => _('Метки'),
				'Description' => _('Произвольные слова, предназначены для поиска и фильтрации, после ввода слова нажать &laquo;Enter&raquo;'),
                'json_url' => $this->getView()->url(array('module' => 'poll', 'controller' => 'list', 'action' => 'tags')),
                'value' => '',
            )
        ));
        $fields = array(
            'cancelUrl',
            'poll_id',
            'title',
            'status',
            'description',
            'tags',
        );

        $this->addDisplayGroup(
            $fields,
            'testGroup1',
            array('legend' => _('Общие свойства'))
        );

        if (!$subjectId) {
            $classifierElements = $this->addClassifierElements(HM_Classifier_Link_LinkModel::TYPE_POLL, $this->getParam('quiz_id', 0));
            $this->addClassifierDisplayGroup($classifierElements);
        }

		$this->addElement('Submit', 'submit', array('Label' => _('Сохранить')));


        parent::init(); // required!
	}

}