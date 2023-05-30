<?php
class Group_SpecialityController extends HM_Controller_Action
{
    protected $required_permission_level = 3;
    
	public function indexAction()
	{

		$specialityId = (int) $this->_getParam('speciality', 0);
		$level        = $this->_getParam('level', 'all');
		$action       = $this->_getParam('action', '');

		$collection = $this->getService('Speciality')->fetchAll();
		$specialities = array();
		if (count($collection)) {
			$specialities = $collection->getList('trid', 'name');
		}

		$this->getService('Unmanaged')->getController()->addFilter(_('Специальность'), 'speciality', $specialities, $specialityId, true);

		$speciality = false;
		if ($specialityId > 0) {
			$speciality = $this->getService('Speciality')->getOne(
			    $this->getService('Speciality')->findManyToMany('Group', 'GroupAssign', $specialityId)
			);
		}

		if ($speciality) {
			$levels = array();
			for($i = 0; $i <= $speciality->number_of_levels; $i++) {
				$levels['s_'.$i] = sprintf(_('Семестр %d'), $i);
			}

			$this->getService('Unmanaged')->getController()->addFilter(_('Семестр'), 'level', $levels, $level, false);
		}

		$this->view->level      = (!$level) ? 'all' : $level;
		$this->view->speciality = $speciality;

	}
	
	public function assignAction()
	{
        $specialityId = (int) $this->_getParam('speciality', 0);
        $level        = $this->_getParam('level', 0);
        $direction    = $this->_getParam('direction', '');		
		$groups       = $this->_getParam('groups', array());
        
        if (($specialityId > 0) && count($groups)) {
            $speciality = $this->getService('Speciality')->getOne(
                $this->getService('Speciality')->find($specialityId)
            );            
            
            foreach($groups as $groupId) {
                $assign = $this->getService('SpecialityGroup')->getOne(
                    $this->getService('SpecialityGroup')->fetchAll(sprintf("trid = '%d' AND gid = '%d'", $specialityId, $groupId))
                );
                                
                switch($direction)
                {
                    case 'next':
                        $level = $assign->level + 1;                        
                        if ($level > $speciality->number_of_levels) {
                            $level = $speciality->number_of_levels;
                            continue;
                        }
                        break;
                    case 'prev':
                        $level = $assign->level - 1;                    
                        if ($level < 0) {
                            $level = 0;
                            continue;
                        }
                        break;
                }
                $this->getService('Speciality')->assignGroup($specialityId, $groupId, $level);
            }            
        }
        
        $this->_flashMessenger->addMessage(_('Группы успешно переведены'));
        //$this->_redirector->gotoSimple('index', 'speciality', 'group', array('speciality' => $specialityId, 'level' => $level));
        $this->_redirector->gotoUrl($this->view->serverUrl().$this->view->url(array('action' => 'index', 'controller' => 'speciality', 'module' => 'group'))."?speciality=$specialityId&level=s_$level");
	}
}