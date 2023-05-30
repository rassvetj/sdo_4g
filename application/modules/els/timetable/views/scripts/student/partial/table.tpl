<style>
	.tt-table{
	    font-size: 13px;
		border: 1px solid #ccc;
		border-collapse: collapse;
	}
	
	.tt-table td, .tt-table th {
			border: 1px solid #ccc;
			padding: 5px;
	}
	.week-day-item td{
		text-align: center;
		background-color: #ecf0f1;
		font-weight: bold;
	}
	.even-odd-item td{
		text-align: center;
		font-weight: bold;
		font-size: 15px;
	}
	
	.even-item td{
		color:red;
	}
	
	.tt-table th{
		background-color: #0067a4;
		color: white;
		font-weight: bold;
		text-align: center;
	}
	
	.user-rank{
		color: #ccc;
	}
</style>
<table class="tt-table">
	<tr class="even-odd-item <?=($this->current_even_odd_id == HM_Timetable_TimetableModel::TYPE_EVEN) ? 'even-item' : ''?>">
		<td colspan="4"><?=($this->current_even_odd_id == HM_Timetable_TimetableModel::TYPE_ODD ? 'Нечетная' : 'Четная')?> неделя</td>
	</tr>
	<tr>
		<th>Время</th>
		<th>Аудитория</th>
		<th>Дисциплина</th>
		<th>Преподаватель</th>
	</tr>
	<?php for($day = 1; $day <= 6; $day++):?>
		<tr class="week-day-item">
			<td colspan="4"><?=$this->list_week_day[$day]?></td>
		</tr>
		
		<?php $this->current_week_day_id = $day; ?>
		<?=$this->render('/student/partial/table-week-day.tpl');?>
		
	<?php endfor;?>
				
</table>
