<?php
$this->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/content-modules/schedule_table.css');
$this->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/content-modules/courses_table.css');
?>
<?php if($this->is_student):?>
	<p>
        Отображение итоговой оценки на вкладке "Мои курсы" отображается в тестовом режиме.
    </p>
    <p>
        Актуальные баллы необходимо смотреть на странице "План занятий" &#8594; "Прогресс изучения".
    </p>
    <p>
        Если итоговые оценки не совпадают, обращайтесь на кнопку «Техническая поддержка» либо на <a href="mailto:helpsdo@rgsu.net">helpsdo@rgsu.net</a>.
    </p>
	<br />
<?php endif;?>

<?php echo $this->headSwitcher(array('module' => 'subject', 'controller' => 'list', 'action' => 'index', 'switcher' => 'list'));?>
<?php if (Zend_Registry::get('serviceContainer')->getService('Acl')->isCurrentAllowed('mca:subject:list:new')):?>
    <?php echo $this->Actions('subject');?>
<?php endif;?>
<?php echo $this->listSwitcher(
      array('ending' => _('завершенные'), 'past' => _('прошедшие'), 'current' => _('текущие'), 'future' => _('будущие')),
      array('module' => 'subject', 'controller' => 'list', 'action' => 'list'),
      $this->listSwitcher
);?>
<?php if (count($this->subjects)):?>
    <?php if (isset($this->is_student) && $this->is_student) : ?>
    <div class="progress_title" style="margin-left: 185px; width: 100px;float:left"><?php echo _('Результат');?></div>
    <?php endif; ?>
    <div class="clearfix"></div>
    <?php foreach($this->subjects as $subject):?>
        <?php echo $this->subjectPreview($subject, $this->marks, $this->graduatedList, $this->studentCourseData[$subject->subid])?>
    <?php endforeach;?>
<?php else:?>
    <div class="clearfix"></div>
    <div><?php echo _('Отсутствуют данные для отображения')?></div>
<?php endif;?>