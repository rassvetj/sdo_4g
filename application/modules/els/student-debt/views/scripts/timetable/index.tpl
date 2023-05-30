<?php if(!count($this->data)):?>
	<p><?=_('Нет данных')?></p>
<?php else: ?>
	<table class="table-default">
		<tr>
			<th><?=$this->fields['semester']?></th>
			<th><?=$this->fields['attempt']?></th>
			<th><?=$this->fields['chair']?></th>
			<th><?=$this->fields['study_form']?></th>
			<th><?=$this->fields['control_form']?></th>
			<th><?=$this->fields['group_name']?></th>
			<th><?=$this->fields['discipline']?></th>
			<th><?=$this->fields['language']?></th>
			<th><?=$this->fields['teacher']?></th>
			<th><?=$this->fields['date_day']?></th>
			<th><?=$this->fields['date_time']?></th>
			<th><?=$this->fields['place']?></th>
			<th><?=$this->fields['commission_url']?></th>
		</tr>
		<?php foreach($this->data as $i):?>
			<tr>
				<td class="center"><?=$i->semester?></td>
				<td class="center"><?=$i->attempt?></td>
				<td class="center"><?=$i->chair?></td>
				<td class="center"><?=$i->study_form?></td>
				<td class="center"><?=$i->control_form?></td>
				<td class="center"><?=$i->group_name?></td>
				<td><?=$i->discipline?></td>
				<td class="center"><?=$i->language?></td>				
				<td class="center">
					<?php if(!empty($i->teacher_list)):?>
						<?php $teachers_formatted = '';?>
						<?php foreach($i->teacher_list as $teacher):?>
							<?php $teachers_formatted .= ', ' . $teacher['fio'] . ' (<a href="mailto:' . $teacher['EMail'] . '">' . $teacher['EMail'] . '</a>)'; ?>
						<?php endforeach;?>
						<?php $teachers_formatted = trim($teachers_formatted, ','); ?>
						<?=$teachers_formatted;?>
					<?php else:?>
						<?=$i->teacher?>
					<?php endif;?>
				</td>				
				<td class="center"><?=date('d.m.Y', strtotime($i->date))?></td>
				<td class="center"><?=date('H:i', strtotime($i->date))?></td>
				<td class="center"><?=$i->place?></td>
				<td class="center"><a href="<?=$i->commission_url?>" target="_blank"><?=$i->commission_url?></a></td>	
			</tr>
		<?php endforeach;?>
	</table>
<?php endif;?>
<br />
<br />
<br />