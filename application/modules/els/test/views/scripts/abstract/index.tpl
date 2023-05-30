<?php if (!$this->gridAjaxRequest):?>
    <?php if (Zend_Registry::get('serviceContainer')->getService('Acl')->isCurrentAllowed('mca:test:list:new')):?>
        
        <?php //echo $this->addButton($this->url(array('action' => 'new', 'controller' => 'list', 'module' => 'test')), _('Создать тест'))?>
         <?php if ($this->subjectId > 0):?>
         <?php echo $this->Actions('abstract', array(
        											array('title' => _('Создать тест'), 'url' => $this->url(array('module' => 'test', 'controller' => 'abstract', 'action' => 'new'))),
        											array('title' => _('Импортировать тест'), 'url' => $this->url(array('module' => 'test', 'controller' => 'import', 'action' => 'index')))
        											));?>
         <?php else:?>
        	<?php if(in_array(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), array(HM_Role_RoleModelAbstract::ROLE_DEVELOPER, HM_Role_RoleModelAbstract::ROLE_MANAGER))): ?>
                <?php echo $this->Actions('abstract', array(
        											array('title' => _('Создать тест'), 'url' => $this->url(array('module' => 'test', 'controller' => 'abstract', 'action' => 'new'))),
        											array('title' => _('Импортировать тест'), 'url' => $this->url(array('module' => 'test', 'controller' => 'import', 'action' => 'index')))
        											));?>
        	<?php endif;?>
        <?php endif;?>
    <?php endif;?>
<?php endif;?>
<?php 
echo $this->grid;
?>
