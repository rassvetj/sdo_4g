<?php
class Event_ListController extends HM_Controller_Action_Crud
{
	
	protected $_currentLang = 'rus';
	
	public function init()
    {   
        parent::init();
        $this->_setForm(new HM_Form_Event());
		
		$request = Zend_Controller_Front::getInstance()->getRequest();
		$this->_currentLang = $request->getCookie(HM_User_UserService::COOKIE_NAME_LANG);		
    }

    public function indexAction()
    {   
        $select = $this->getService('Event')->getSelect();

        $select->from('events', array('event_id', 'title', 'title_translation', 'tool', 'weight'));

        $grid = $this->getGrid(
            $select,
            array(
                'event_id' => array('hidden' => true),
                'title' => array('title' => _('Название')),
				'title_translation' => array('hidden' => true),
                'tool' => array('title' => _('Инструмент обучения'), 'callback' => array('function' => array($this, 'updateTool'), 'params' => array('{{tool}}'))),
                'weight' => array('title' => _('Вес')),
            ),
            array(
                'title' => null,
                'title_translation' => null,
                'tool' => array('values' => HM_Event_EventModel::getTypes())
            )
        );

        $grid->addAction(array(
           'module' => 'event',
           'controller' => 'list',
           'action' => 'edit'
       ),
           array('event_id'),
           $this->view->icon('edit')
       );


       $grid->addAction(array(
           'module' => 'event',
           'controller' => 'list',
           'action' => 'delete'
       ),
           array('event_id'),
           $this->view->icon('delete')
       );

        $grid->addMassAction(array(
            'module' => 'event',
            'controller' => 'list',
            'action' => 'delete-by'
			),
            _('Удалить'),
            _('Вы уверены?')
        );

			$grid->updateColumn('title', array(
                    'callback' => array(
                        'function' => array($this, 'updateTitle'),
                        'params' => array('{{title}}', '{{title_translation}}')
                    )                    
                )
            );		
		
		
        $this->view->grid = $grid->deploy();
        $this->view->isAjaxRequest = $this->isAjaxRequest();
    }

    protected function _getMessages()
    {
        return array(
            self::ACTION_INSERT => _('Тип успешно создан'),
            self::ACTION_UPDATE => _('Тип успешно обновлён'),
            self::ACTION_DELETE => _('Тип успешно удалён'),
            self::ACTION_DELETE_BY => _('Типы успешно удалены')
        );
    }

    public function create(Zend_Form $form)
    {
        $event = $this->getService('Event')->insert(
            array(
                'title' => $form->getValue('title'),
                'title_translation' => $form->getValue('title_translation'),
                'tool' => $form->getValue('tool'),
                'scale_id' => $form->getValue('scale_id'),
                'weight' => $form->getValue('weight')
            )
        );

        if ($event) {
            $this->getService('Event')->updateIcon($event->event_id, $form->getElement('icon'));
            return true;
        }
        return false;
    }

    public function update(Zend_Form $form)
    {
        $event = $this->getService('Event')->update(
            array(
                'title' => $form->getValue('title'),
                'title_translation' => $form->getValue('title_translation'),				
                'tool' => $form->getValue('tool'),
                'scale_id' => $form->getValue('scale_id'),
                'event_id' => $form->getValue('event_id'),
                'weight' => $form->getValue('weight'),
            )
        );

        if ($event) {
            $this->getService('Event')->updateIcon($form->getValue('event_id'), $form->getElement('icon'));
            return true;
        }

        return false;

    }

    public function delete($id)
    {
        return $this->getService('Event')->delete($id);
    }

    public function setDefaults(Zend_Form $form)
    {
        $eventId = (int) $this->_getParam('event_id', 0);
        if ($eventId) {
            $event = $this->getOne($this->getService('Event')->find($eventId));
            if ($event) {
                $form->setDefaults($event->getValues());
            }
        }
    }

	public function updateTool($tool)
    {
        $tools = HM_Event_EventModel::getTypes();
        return $tools[$tool];
    }
	
	public function updateTitle($title, $translation='') {

		if ($this->_currentLang == 'eng' && $translation != '')
			return $translation;
		else  
			return $title;		
	} 
	
	
	
	
}