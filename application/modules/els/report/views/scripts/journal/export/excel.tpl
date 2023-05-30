<style>
	.report-journal-table, .report-journal-table td, ww {
		border: 1px solid black;
		border-collapse: collapse;
		padding: 5px;
	}
</style>
Сессия: <?=$this->subject->name?>&nbsp;<?=$this->subject->external_id ? '(' . $this->subject->external_id . ')' : '' ?>
<br />
Занятие: <?=$this->lesson->title?>
<table class="report-journal-table">
	<thead>		
		<tr>
			<td style="border: 1px solid black;">Студент</td>
			<td style="border: 1px solid black;">Группа</td>
			<?php foreach($this->lesson->days as $day):?>
				<td style="border: 1px solid black;"><?=(strtotime($day->date_lesson)>0 ? date('d.m.Y', strtotime($day->date_lesson)) : $day->date_lesson )?></td>	
			<?php endforeach;?>
			
			<?php if(in_array($this->lesson->typeID, array(HM_Event_EventModel::TYPE_JOURNAL_PRACTICE))):?>
				<td style="border: 1px solid black;">АА</td>
				<td style="border: 1px solid black;">ПР</td>
			<?php else: ?>
				<td style="border: 1px solid black;">Итог</td>				
			<?php endif;?>
		</tr>
	</thead>
	<tbody>
	<?php foreach($this->users as $user):?>
		<?php $lastExistsItem = false; ?>
		<tr style="<?=$user->isGraduated ? 'color:red;' : ''?>" >
			<td style="border: 1px solid black;"><?=$user->getName()?></td>
			<td style="border: 1px solid black;">
			<?
			if($user->groupId){
				$group = $this->groups->exists('group_id', $user->groupId);
			}
			echo $group ? $group->name : 'нет';				
			?>
			</td>
			<?php foreach($this->lesson->days as $day):?>
			<?php $journalResult = $day->journalResults ? $day->journalResults->exists('MID', $user->MID) : false; ?>
			
			<td style="border: 1px solid black;">
				<?php if($journalResult):?>
					<?php 
					$lastExistsItem = $journalResult;
					$info           = '';
					$info           = $info . ', ' . ($journalResult->isBe == HM_Lesson_Journal_Result_ResultModel::IS_BE_YES ? 'Был' : 'Не был');
					$info           = $journalResult->format_attendance ? $info . ', ' . $journalResult->format_attendance : $info;
					$info           = $journalResult->mark              ? $info . ', ' . $journalResult->mark              : $info;
					$info           = trim($info, ", ");					
					?>
					<?=$info?>
				<?php endif;?>
			</td>
			<?php endforeach;?>
			
			<?php if(in_array($this->lesson->typeID, array(HM_Event_EventModel::TYPE_JOURNAL_PRACTICE))):?>
				<td style="border: 1px solid black;"><?=$lastExistsItem ? str_replace('.', ',', $lastExistsItem->ballAcademic) : ''?></td>
				<td style="border: 1px solid black;"><?=$lastExistsItem ? str_replace('.', ',', $lastExistsItem->ballPractic) : ''?></td>
			<?php else:?>
				<td style="border: 1px solid black;">					
				<?=$lastExistsItem ? str_replace('.', ',', $lastExistsItem->V_STATUS) : 'Нет'?></td>
			<?php endif;?>
		</tr>
	<?php endforeach;?>
	</tbody>
</table>