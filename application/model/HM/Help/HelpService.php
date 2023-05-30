<?php
class HM_Help_HelpService extends HM_Service_Abstract
{

	public function testAndSave($params = null){
		
		if(!$params) return true;
		if(isset($params['module']) && $params['module'] == 'help') return false;

		$role = $this->getService('User')->getCurrentUserRole();

		$where = array();
		// managed
		if($params['module']){
			$controller = ($params['controller']) ? $params['controller'] : 'index';
			$action = ($params['action']) ? $params['action'] : 'index';
			
			$sub = intval(($params['subject_id'] || $params['resource_id'] || $params['course_id'] || $params['user_id']));
			
			$subject = $sub > 0 ? 1 : 0;
			
			$where = array( 
							'module = ?' => $params['module'], 
							'controller = ?' => $controller, 
							'role = ?' => $role, 
							'action = ?' => $action, 
							'link_subject = ?' => $subject
			              );
			if (APPLICATION_MODULE != 'ELS') {
			    $where['app_module=?'] = strtolower(APPLICATION_MODULE);
			} else {
			    $where['app_module IS NULL OR app_module=?'] = '';
			}             
		}
		// unmanaged
		elseif ($params['link']){
			$where = array('link = ?' => $params['link']);
		}

		$help = $this->getOne($this->fetchAll($where));
		
		if(!$help->help_id){
			$data = array('role' => $role);
			if($params['module']){
			    $data['app_module'] = (APPLICATION_MODULE != 'ELS')? strtolower(APPLICATION_MODULE) : '';
				$data['module'] = $params['module'];
				$data['controller'] = ($params['controller']) ? $params['controller'] : 'index';
				$data['action'] = ($params['action']) ? $params['action'] : 'index';
				$data['link_subject'] = intval(($params['subject_id'] || $params['resource_id'] || $params['course_id'] || $params['user_id']));
			}
			if($params['link']){
				$link = explode('?', $params['link']);
				$data['link'] = $link[0];
			}
            $data['lang'] = $lang;
			$this->insert($data);
		}
		
	}

    public function insert($data)
    {
        $data['lang'] = $this->getService('User')->getCurrentLangId();
        return parent::insert($data);
    }

    public function update($data)
    {
        $data['lang'] = $this->getService('User')->getCurrentLangId();
        return parent::update($data);
    }

    public function fetchAll($where = null, $order = null, $count = null, $offset = null)
    {
        if (null !== $where) {

            $lang = $this->getService('User')->getCurrentLangId();

            if (is_string($where)) {
                // todo
            } elseif(is_array($where)) {
                if ($lang == HM_User_UserService::DEFAULT_LANG) {
                    $where["lang = ? OR lang = '' OR lang IS NULL"] = $lang;
                } else {
                    $where["lang = ?"] = $lang;
                }
            }
        }

        return parent::fetchAll($where, $order, $count, $offset);
    }

}