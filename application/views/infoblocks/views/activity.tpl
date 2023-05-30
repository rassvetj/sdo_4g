<div class="activity-control">
Группа:<br>
<select id="activity-select-group" name="group">
<?foreach ($this->groups as $key => $value):?>
<option value="<?php echo $key?>" <? echo ($key < 0) ? 'class="subgroup"' : ''; ?> <? echo ($this->group == $key) ? 'selected' : ''; ?>><?php echo (strlen($value) > 25) ? substr($value, 0, 25) . '...' : $value; ?></option>
<?endforeach;?>
</select>
</div>
<div class="activity-control">
Пользователь:<br>
<select id="activity-select-user" name="user">
<?foreach ($this->users as $key => $value):?>
<option value="<?php echo $key?>" <? echo ($this->user == $key) ? 'selected' : ''; ?>><?php echo $value?></option>
<?endforeach;?>
</select>
</div>
<div class="activity-control">
Вид:<br>
<select class="activity-select-type" name="type">
<option id="activity-select-type-times" value="times" <? echo ($this->type == 'times') ? 'selected' : ''; ?>><?php echo _('Время в системе')?></option>
<option id="activity-select-type-sessions" value="sessions" <? echo ($this->type == 'sessions') ? 'selected' : ''; ?>><?php echo _('Количество сессий')?></option>
</select>
</div>
<div class="activity-control">
Период:<br>
<? echo $this->chartSelectPeriod($this->periodSet, $this->period);?>
</div>
<?php
$export_url = $this->url(array(
	'module' => 'infoblock',
	'controller' => 'activity',
	'action' => 'get-data',
	'format' => 'csv',
));
$id = $this->id('button');
?>
<a title="<? echo _('Экспортировать данные в .csv')?>" href="<?php echo $export_url; ?>" target="_blank" class="ui-button export-button" id="<?php echo $id; ?>"><span class="button-icon"></span></a>
<?php $this->inlineScript()->captureStart(); ?>
$(function () { $('#<?php echo $id; ?>').button({text: false}); });
<?php $this->inlineScript()->captureEnd(); ?>
<div style="clear: both"></div>
<? echo $this->chart('activity');?>
