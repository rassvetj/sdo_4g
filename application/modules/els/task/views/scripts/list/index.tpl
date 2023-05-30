<?php if (!$this->gridAjaxRequest):?>
    <?php if (Zend_Registry::get('serviceContainer')->getService('Acl')->isCurrentAllowed('mca:task:list:new')):?>
        
        <?php if ($this->subjectId > 0):?>
        <?php echo $this->Actions('task', array(array('title' => _('Создать задание'), 'url' => $this->url(array('module' => 'task', 'controller' => 'list', 'action' => 'new')))));?>
        <?php else:?>
        	<?php //if(in_array(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), array(HM_Role_RoleModelAbstract::ROLE_DEAN, HM_Role_RoleModelAbstract::ROLE_DEVELOPER, HM_Role_RoleModelAbstract::ROLE_MANAGER))): ?>
            <?php if (Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), array(HM_Role_RoleModelAbstract::ROLE_DEAN, HM_Role_RoleModelAbstract::ROLE_DEVELOPER, HM_Role_RoleModelAbstract::ROLE_MANAGER))):?>
                <?php echo $this->Actions('task');?>
        	<?php endif;?>
        <?php endif;?>
        <?php //echo $this->headScript()?>
    <?php endif;?>
<?php endif;?>
<?php 
echo $this->grid;
?>
