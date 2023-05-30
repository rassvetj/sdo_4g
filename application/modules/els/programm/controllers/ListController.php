<?php
class Programm_ListController extends HM_Controller_Action_Crud
{
    private $_programmId = 0;
    private $_groupsByProgrammId = array();

    public function init()
    {
        $this->_programmId = (int) $this->_getParam('programm_id', 0);
        $this->_setForm(new HM_Form_Programm());
        parent::init();
    }

    protected function _redirectToIndex()
    {

        $this->_redirector->gotoSimple('index', 'list', 'programm', array());

    }

    protected function _getMessages()
    {
        return array(
            self::ACTION_INSERT => _('Программа успешно создана'),
            self::ACTION_UPDATE => _('Программа успешно обновлена'),
            self::ACTION_DELETE => _('Программа успешно удалена'),
            self::ACTION_DELETE_BY => _('Программы успешно удалены')
        );
    }

    public function indexAction()
    {
		
		$isExport 	     = $this->_getParam('_exportTogrid', false);
		$isSetEmptyQuery = ($this->isGridAjaxRequest() || $isExport) ? false : true;

        $subSelect = $this->getService('Programm')->getSelect();

        $subSelect
            ->from(
            'programm',
            array(
                'programm.programm_id',
                'study_groups.group_id',
                'study_groups.name')
        )->joinLeft(
            'study_groups_programms',
            'programm.programm_id = study_groups_programms.programm_id',
            array()
        )->joinLeft(
            'study_groups',
            'study_groups_programms.group_id = study_groups.group_id',
            array()
        );
		
		if(!$isSetEmptyQuery){	
			$stmt =  $subSelect->query();
			$this->_groupsByProgrammId = $stmt->fetchAll();
		}

        $select = $this->getService('Programm')->getSelect();

        $select->from(
            array('p' => 'programm'),
            array(
                'p.programm_id',
                'p.name',
                'items' => new Zend_Db_Expr('GROUP_CONCAT(CONCAT(s.name, \',,,\'))'),
                'groups' => 'p.programm_id'
            )
        )->joinLeft(
            array('pe' => 'programm_events'),
            'pe.programm_id = p.programm_id',
            array()
        )->joinLeft(
            array('s' => 'subjects'),
            'pe.item_id = s.subid AND pe.type = '.HM_Programm_Event_EventModel::EVENT_TYPE_SUBJECT,
            array()
        )->group(array('p.programm_id', 'p.name'));
		
		
		if($isSetEmptyQuery){			
			$select->where('1=0');			
		}

        $grid = $this->getGrid(
            $select,
            array(
                'programm_id' => array('hidden' => true),
                'name' => array(
                    'title' => _('Название'),
                    'decorator' => '<a href="'.$this->view->url(array('module' => 'programm', 'controller' => 'index', 'action' => 'index', 'programm_id' => '')).'{{programm_id}}">{{name}}</a>'
                ),
                'items' => array(
                    'title' => _('Состав'),
                    'callback' => array('function' => array($this, 'updateItems'), 'params' => array('{{items}}'))
                ),
                'groups' => array(
                    'title' => _('Учебные группы'),
                    'callback' => array('function' => array($this, 'updateGroups'), 'params' => array('{{programm_id}}'))
                )
            ),
            array(
                'name' => null
            )
        );

        $grid->addAction(
            array('module' => 'programm', 'controller' => 'list', 'action' => 'edit'),
            array('programm_id'),
            $this->view->icon('edit')
        );

        $grid->addAction(
            array('module' => 'programm', 'controller' => 'list', 'action' => 'delete'),
            array('programm_id'),
            $this->view->icon('delete')
        );

        $grid->addMassAction(
            array('module' => 'programm', 'controller' => 'list', 'action' => 'delete-by'),
            _('Удалить'),
            _('Вы уверены?')
        );

        $this->view->gridAjaxRequest = $this->isAjaxRequest();
        $this->view->grid = $grid->deploy();
    }

    public function updateItems($items)
    {
        $items = explode(',,,,', $items);
        $output = array();

        if (count($items)) {
            foreach($items as $item) {
                if ($item != '') {
                    $output[] = $item;
                }
            }
        }

        $items = $output;


        if (count($items)) {
            $ret = sprintf('<p class="total">%s</p>', $this->getService('Subject')->pluralFormCount(count($items)));

            foreach($items as $item) {
                $ret .= sprintf('<p>%s</p>', $item);
            }
            $ret = str_replace(',,,', '', $ret);
            return $ret;
        } else {
            return _('Нет');
        }
    }

    public function updateGroups($programmId)
    {
        $groups = array();
        foreach($this->_groupsByProgrammId as $groupByProgrammId){
            if ($groupByProgrammId['programm_id'] == $programmId){
                if($groupByProgrammId['name']){
                    $groups[] = $groupByProgrammId;
                }
            }
        }

        if (count($groups)) {
            $ret = sprintf('<p class="total">%s</p>', $this->getService('StudyGroup')->pluralFormCount(count($groups)));

            foreach($groups as $group) {
                $link = '<a href="'.$this->view->url(array('module' => 'study-groups', 'controller' => 'users', 'action' => 'index', 'group_id' => $group['group_id'])).'">'. $group['name'] .'</a>';
                $ret .= sprintf('<p>%s</p>', $link);
                //'<a href="'.$this->view->url(array('module' => 'programm', 'controller' => 'index', 'action' => 'index', 'programm_id' => '')).'{{programm_id}}">{{name}}</a>'
            }
            return $ret;
        }

        return _('Нет');
    }


    public function create($form)
    {
        $this->getService('Programm')->insert(
            array(
                'name' => $form->getValue('name'),
                'description' => $form->getValue('description'),
                'name_translation' => $form->getValue('name_translation'),
                'description_translation' => $form->getValue('description_translation')
            )
        );

    }

    public function setDefaults($form)
    {
        if ($this->_programmId) {
            $programm = $this->getOne($this->getService('Programm')->find($this->_programmId));
            if ($programm) {
                $form->setDefaults(
                    $programm->getValues()
                );
            }
        }
    }

    public function update($form)
    {
        return $this->getService('Programm')->update(
            array(
                'programm_id' => (int) $form->getValue('programm_id'),
                'name' => $form->getValue('name'),
                'description' => $form->getValue('description'),
                'name_translation' => $form->getValue('name_translation'),
                'description_translation' => $form->getValue('description_translation')				
            )
        );
    }

    public function delete($id)
    {
        return $this->getService('Programm')->delete($id);
    }
}