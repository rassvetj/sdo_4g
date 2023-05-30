<?php
$totalCols = count($this->lesson->days) + 3;
if(in_array($this->lesson->typeID, array(HM_Event_EventModel::TYPE_JOURNAL_PRACTICE))){
	$totalCols++;	
}
?>
<style>
	.report-journal-table, .report-journal-table td, ww {
		border: 1px solid #eef2f5;
		border-color: #b1c0ca #c5d0d7;
		border-collapse: collapse;
		padding: 5px;
	}
	
	.tbl-header {
		mso-style-parent:style0;
		font-size: 11px;
		border:1.0pt solid #eef2f5;
		background:#dde4ea;
		mso-pattern:black none;
		white-space:normal;
		text-align: center;
		vertical-align: middle;
		padding: 0.182em 0.636em;
	}
	
	.tbl-footer {
		mso-style-parent:style0;
		font-size: 11px;
		border:1.0pt solid #eef2f5;
		background:#dde4ea;
		mso-pattern:black none;
		white-space:normal;
		text-align: center;
		vertical-align: middle;
	}
	
	.tbl-base-ceil {
		border:1.0pt solid #eef2f5;
		border-color: #c5d0d7;
		color: #333333;
	}
	
	
</style>

<table class="report-journal-table">
	<thead>
		<tr>
			<td colspan="<?=$totalCols?>">Сессия: <?=$this->subject->name;?></td>
		</tr>
		<tr>
			<td colspan="<?=$totalCols?>">Занятие: <?=$this->lesson->title?></td>
		</tr>
		<tr >
			<td  class="tbl-header">ФИО студента</td>
			<td  class="tbl-header">Учебные группы</td>
			<?php if($this->lesson->days && count($this->lesson->days)):?>
				<?php foreach($this->lesson->days as $day):?>
					<td  class="tbl-header"><b><?=(strtotime($day->date_lesson)>0 ? date('d.m.Y', strtotime($day->date_lesson)) : $day->date_lesson )?></b></td>
				<?php endforeach;?>
			<?php endif;?>
			
			<?php if(in_array($this->lesson->typeID, array(HM_Event_EventModel::TYPE_JOURNAL_PRACTICE))):?>
				<td  class="tbl-header">АА</td>
				<td  class="tbl-header">ПР</td>
			<?php else: ?>
				<td  class="tbl-header">Итог</td>				
			<?php endif;?>
		</tr>
	</thead>
	<tbody>
	<?php foreach($this->users as $user):?>
		<?php $lastExistsItem = false; ?>
		<tr >
			<td style="tbl-base-ceil" ><span style="<?=$user->isGraduated ? 'color:red;' : 'color: #1171b4;'?>"><?=$user->getName()?></span></td>
			<td style="tbl-base-ceil" >
				<?
				if($user->groupId){
					$group = $this->groups->exists('group_id', $user->groupId);
				}
				echo $group ? $group->name : 'нет';				
				?>
				</td>
			
			<?php if($this->lesson->days && count($this->lesson->days)):?>
				<?php foreach($this->lesson->days as $day):?>			
				<?php $journalResult = $day->journalResults ? $day->journalResults->exists('MID', $user->MID) : false; ?>
				<td style="tbl-base-ceil" >
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
			<?php endif;?>
				
			<?php if(in_array($this->lesson->typeID, array(HM_Event_EventModel::TYPE_JOURNAL_PRACTICE))):?>
				<td style="tbl-base-ceil" ><?=$lastExistsItem ? str_replace('.', ',', $lastExistsItem->ballAcademic) : ''?></td>
				<td style="tbl-base-ceil" ><?=$lastExistsItem ? str_replace('.', ',', $lastExistsItem->ballPractic) : ''?></td>
			<?php else:?>
				<td style="tbl-base-ceil" >					
				<?=$lastExistsItem ? str_replace('.', ',', $lastExistsItem->V_STATUS) : 'Нет'?></td>
			<?php endif;?>
			
		</tr>
	<?php endforeach;?>
		<tr>
			<td  colspan="<?=$totalCols?>" class="tbl-footer">&nbsp;</td>
		</tr>	
	</tbody>
	
	
	
</table>