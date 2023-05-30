<?php if (!$this->gridAjaxRequest):?>
    <?php if (Zend_Registry::get('serviceContainer')->getService('Acl')->isCurrentAllowed('mca:poll:list:new')):?>
        
        <?php if ($this->subjectId > 0):?>
        <?php echo $this->Actions('poll', array(array('title' => _('Создать опрос'), 'url' => $this->url(array('module' => 'poll', 'controller' => 'list', 'action' => 'new')))));?>
        <?php else:?>
        	<?php if(in_array(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), array(HM_Role_RoleModelAbstract::ROLE_DEVELOPER, HM_Role_RoleModelAbstract::ROLE_MANAGER))): ?>
                <?php echo $this->Actions('poll');?>
        	<?php endif;?>
        <?php endif;?>
    <?php endif;?>
<?php endif;?>
<?php 
echo $this->grid;
?>
