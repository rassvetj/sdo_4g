	<div class="byp-items">	
		<form	method="POST"
				action="<?=$this->url(array('module' => 'subject', 'controller' => 'by-programm', 'action' => 'assign', 'MID' => $this->student->MID), 'default', true)?>"
		>
			<table class="byp-tbl-items">
				<tr>
					<th><?=_('ID')?></th>
					<th><?=_('Код 1С')?></th>
					<th><?=_('Сессия')?></th>
					<th><?=_('Дата начала')?></th>
					<th><?=_('Дата окончания')?></th>
					<th><?=_('Дата начала обучения')?></th>
					<th><?=_('Продление 1')?></th>
					<th><?=_('Продление 2')?></th>
					<th><?=_('Семестр')?></th>
					<th><?=_('Код языка')?></th>
					<th><?=_('Код модуля')?></th>
					<th><?=_('Дата создания сессии')?></th>
					<th><?=_('Ошибки')?></th>
					<th><?=_('Будут назначены')?></th>
				</tr>
			<?php foreach($this->subjects as $subject):?>
				<tr>
					<td><?=$subject->subid?></td>
					<td><?=$subject->external_id?></td>
					<td>
						<a href="<?=$subject->getDefaultUri()?>" target="_blank">
							<?=$subject->getName()?>
						</a>
					</td>
					<td><?=$subject->getBegin()?></td>
					<td><?=$subject->getEnd()?></td>
					<td><?=$subject->getBeginLearning()?></td>
					<td><?=$subject->getDateDebt()?></td>
					<td><?=$subject->getDateDebt2()?></td>
					<td><?=$subject->semester?></td>
					<td><?=$subject->language?></td>
					<td><?=$subject->module_name?></td>
					<td style="vertical-align: middle;">
						<?=$subject->getDateCreated()?>
						<p style="font-size: 11px; color: red;"><?=($subject->isOld() ? _('устаревшая') : '')?></p>
					</td>				
					<td class="<?=(empty($subject->errors) ? '' : 'byp-item-error')?>" >
						<ul class="byp-item-error-list">
							<?=(empty($subject->errors) ? '' : '<li>' . implode('</li><li>', $subject->errors) . '</li>' )?>							
							<?=$subject->isGraduated ? '<li>' . HM_Role_StudentModel::getErrorText(HM_Role_StudentModel::ERR_IN_GRADUATED) . '</li>' : '' ?>
						</ul>
					</td>
					<td>
						<?php if(empty($subject->errors)): ?>
							<input type="checkbox" name="cid[]" value="<?=$subject->subid?>" <?=$subject->isGraduated ? '' : 'checked'?>/>
						<?php endif;?>
					</td>
				</td>
			<?php endforeach;?>
			</table>
			<input type="submit" value="<?=_('Назначить на выделеные сессии')?>" >
		</form>	
	</div>