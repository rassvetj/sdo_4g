<?php
class Holiday_IndexController extends HM_Controller_Action_Crud
{
    public function indexAction()
    {

        if (!$this->_hasParam('ordergrid')) {
            $this->_setParam('ordergrid', 'date_ASC');
        }
        
        if (!$this->isGridAjaxRequest() && $this->_request->getParam('date[from]grid') == "" && $this->_request->getParam('date[to]grid') == "") {
        	
        	$date = Zend_Date::now()->getDate();
        	$year = $date->get('Y');
        	$month = $date->get('M');
        	        	    	
        	$from = new Zend_Date(array('year' =>$year, 'month' => $month, 'day' => 1));
        	$to = new Zend_Date(array('year' => ($month == 12) ? $year+1 : $year, 'month' => ($month == 12) ? 1 : $month+1, 'day' => 1));
        	$to->sub(1, Zend_Date::DAY);
        	
        	$this->_request->setParam('date[from]grid', $from->toString());
        	$this->_request->setParam('date[to]grid', $to->toString());
        }

        $select = $this->getService('Holiday')->getSelect();
        $select->from(
            'holidays',
            array(
                'id',
            	'date',
            	'title',
            )
        )
        ->where('user_id=?',0);

        $grid = $this->getGrid(
            $select,
            array(
                'id' => array('hidden' => true),
                'date' => array(
                	'title' => _('Дата'),
                	'format' => array('date',
                		array('date_format' => HM_Locale_Format::getDateFormat())
                	),
                ),
                'title' => array('title' => _('День недели') . '/' . _('Название праздника')),
            ),
            array(
                'id' => null,
            	'date' => array('render' => 'GridDate'),
            	'title' => null,
            )
        );

        $grid->addAction(array(
            'module' => 'holiday',
            'controller' => 'index',
            'action' => 'edit'
        ),
            array('id'),
            $this->view->icon('edit')
        );
        
        $grid->addAction(array(
            'module' => 'holiday',
            'controller' => 'index',
            'action' => 'delete'
        ),
            array('id'),
            $this->view->icon('delete')
        );
        
        $grid->addMassAction(array(
        		'module' => 'holiday',
        		'controller' => 'index',
        		'action' => 'delete-by'
        ),
        		_('Удалить'),
        		_('Вы уверены?')
        );
        
        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid->deploy();

    }

    public function editAction()
    {
        $id = (int) $this->_getParam('id', 0);

        $form = new HM_Form_Holiday();
        $form->setAction($this->view->url(array('module' => 'holiday', 'controller' => 'index', 'action' => 'edit')));

        if (!$this->getRequest()->getParam('is_user_event', false)) {
            $form->removeElement('user_id');
        }

        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {

                $userId = 0;
                if ($form->getElement('user_id')) {
                    if (is_array($form->getValue('user_id'))) {
                        $userId = array_shift($form->getValue('user_id'));
                    } else {
                        $userId = (int)$form->getValue('user_id');
                    }
                }
                if ($id) {
	            	$holiday = $this->getService('Holiday')->update(array(
	                    'title' => $form->getValue('title'),
	                    'date' => $form->getValue('date'),
	                    'id' => $form->getValue('id'),
                        'user_id' => $userId
	                ));
	            	$this->_flashMessenger->addMessage(_('Выходной день успешно изменен'));
                } else {
                    $holiday = $this->getService('Holiday')->insert(array(
	                    'type' => HM_Holiday_HolidayModel::TYPE_SINGLE,
	                    'title' => $form->getValue('title'),
	                    'date' => $form->getValue('date'),
                        'user_id' => $userId
	                ));
                	$this->_flashMessenger->addMessage(_('Выходной день успешно создан'));
                }

                if ($holiday->user_id) {
                    $this->_redirector->gotoSimple('calendar', 'teacher', 'assign', array('switcher' => 'calendar'));
                } else {
                    $this->_redirector->gotoSimple('index', 'index', 'holiday');
                }


            }
        } else {
            if ($id) {
                $holiday = $this->getOne($this->getService('Holiday')->find($id));
                $values = array();
                if ($holiday) {
                    $values = $holiday->getValues();
                    $values['date'] = date('d.m.Y', strtotime($values['date']));
                    if($values['user_id']) {
                        $values['user_id'] = array($values['user_id'] => $this->getService('User')->find($values['user_id'])->current()->getName());
                    }
                }
                $form->setDefaults($values);
            } elseif($this->_getParam('user_id',0)) {
                $userId = $this->_getParam('user_id',0);
                $user   =  $this->getService('User')->find($userId)->current();
                if ($user) {
                    $form->setDefault('user_id', array($userId => $user->getName()));
                }
            }
        }

        $this->view->form = $form;

    }
   
    public function editPeriodicAction()
    {
        $form = new HM_Form_HolidayPeriodic();
        $request = $this->_request;
        if ($request->isPost())
        {
            if ($form->isValid($request->getParams()))
            {
            	$holidays = $form->getValue('holidays');
				if (is_array($holidays) && count($holidays)) {
            		$this->getService('Option')->setOption('holidays', implode(',', $holidays));
            		
            		// удалить все выходные в будущем
            		$this->getService('Holiday')->deleteBy(array(
            			'type = ?' => HM_Holiday_HolidayModel::TYPE_PERIODIC,
            			'date > ?' => date('Y-m-d'),
            		));		
            		
            		// создать выходные на год вперед
            		$this->getService('Holiday')->createForYear($holidays);
				}
				
                //$this->_flashMessenger->addMessage(_('Параметры отображения сервисов взаимодействия успешно изменены.'));
                $this->_redirector->gotoSimple('index', 'index', 'holiday');
            }
        } else
        {

            $data = $this->getService('Option')->getOption('holidays');

            if ($data !== null) {
            	$holidays = explode(',', $data);
            	$form->populate(array('holidays' => $holidays));
            }
        }

        $this->view->form = $form;
    }

    public function delete($id)
    {
    	$this->getService('Holiday')->delete($id);
    }
}