<div>
	<div>
		<?=_('Студенты других преподавателей')?>
	</div>
	<table>
				<tr>
					<td><?=_('ФИО студента')?></td>
					<td><?=_('Группа')?></td>
					<td><?=_('Балл')?></td>
					<td><?=_('Уровень языка')?></td>
				</tr>
			<?php foreach($this->students as $student): ?>
				<?php if($student->available){ continue; } ?>
				<?php $isBlocked = ($student->assign && $student->assign->isBlocked()) ? true : false; ?>
				<tr class="<?=$isBlocked ? 'ln_not_available' : ''?>">				
					<td><?=$student->getName()?></td>
					<td><?=$student->group?></td>
					<td><?=$student->ball?></td>
					<td><?=$student->assign->language->name ? $student->assign->language->name : _('нет')?></td>
				</tr>
			<?php endforeach; ?>
	</table>		
</div>
