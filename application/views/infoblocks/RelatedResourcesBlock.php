<?php
require_once APPLICATION_PATH . '/views/infoblocks/ScreenForm.php';

class HM_View_Infoblock_RelatedResourcesBlock extends HM_View_Infoblock_ScreenForm
{
    protected $id = 'relatedResourcesBlock';

    public function relatedResourcesBlock($title = null, $attribs = null, $options = null)
    {

        $resource = $options['subject'];
        $services = Zend_Registry::get('serviceContainer');

        $relatedResources = array();
        if (!empty($resource->related_resources)) {
            $relatedResources = explode(',', $resource->related_resources);
            array_walk($relatedResources, function(&$resource){$resource = (int) $resource;});
            $where = $services->getService('Resource')->quoteInto(
                    array('resource_id IN (?)'),
                    array($relatedResources)
            );
            if (!$services->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_RoleModelAbstract::ROLE_MANAGER, HM_Role_RoleModelAbstract::ROLE_DEVELOPER))) {
                 $where['status = ?'] = HM_Resource_ResourceModel::STATUS_PUBLISHED;               
            }
            $relatedResources = $services->getService('Resource')->fetchAll($where, 'title');
        }
        
        $this->view->resource = $resource;
        $this->view->relatedResources = $relatedResources;
        
        if ((
                $resource->created_by == $services->getService('User')->getCurrentUserId() &&
                $services->getService('Acl')->inheritsRole($services->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_DEVELOPER)
            ) ||
            $services->getService('Acl')->inheritsRole($services->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_MANAGER) // манагеру всё можно 
        ) {
            $this->view->editable = true;
        }        

		$content = $this->view->render('relatedResourcesBlock.tpl');

        if ($title == null) return $content;
        return parent::screenForm($title, $content, $attribs);
    }
}