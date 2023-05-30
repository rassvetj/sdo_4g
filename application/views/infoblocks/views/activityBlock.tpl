<div class="activity-control-block">
			<div class="activity-control">
				<div class="infoblock-activityBlock-input">
					<div title="<?php echo _('Группа');?>"><?php echo _('Группа:');?></div>
					<select id="activity-select-group" name="group">
						<?foreach ($this->groups as $key => $value):?>
						<option value="<?php echo $key?>" <? echo ($key < 0) ? 'class="subgroup"' : ''; ?> <? echo ($this->group == $key) ? 'selected' : ''; ?>><?php echo (strlen($value) > 50) ? substr($value, 0, 50) . '...' : $value; ?></option>
						<?endforeach;?>
					</select>
				</div>
			</div>
			<div class="activity-control">
				<div class="infoblock-activityBlock-input">
					<div title="<?php echo _('Пользователь');?>"><?php echo _('Пользователь');?>:</div>
					<select id="activity-select-user" name="user">
						<?foreach ($this->users as $key => $value):?>
						<option value="<?php echo $key?>" <? echo ($this->user == $key) ? 'selected' : ''; ?>><?php echo $value?></option>
						<?endforeach;?>
					</select>
				</div>
			</div>	
			<div class="activity-control">
				<div class="infoblock-activityBlock-input">
					<div title="<?php echo _('Вид');?>"><?php echo _('Вид:');?></div>
					<select class="activity-select-type" name="type">
						<option id="activity-select-type-times" value="times" <? echo ($this->type == 'times') ? 'selected' : ''; ?>><?php echo _('Время в системе')?></option>
						<option id="activity-select-type-sessions" value="sessions" <? echo ($this->type == 'sessions') ? 'selected' : ''; ?>><?php echo _('Количество сессий')?></option>
					</select>
				</div>
			</div>
			<div class="activity-control">
				<div class="infoblock-activityBlock-input">
					<div title="<?php echo _('Период');?>"><?php echo _('Период:');?></div>
					<? echo $this->chartSelectPeriod($this->periodSet, $this->period);?>
				</div>
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
	</div>
	<div class="activity-control-block-right">
		<a title="<? echo _('Экспортировать данные в .csv')?>" href="<?php echo $export_url; ?>" target="_blank" class="ui-button export-button" id="<?php echo $id; ?>"><span class="button-icon"></span></a>
	</div>
<?php $this->inlineScript()->captureStart(); ?>
$(function () { $('#<?php echo $id; ?>').button({text: false}); });
<?php $this->inlineScript()->captureEnd(); ?>
<div style="clear: both"></div>
<? echo $this->chart('activity');?>
