<?php //@todo: headswitcher'ы 2 двух разных страниц указывают на одну страницу materials; переход обратно всегда в resources ?>
<?php if (!$this->isGridAjaxRequest) echo $this->headSwitcher(array('module' => 'subject', 'controller' => 'index', 'action' => 'courses', 'switcher' => 'index_courses'), 'materialresource', array('materialresource', 'index'));?>
<?php if (!$this->isGridAjaxRequest):?>
    <?php if (Zend_Registry::get('serviceContainer')->getService('Acl')->isCurrentAllowed('mca:course:import:subject')):?>
        <?php echo $this->actions('subject-courses', array(
			array(
	            'title' => _('Создать учебный модуль'),
	            'url' => $this->url(array('module' => 'course', 'controller' => 'list', 'action' => 'new', 'subject_id' => $this->subjectId))
	        ),
            array(
                'title' => _('Импортировать учебный модуль'),
                'url' => $this->url(array('module' => 'course', 'controller' => 'import', 'action' => 'subject', 'subject_id' => $this->subjectId))
            )
        ))?>
    <?php endif;?>
<?php endif;?>
<?php echo $this->grid?>