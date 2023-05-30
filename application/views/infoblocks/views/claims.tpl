<? echo $this->chart('claims');?>
<?php
$export_url = $this->url(array(
							'module' => 'infoblock',
							'controller' => 'claims',
							'action' => 'get-data',
							'format' => 'csv',
));
$id = $this->id('button');
?>
<a title="<? echo _('Экспортировать данные в .csv')?>" href="<?php echo $export_url; ?>" target="_blank" class="ui-button export-button" id="<?php echo $id; ?>"><span class="button-icon"></span></a>
<?php $this->inlineScript()->captureStart(); ?>
$(function () { $('#<?php echo $id; ?>').button({text: false}); });
<?php $this->inlineScript()->captureEnd(); ?>
<p>
<? echo sprintf(_('За период %s поступило заявок: %s'), $this->chartSelectPeriod($this->periodSet, $this->period), "<span id=\"claims-placeholder-total\" class=\"claims-total\">{$this->total}</span>");?>.&nbsp;
<? if ($this->undone): ?>
<? echo sprintf(_('Не обработано заявок: %s'), "<span class=\"claims-undone\"><a href=\"" . $this->url(array('module' => 'order', 'controller' => 'list')) . "\" title=\"" . _('Список заявок') . "\">{$this->undone}</a></span>");?>
<? else: ?>
<? echo _('Необработанных заявок нет'); ?>.</p>
<? endif; ?>
</p>