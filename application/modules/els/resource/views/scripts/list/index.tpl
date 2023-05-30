<?php if (!$this->gridAjaxRequest):?>
    <?php if (!$this->gridAjaxRequest && $this->isTeacher) echo $this->headSwitcher(array('module' => 'resource', 'controller' => 'list', 'action' => 'index', 'switcher' => 'index'), 'materialresource', array('index_courses', 'materialresource_courses'));?>
    <?php if (Zend_Registry::get('serviceContainer')->getService('Acl')->isCurrentAllowed('mca:resource:list:new')):?>
         <?php if ($this->subjectId > 0):?>
             <?php echo $this->Actions('resource', array(array('title' => _('Создать информационный ресурс'), 'url' => $this->url(array('action' => 'new')))));?>
         <?php else:?>
            <?php echo $this->Actions('resource', array());?>
        	<?php endif;?>
        <?php endif;?>
    <?php endif;?>
<?php echo $this->grid?>