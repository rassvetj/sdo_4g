<?php
class HM_Form_Group extends HM_Form
{
    
    
public function init()
	{
        $this->setMethod(Zend_Form::METHOD_POST);
        //$this->setAttrib('enctype', 'multipart/form-data');
        $this->setName('group');

        $this->addElement('hidden', 'cancelUrl', array(
            'Required' => false,
            'Value' => $this->getView()->url(array('action' => 'index'))
        ));

        $this->addElement('hidden', 'gid', array(
            'Required' => true,
            'Validators' => array('Int'),
            'Filters' => array('Int')
        ));

        $this->addElement('text', 'name', array(
            'Label' => _('Название'),
            'Required' => true,
            'Validators' => array(
                array('StringLength', 255, 1)
            ),
            'Filters' => array(
                'StripTags'
            )
        ));
		
        $this->addElement('text', 'name_translation', array(
            'Label' => _('Перевод (en)'),
            'Required' => false,
            'Validators' => array(
                array('StringLength', 255, 1)
            ),
            'Filters' => array(
                'StripTags'
            )
        ));		

   $this->addElement('Submit', 'submit', array('Label' => _('Сохранить')));
        
            $this->addDisplayGroup(
                array(
                    'cancelUrl',
                    'gid',
                    'name',
					'name_translation',
                    'submit'
                ),
                'groupGroup',
                array('legend' => _('Подгруппа'))
            );
     

        parent::init(); // required!
	}
    
    
    
    
    
    
    
    
    
    
    
    
	/**
	 * Предыдущий метод инициализации
	 */
	public function init11()
	{
        $this->setMethod(Zend_Form::METHOD_POST);
        //$this->setAttrib('enctype', 'multipart/form-data');
        $this->setAttrib('onSubmit', "select_list_select_all('list2');");
        $this->setName('group');

        $this->addElement('hidden', 'cancelUrl', array(
            'Required' => false,
            'Value' => $this->getView()->url(array('action' => 'index'))
        ));

        $this->addElement('hidden', 'gid', array(
            'Required' => true,
            'Validators' => array('Int'),
            'Filters' => array('Int')
        ));

        $this->addElement('text', 'name', array(
            'Label' => _('Название'),
            'Required' => true,
            'Validators' => array(
                array('StringLength', 255, 1)
            ),
            'Filters' => array(
                'StripTags'
            )

        ));

        if (Zend_Registry::get('serviceContainer')->getService('Unmanaged')->isSpecialitiesExist()) {

            $collection = Zend_Registry::get('serviceContainer')->getService('Speciality')->fetchAll(null, 'name');
            $specialities = $collection->getList('trid', 'name', _('Нет'));


            $this->addElement('select', 'speciality', array(
                    'Label' => _('Специальность'),
                    'Required' => true,
                    'Filters' => array(
                        'Int'
                    ),
                    'MultiOptions' => $specialities
                )
            );
        }

        $list2Options = '';
        $collection = Zend_Registry::get('serviceContainer')->getService('Group')->findManyToMany('User', 'Assign', $this->getParam('gid', 0));
        if (count($collection)) {
            $group = $collection->current();
            $users = $group->getValue('users');
            if ($users && count($users)) {
                foreach($users as $user) {
                    $list2Options .= sprintf('<option value="%d"> %s</option>', $user->MID, $user->getName());
                }
            }
        }

        $this->addElement('lists', 'students', array(
                'list1Name'  => 'list1',
                'list1Title' => _('Все'),
                'list2Name'  => 'list2',
                'list2Title' => _('Группа'),
                'list2Options' => $list2Options
            )
        );

		$this->addElement('Submit', 'submit', array('Label' => _('Сохранить')));

        if (Zend_Registry::get('serviceContainer')->getService('Unmanaged')->isSpecialitiesExist()) {
            $this->addDisplayGroup(
                array(
                    'cancelUrl',
                    'gid',
                    'name',
                    'speciality',
                    'students',
                    'submit'
                ),
                'groupGroup',
                array('legend' => _('Группа'))
            );
        } else {
            $this->addDisplayGroup(
                array(
                    'cancelUrl',
                    'gid',
                    'name',
                    'students',
                    'submit'
                ),
                'groupGroup',
                array('legend' => _('Группа'))
            );
        }

        parent::init(); // required!
	}
	
	
	
	

}