<?php $this->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/content-modules/score.css'); ?>
<?php $this->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/content-modules/test.css'); ?>
<?php $this->headLink()->appendStylesheet($this->baseUrl('css/content-modules/marksheet.css')); ?>
<?php if(empty($this->users)) : ?>
	<h3>Нет данных для отображения</h3>
<?php else :?>
	<form method="POST" action="<?= $this->baseUrl($this->url(array('module' => 'journal', 'controller' => 'index', 'action' => 'save')))?>">
		<table class="main-grid">
			<thead>
				<tr class="marksheet-head">
					<th class="marksheet-rowcheckbox first-cell">ФИО студента</th>
					<th class="marksheet-rowcheckbox first-cell">Текущий балл</th>
					<th class="marksheet-rowcheckbox first-cell">Новый балл</th>			
				</tr>
			</thead>
			<tbody>		
				<?php foreach($this->users as $user) : ?>				
					<tr class="odd fio-cell">
						<td class="fio-cell">
							<!--<input type="hidden" name="group_id" value="<?php echo ','.implode(',',$user['groups']).','; ?>">--> <? /*Для фильтра  по группа. Но его пока нет*/ ?>
							<?=$user['card']; ?>
							<b style="color: #1171b4;"><?=$user['fio'];?></b>
							<p>Учебные группы: <?=(!empty($user['groups_name'])) ? (implode(', ',$user['groups_name'])) : (_('Нет')); ?></p>
						</td>
						<td>
							<div class="<?=($user['mark'] > 0) ? 'score_red' : 'score_gray'; ?> number_number">
								<span align="center"><?=($user['mark'] > 0) ? round($user['mark'], 2) : 'Нет'; ?></span>
							</div>
						</td>
						<td>
							<select name="ball_<?=$user['MID']?>" id="ball_<?=$user['MID']?>" style="display: inline-block;">
								<option value="-1" title="-Выберите-" label="-Выберите-" selected="selected">-Выберите-</option>
								<optgroup label="Отлично">
									<? for($i=100; $i >= 85; $i--) : ?>
										<option value="<?=$i;?>" title="<?=$i;?>" label="<?=$i;?>"><?=$i;?></option>
									<? endfor; ?>
								</optgroup>
								<optgroup label="Хорошо">
									<? for($i=84; $i >= 75; $i--) : ?>
										<option value="<?=$i;?>" title="<?=$i;?>" label="<?=$i;?>"><?=$i;?></option>
									<? endfor; ?>
								</optgroup>
								<optgroup label="Удовлетворительно">
									<? for($i=74; $i >= 65; $i--) : ?>
										<option value="<?=$i;?>" title="<?=$i;?>" label="<?=$i;?>"><?=$i;?></option>
									<? endfor; ?>
								</optgroup>
								<optgroup label="Неудовлетворительно">
									<? for($i=64; $i >= 1; $i--) : ?>
										<option value="<?=$i;?>" title="<?=$i;?>" label="<?=$i;?>"><?=$i;?></option>
									<? endfor; ?>
								</optgroup>
								<optgroup label="Неявка">
									<? for($i=0; $i >= 0; $i--) : ?>
										<option value="<?=$i;?>" title="<?=$i;?>" label="<?=$i;?>"><?=$i;?></option>
									<? endfor; ?>
								</optgroup>
							</select>
						</td>
					</tr>
				<?php endforeach; ?>		
			</tbody>
			<tfoot>
				<tr>
					<td colspan="3">
						<table cellspacing="0">
							<tbody>
								
								<tr class="last-row">
									<td class="first-cell" style="text-align: right;">
										<input type="submit" value="Сохранить" onClick="if (confirm('Сохранить результат?')) { } else { return false; };">
									</td>
								</tr>
							</tbody>
						</table>
					</td>
				<tr>
			</tfoot>
		</table>
	</form>
<?php endif; ?>