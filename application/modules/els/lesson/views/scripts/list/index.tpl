<?php if (!$this->gridAjaxRequest):?>
    <?php echo $this->headSwitcher(array('module' => 'lesson', 'controller' => 'list', 'action' => 'index', 'switcher' => 'index'));?>
    <?php if (Zend_Registry::get('serviceContainer')->getService('Acl')->isCurrentAllowed('mca:lesson:list:new')):?>
    <?php echo $this->Actions('lessons', array(array('title' => _('Создать занятие'), 'url' => $this->url(array('action' => 'new'))), array('title' => _('Сгенерировать план занятий'), 'url' => $this->url(array('action' => 'generate')))));?>
    <?php endif;?>
	<table>
		<tr><td><b><?=_('Ф')?>&minus;</b></td><td> &ndash; <?=_('можно ставить оценку без файла')?></td></tr>
		<tr><td><b><?=_('Р')?>+</b></td><td> &ndash; <?=_('можно вручную выставлять оценку в занятии "тест"')?></td></tr>
		<?php if($this->isTeacher): ?>
			<tr><td><b><?=_('л')?></b></td><td> &ndash; <?=_('лектор')?></td></tr>
			<tr><td><b><?=_('лаб')?></b></td><td> &ndash; <?=_('лаборант')?></td></tr>
			<tr><td><b><?=_('пр')?></b></td><td> &ndash; <?=_('семинарист')?></td></tr>
			<tr><td><b><?=_('отсутствие метки')?></b></td><td> &ndash; <?=_('вручную назначенный тьютор')?></td></tr>		
		<?php endif; ?>
	</table>	
<?php endif;?>
<?php echo $this->grid?>
<?php if (!$this->gridAjaxRequest):?>
<?php if ($this->isBrs):?>
<div style="font-weight: bold; text-align: center; padding: 10px">
    <?=_('Сумма максимальных баллов за обязательные занятия: ').$this->maxBallSum?>
</div>
<?php endif;?>
<?php endif;?>