<?php
abstract class HM_Extension_Remover_Abstract extends HM_Service_Standalone_Abstract implements HM_Service_Extension_Remover_Interface
{
    public function callAfterInitExtensions($event)
    {
    	$this->getService('Unmanaged')->removeRoles($this->_getItemstoHide('roles'));
    	$this->getService('Unmanaged')->removeRoleActions($ids = $this->_getItemstoHide('menus'));
    	
    	foreach($ids as $id) {
    		$this->getService('Unmanaged')->removeActionsXmlId($id);
    	}
    }    
    
    public function callFilterBasicRoles($event, $roles)
    {
    	foreach($this->_getItemstoHide('roles') as $roleToRemove) {
    		unset($roles[$roleToRemove]);
    	}
    	return $roles;
    }
    
    public function callFilterGridColumns($events, $columns)
    {
        foreach($this->_getItemstoHide('columns') as $col) {
            if (isset($columns[$col])) {
                $columns[$col] = array('hidden' => true);
            }
        }
        return $columns;
    }  

    public function callFilterClassifierLinkTypes($event, $types)
    {
        foreach ($this->_getItemstoHide('classifierTypes') as $type) {
            unset($types[$type]);
        }
        return $types;
    }

    public function callFilterForm($event, HM_Form $form)
    {
        foreach($this->_getItemstoHide('elements') as $element) {
            $form->removeElement($element);
        }
        
        foreach ($form->getDisplayGroups() as $group) {
            $elements = $group->getElements();
            if (!is_array($elements) || !count($elements)) {
                $form->removeDisplayGroup($group->getName());
            }
        }
    }

    public function callFilterReportDomains($event, $domains)
    {
    	foreach($this->_getItemstoHide('domains') as $domainToRemove) {
    		unset($domains[$domainToRemove]);
    	}
    	return $domains;
    }
    
    public function callFilterContextMenu($event, &$view) 
    {
        foreach($this->_getItemstoHide('contextMenus') as $menuToRemove) {
             $view->addContextNavigationModifier(
                 new HM_Navigation_Modifier_Remove_Page('resource', $menuToRemove)
             ); 
        }       
    }
    
    public function callFilterInfoblocks($event, &$blocks) 
    {
        $blocksToRemove = $this->_getItemstoHide('infoblocks');
        foreach($blocks as $i => &$block) {
            if (in_array($block['name'], $blocksToRemove)) {
                unset($blocks[$i]);
            } elseif (is_array($block['block'])) {
                foreach ($block['block'] as $j => $childBlock) {
                    if (in_array($childBlock['name'], $blocksToRemove)) {
                        unset($block['block'][$j]);
                    }                    
                }
            }
        }
        return !empty($blocks) ? $blocks : array();       
    }
    
    public function callFilterMassActions($event, $massActions)
    {
        $actionsToRemove = $this->_getItemstoHide('massActions');
        foreach($actionsToRemove as $action) {
            $urlToRemove = Zend_Registry::get('view')->url($action);
            foreach ($massActions as $i => $massAction) {
                if (strpos($massAction['url'], $urlToRemove) !== false) {
                    unset($massActions[$i]);
                }
            }
        }
        return $massActions;
    }
    
    protected function _getItemstoHide($type)
    {
        if (isset($this->_itemsToHide[$type]) && is_array($this->_itemsToHide[$type])) {
            return $this->_itemsToHide[$type];
        } else {
            return array();
        }
    }
}