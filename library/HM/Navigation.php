<?php
class HM_Navigation extends Zend_Navigation
{
    private $_substitutions = null;
    private $_activities = null;

    public function __construct($pages = null, $acl = null, $substitutions = null)
    {
        parent::__construct($pages);
        $this->_substitutions = $substitutions;
                
        $this->_setActivities();
        $this->_parseAcl($pages, $acl);
        $this->_processSubstitutions();
    }

    private function _parseAcl($pages, $acl, $resource = null)
    {
        foreach($pages as $item) {
            if (isset($item->resource) && strlen($item->resource)) {

                if (!$acl->has($item->resource)) {
                    if ($resource !== null) {
                        $acl->addResource(new Zend_Acl_Resource($item->resource), $resource);
                    } else {
                        $acl->addResource(new Zend_Acl_Resource($item->resource));
                    }
                }
                
                if (
                    (strpos($item->resource, 'cm:activity:') !== false) && 
                    !empty($this->_substitutions['subject_id']) 
                    // && Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_STUDENT)
                ) {
                    $this->_setActivityAcl($item, $acl);
                    continue;
                }
                
                if (isset($item->allow) && strlen($item->allow)) {
                    $roles = explode(',', $item->allow);
                    foreach($roles as $role) {
                        $acl->allow($role, $item->resource);
                    }
                } else {
                    $acl->allow(null, $item->resource);
                }

                if (isset($item->deny) && strlen($item->deny)) {
                    $roles = explode(',', $item->deny);
                    foreach($roles as $role) {
                        $acl->deny($role, $item->resource);
                    }
                }

                if (isset($item->pages) && count($item->pages)) {
                    $this->_parseAcl($item->pages, $acl, $item->resource);
                }

            } else {
                if (isset($item->pages) && count($item->pages)) {
                    $this->_parseAcl($item->pages, $acl);
                }
            }


        }
    }

    private function _processSubstitutions()
    {
        if ($this->_substitutions !== null) {
            $iterator = new RecursiveIteratorIterator($this, RecursiveIteratorIterator::SELF_FIRST);
            foreach($iterator as $page) {
                if ($page instanceof Zend_Navigation_Page_Mvc) {
                    $params = $page->getParams();
                    if (is_array($params) && count($params)) {
                        foreach($params as $key => $value) {
                            if (isset($this->_substitutions[$key])) {
                                $params[$key] = $this->_substitutions[$key];
                            } else {
                                if ($param = Zend_Controller_Front::getInstance()->getRequest()->getParam($key, false)) {
                                    $params[$key] = $param;
                                }
                            }
                        }
                    }
                    $page->setParams($params);
                }
                if ($page instanceof Zend_Navigation_Page_Uri) {
                    if (is_array($this->_substitutions) && count($this->_substitutions)) {
                        foreach($this->_substitutions as $key => $value) {
                            $page->uri = str_replace("%$key%", $value, $page->uri);
                        }
                    }
                    
                    if (strlen($page->uri) && (false == strstr('http://', $page->uri))) {
                        $page->uri = Zend_Registry::get('view')->baseUrl($page->uri);
                    }
                }
            }
        }
    }
    
    private function _setActivityAcl($page, $acl)
    {
        list(,,$activity) = explode(':', $page->resource);
        if (in_array((int) $activity, $this->_activities)) {
            $acl->allow(null, $page->resource);            
        } else {
            $acl->deny(null, $page->resource);
        }
    }
    
    // @todo: вынести прикладную логику из library 
    private function _setActivities()
    {
        $activities = array();
        
        $subject = Zend_Registry::get('serviceContainer')->getService('Subject')->getOne(
                Zend_Registry::get('serviceContainer')->getService('Subject')->find($this->_substitutions['subject_id'])
        );        
        
        if ($subject) {
            $names = HM_Activity_ActivityModel::getTabActivities();
            foreach($names as $activityId => $activityName) {
                if (($subject->services & $activityId)) {
                    $activities[] = $activityId;
                }
            }
        }  
        $this->_activities = $activities;      
    }

}