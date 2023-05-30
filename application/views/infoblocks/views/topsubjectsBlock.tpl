<!--div id="topsubjects-text">
	<?php
	$export_url = $this->url(array(
		'module' => 'infoblock',
		'controller' => 'topsubjects',
		'action' => 'get-data',
		'format' => 'csv',
	));
	$id = $this->id('button');
	?>
	<a title="<? echo _('Экспортировать данные в .csv')?>" href="<?php echo $export_url; ?>" target="_blank" class="ui-button export-button" id="<?php echo $id; ?>"><span class="button-icon"></span></a>
	<?php $this->inlineScript()->captureStart(); ?>
	$(function () { $('#<?php echo $id; ?>').button({text: false}); });
	<?php $this->inlineScript()->captureEnd(); ?>
	<p><?php echo sprintf(_('Наиболее популярные курсы за период с %s по %s (по числу заявок):'),$this->begin,$this->end);?></p>
	<ol id="topsubjects-placeholder-list"></ol>
</div-->
<? echo $this->chart('topsubjects', 'ampie', 100);?>