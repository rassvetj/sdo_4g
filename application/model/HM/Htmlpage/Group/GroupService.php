<?php
class HM_Htmlpage_Group_GroupService extends HM_Service_Nested
{

    public function getTreeContent($parent = 0, $role = null, $notEncode = false)
    {
        $res = array();
        
        if (null !== $role) $categories = $this->getChildren($parent, true, $this->quoteInto(array('node.role = ?'), array($role)));
        else                $categories = $this->getChildren($parent);
        
        if (count($categories)) {
            foreach($categories as $category) {
                $item = array(
                    'title' => (($parent > 0 && $notEncode === false) ? iconv(Zend_Registry::get('config')->charset, 'UTF-8', $category->name) : $category->name),
                    'key' => $category->group_id,
                    'isLazy' => true,
                    'isFolder' => true
                );
                $res[] = $item;
            }
        }
        // 0st level (roles & footer pages)
        if($parent === 0 && !$role){
        	
        	$roles = HM_Role_RoleModelAbstract::getBasicRoles(true, true);

//             unset($roles[HM_Role_RoleModelAbstract::ROLE_SUPERVISOR]);
//             unset($roles[HM_Role_RoleModelAbstract::ROLE_EMPLOYEE]);
//             unset($roles[HM_Role_RoleModelAbstract::ROLE_STUDENT]);
//             unset($roles[HM_Role_RoleModelAbstract::ROLE_USER]);

//             $roles = array_reverse($roles, true);
//             $roles[HM_Role_RoleModelAbstract::ROLE_ENDUSER] = _('Пользователь');
//             $roles = array_reverse($roles, true);

            if (count($roles)) {
                $result = array();
                foreach($roles as $key => $title) {
                	$item = array('title' => $title, 'expand' => false, 'key' => $key, 'isLazy' => 1, 'isFolder' => 1);
                    $result[] = $item;
                    $temp = $this->getTreeContent(0, $key, true);
                    if(!$temp) {
                        $temp = array(array('title' => _('нет элементов'), 'key' => 0, 'expand' => false, 'isFolder' => 0));
                    }
                    $result[] = $temp;
                }
                $res = $result;
            }
            
            $footer_pages = $this->getService('Htmlpage')->fetchAll($this->quoteInto(array('group_id = ?'), array(0)))->getList('page_id', 'name');
            foreach ($footer_pages as $key => $name){
            	$res[] = array('title' => $name, 'expand' => false, 'key' => $key, 'isLazy' => 1, 'isFolder' => 0);
            }
            
        }
        
        // 1st level (page groups)            
        if($parent === 0 && $role){
            if (count($res)) {
                $result = array();
                foreach($res as $r) {
                    $r['expand'] = true;
                    $result[] = $r;
                    $temp = $this->getTreeContent($r['key'], $role, true);
                    if(count($temp)) $result[] = $temp;
                    
                }
                
                $res = $result;
                $pages = array();
                $pages_data = $this->getService('Htmlpage')->fetchAll($this->quoteInto(array('group_id = ?'), array($r['key'])))->getList('page_id', 'name');
	            foreach ($pages_data as $key => $name){
	            	$pages[] = array('title' => $name, 'expand' => false, 'key' => $key, 'isLazy' => 1, 'isFolder' => 0);
	            }
	            $res[] = $pages;
            }
        }
        
        return $res;
    }
    

    public function delete($id)
    {
        // файл удаляется здесь, а генерится в 1main.php при первом последующем запуске
        $file = HM_Htmlpage_HtmlpageModel::getActionsPath();
        if (file_exists($file)) unlink($file);
        
        return parent::delete($id);        
    }    
    
    public function insert($data, $objectiveNodeId = 0, $position = HM_Db_Table_NestedSet::LAST_CHILD) 
    {
        $file = HM_Htmlpage_HtmlpageModel::getActionsPath();
        if (file_exists($file)) unlink($file);
        
        return parent::insert($data, $objectiveNodeId, $position);
    }
    
    public function update($data)
    {
        $file = HM_Htmlpage_HtmlpageModel::getActionsPath();
        if (file_exists($file)) unlink($file);
        
        return parent::update($data);
    }
}