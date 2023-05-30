<?php
class HM_Form_List extends HM_Form
{
	public function init()
	{
        
        $subjectId = (int) $this->getParam('subject_id', 0);

        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('list');

        $this->addElement('hidden', 'cancelUrl', array(
            'Required' => false,
            'Value' => $this->getView()->url(($subjectId ? array('action' => 'index', 'subject_id' => $subjectId) : array('action' => 'index')))
        ));

        $this->addElement('hidden', 'exercise_id', array(
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

        if (!$subjectId) {
            $this->addElement('select', 'status', array(
                    'label' => _('Статус ресурса БЗ'),
                    'required' => true,
                    'filters' => array(array('int')),
                    'multiOptions' => HM_Test_Abstract_AbstractModel::getStatuses()
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

        $this->addElement($this->getDefaultWysiwygElementName(), 'description', array(
            'Label' => _('Описание'),
            'Required' => false,
            'Validators' => array(
            ),
            'Filters' => array('HtmlSanitizeRich'),
        ));

        if (!$subjectId) {
            $this->addElement('textarea', 'keywords', array(
                'Label' => _('Ключевые слова'),
                'Required' => false,
                'Validators' => array(
                ),
                'Filters' => array(
                    'StripTags'
                )
            ));
        } else {
            $this->addElement('hidden', 'keywords');
        }

        $fields = array(
            'cancelUrl',
            'test_id',
            'title',
            'status',
            'description',
            'keywords',
        );

        $this->addDisplayGroup(
            $fields,
            'testGroup1',
            array('legend' => _('Общие свойства'))
        );

        if (!$subjectId) {

            $this->addElement('UiMultiSelect', 'categories',
                array(
                    'Label' => _('Классификатор информационных ресурсов'),
                    'Required' => false,
                    'Filters' => array(
                        'Int'
                    ),
                    'multiOptions' => array(),
                    'class' => 'multiselect'
                )
            );

            $collections = Zend_Registry::get('serviceContainer')->getService('CourseCompetence')->fetchAll(null, 'name');
            $competences = $collections->getList('coid', 'name');

            $this->addElement('UiMultiSelect', 'competences',
                array(
                    'Label' => _('Классификатор компетенций работников ОАО «Газпром»'),
                    'Required' => false,
                    'Filters' => array(
                        'Int'
                    ),
                    'multiOptions' => $competences,
                    'class' => 'multiselect'
                )
            );

            $this->addElement('UiMultiSelect', 'activities',
                array(
                    'Label' => _('Классификатор видов деятельности и тем обучения'),
                    'Required' => false,
                    'Filters' => array(
                        'Int'
                    ),
                    'multiOptions' => array(),
                    'class' => 'multiselect'
                )
            );


            $this->addDisplayGroup(array(
                'categories',
                'competences',
                'activities'
            ),
                'testGroup2',
                array('legend' => _('Классификация'))
            );

        }

		$this->addElement('Submit', 'submit', array('Label' => _('Сохранить')));
        
        parent::init(); // required!
	}

}